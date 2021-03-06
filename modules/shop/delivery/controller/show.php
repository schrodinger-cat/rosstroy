<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Выбор способа доставки.
 *
 * @package HostCMS 6\Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2014 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Delivery_Controller_Show extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'shop_country_id',
		'shop_country_location_id',
		'shop_country_location_city_id',
		'shop_country_location_city_area_id',
		'totalWeight',
		'totalAmount',
		'couponText',
		'postcode',
		'volume'
	);

	/**
	 * Constructor.
	 * @param Shop_Model $oShop shop
	 */
	public function __construct(Shop_Model $oShop)
	{
		parent::__construct($oShop->clearEntities());

		if (Core::moduleIsActive('siteuser'))
		{
			// Если есть модуль пользователей сайта, $siteuser_id равен 0 или ID авторизованного
			$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
			if ($oSiteuser)
			{
				$this->addEntity($oSiteuser->clearEntities());
			}
		}
	}

	/**
	 * Show built data
	 * @return self
	 * @hostcms-event Shop_Delivery_Controller_Show.onBeforeRedeclaredShow
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

		$oShop = $this->getEntity();

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('total_weight')
				->value($this->totalWeight)
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('total_amount')
				->value($this->totalAmount)
		);

		// Выбираем все типы доставки для данного магазина
		$aShop_Deliveries = $oShop->Shop_Deliveries->getAllByActive(1);

		foreach ($aShop_Deliveries as $oShop_Delivery)
		{
			if ($oShop_Delivery->type == 0)
			{
				$oShop_Delivery_Condition_Controller = new Shop_Delivery_Condition_Controller();
				$oShop_Delivery_Condition_Controller
					->shop_country_id($this->shop_country_id)
					->shop_country_location_id($this->shop_country_location_id)
					->shop_country_location_city_id($this->shop_country_location_city_id)
					->shop_country_location_city_area_id($this->shop_country_location_city_area_id)
					->totalWeight($this->totalWeight)
					->totalAmount($this->totalAmount);

				// Условие доставки, подходящее под ограничения
				$aShop_Delivery_Condition = array(
					$oShop_Delivery_Condition_Controller->getShopDeliveryCondition($oShop_Delivery)
				);
			}
			else
			{
				$aShop_Delivery_Condition = array();

				try
				{
					$aPrice = Shop_Delivery_Handler::factory($oShop_Delivery)->country($this->shop_country_id)->location($this->shop_country_location_id)->city($this->shop_country_location_city_id)->weight($this->totalWeight)->postcode($this->postcode)->volume($this->volume)->execute();

					!is_array($aPrice) && $aPrice = array($aPrice);

					foreach ($aPrice as $key => $object)
					{
						if(!is_object($object))
						{
							$tmp = $object;
							$object = new StdClass();
							$object->price = $tmp;
							$object->rate = 0;
							$object->description = NULL;
						}

						$sIndex = $oShop_Delivery->id . '-' . $key;
						
						$_SESSION['hostcmsOrder']['deliveries'][$sIndex] = array(
							'shop_delivery_id' => $oShop_Delivery->id,
							'price' => $object->price,
							'rate' => (isset($object->rate) ? intval($object->rate) : 0),
							'name' => $object->description
						);

						$oShop_Delivery_Condition = Core::factory('Core_Xml_Entity')
							->name('shop_delivery_condition')
							->addAttribute('id', $sIndex . '#')
							->addEntity(
								Core::factory('Core_Xml_Entity')->name('shop_delivery_id')->value($oShop_Delivery->id)
							)->addEntity(
								Core::factory('Core_Xml_Entity')->name('shop_currency_id')->value($oShop_Delivery->Shop->shop_currency_id)
							)->addEntity(
								Core::factory('Core_Xml_Entity')->name('price')->value($object->price)
							)->addEntity(
								Core::factory('Core_Xml_Entity')->name('description')->value($object->description)
							);

						$aShop_Delivery_Condition[] = $oShop_Delivery_Condition;
					}
				}
				catch (Exception $e)
				{
					$aShop_Delivery_Condition = array();
				}
			}

			if (count($aShop_Delivery_Condition))
			{
				foreach ($aShop_Delivery_Condition as $oShop_Delivery_Condition)
				{
					if(!is_null($oShop_Delivery_Condition))
					{
						$oShop_Delivery_Clone = clone $oShop_Delivery;

						$this->addEntity(
							$oShop_Delivery_Clone
								->id($oShop_Delivery->id)
								->clearEntities()
								->addEntity($oShop_Delivery_Condition)
						);
					}
				}

				$aShop_Delivery_Condition = array();
			}
		}

		return parent::show();
	}

	/**
	 * Calculate total amount and weight
	 * @return self
	 */
	public function setUp()
	{
		$oShop = $this->getEntity();

		$Shop_Cart_Controller = Shop_Cart_Controller::instance();

		$amount = 0;
		$quantity = 0;
		$weight = 0;
		$this->volume = 0;

		$aShop_Cart = $Shop_Cart_Controller->getAll($oShop);
		foreach ($aShop_Cart as $oShop_Cart)
		{
			if ($oShop_Cart->Shop_Item->id)
			{
				if ($oShop_Cart->postpone == 0)
				{
					// Prices
					$oShop_Item_Controller = new Shop_Item_Controller();
					if (Core::moduleIsActive('siteuser'))
					{
						$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
						$oSiteuser && $oShop_Item_Controller->siteuser($oSiteuser);
					}

					$oShop_Item_Controller->count($oShop_Cart->quantity);

					$aPrices = $oShop_Item_Controller->getPrices($oShop_Cart->Shop_Item);

					$amount += $aPrices['price_discount'] * $oShop_Cart->quantity;
					$quantity += $oShop_Cart->quantity;
					$weight += $oShop_Cart->Shop_Item->weight * $oShop_Cart->quantity;
					$this->volume += Shop_Controller::convertSizeMeasure($oShop_Cart->Shop_Item->length * $oShop_Cart->Shop_Item->width * $oShop_Cart->Shop_Item->height, $oShop->size_measure, 0);
				}
			}
		}

		// Скидки от суммы заказа
		$oShop_Purchase_Discount_Controller = new Shop_Purchase_Discount_Controller($oShop);
		$oShop_Purchase_Discount_Controller
			->amount($amount)
			->quantity($quantity)
			->couponText($this->couponText)
			;
		$totalDiscount = 0;
		$aShop_Purchase_Discounts = $oShop_Purchase_Discount_Controller->getDiscounts();
		foreach ($aShop_Purchase_Discounts as $oShop_Purchase_Discount)
		{
			$totalDiscount += $oShop_Purchase_Discount->getDiscountAmount();
		}

		$this->totalWeight = $weight;
		$this->totalAmount = $amount - $totalDiscount;

		return $this;
	}
}