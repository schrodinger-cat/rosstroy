<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS 6\Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2013 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Purchase_Discount_Model extends Core_Entity
{
	/**
	 * shop_purchase_discount_coupon_id, uses in the getByCouponText()
	 */
	public $shop_purchase_discount_coupon_id = NULL;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_purchase_discount_coupon' => array()
	);

	/**
	 * One-to-one relations
	 * @var array
	 */
	protected $_hasOne = array(
		'shop' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'active' => 1,
		'value' => 0,
		'min_amount' => 0,
		'max_amount' => 0,
		'min_count' => 0,
		'max_count' => 0
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'shop_currency' => array()
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id))
		{
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
			$this->_preloadValues['start_datetime'] = Core_Date::timestamp2sql(time());
			$this->_preloadValues['end_datetime'] = Core_Date::timestamp2sql(strtotime("+1 year"));
		}
	}

	/**
	 * Order's discount
	 * Расчитанный размер скидки для заказа
	 * @var float
	 */
	protected $_discountAmount = NULL;

	/**
	 * Set discount amount
	 * @param float $discountAmount amount
	 * @return self
	 */
	public function discountAmount($discountAmount)
	{
		$this->_discountAmount = $discountAmount;
		return $this;
	}

	/**
	 * Get discount amount
	 * @return float
	 */
	public function getDiscountAmount()
	{
		return $this->_discountAmount;
	}

	/**
	 * Get discount by coupon text
	 * @param string $couponText text
	 * @return Shop_Purchase_Discount_Model|NULL
	 */
	public function getByCouponText($couponText)
	{
		$this->queryBuilder()
			->select('shop_purchase_discounts.*',
				array('shop_purchase_discount_coupons.id', 'shop_purchase_discount_coupon_id')
			)
			->join('shop_purchase_discount_coupons', 'shop_purchase_discounts.id', '=', 'shop_purchase_discount_coupons.shop_purchase_discount_id')
			->where('shop_purchase_discount_coupons.active', '=', 1)
			->where('shop_purchase_discount_coupons.deleted', '=', 0)
			->where('shop_purchase_discount_coupons.text', 'LIKE', $couponText)
			->open()
			->where('shop_purchase_discount_coupons.count', '>', 0)
			->setOr()
			->where('shop_purchase_discount_coupons.count', '=', -1)
			->close()
			;

		// Чтобы получить новый объект с заполненным shop_purchase_discount_coupon_id используем FALSE
		$aObjects = $this->findAll(FALSE);

		return isset($aObjects[0]) ? $aObjects[0] : NULL;
	}

	/**
	 * Change status of activity for discount
	 * @return self
	 */
	public function changeStatus()
	{
		$this->active = 1 - $this->active;
		return $this->save();
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 */
	public function copy()
	{
		$newObject = parent::copy();

		$aShop_Purchase_Discount_Coupons = $this->Shop_Purchase_Discount_Coupons->findAll();
		foreach($aShop_Purchase_Discount_Coupons as $oShop_Purchase_Discount_Coupon)
		{
			$oNew_Shop_Purchase_Discount_Coupon = $oShop_Purchase_Discount_Coupon->copy();
			$newObject->add($oNew_Shop_Purchase_Discount_Coupon);
		}

		return $newObject;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		$this->Shop_Purchase_Discount_Coupons->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_purchase_discount.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->clearXmlTags()
			->addXmlTag('discount_amount', $this->_discountAmount);

		return parent::getXml();
	}
}