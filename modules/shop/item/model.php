<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS 6\Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2014 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'shop_item';

	/**
	 * Callback property_id
	 * @var int
	 */
	public $img = 1;

	/**
	 * Callback property_id
	 * @var int
	 */
	public $related = 1;

	/**
	 * Callback property_id
	 * @var int
	 */
	public $modifications = 1;

	/**
	 * Callback property_id
	 * @var int
	 */
	public $discounts = 1;

	/**
	 * Callback property_id
	 * @var int
	 */
	public $reviews = 1;

	/**
	 * Callback property_id
	 * @var string
	 */
	public $key = NULL;

	/**
	 * Callback property_id
	 * @var string
	 */
	public $count = NULL;

	/**
	 * Callback property_id
	 * @var string
	 */
	public $absolute_price = NULL;

	/**
	 * Triggered by calling isset() or empty() on inaccessible properties
	 * @param string $property property name
	 * @return boolean
	 */
	public function __isset($property)
	{
		return in_array(strtolower($property), array('adminprice'))
			? TRUE
			: parent::__isset($property);
	}

	/**
	 * Run when writing data to inaccessible properties
	 * @param string $property property name
	 * @param string $value property value
	 * @return self
	 */
	public function __set($property, $value)
	{
		$property == 'adminPrice' && $property = 'price';
		return parent::__set($property, $value);
	}

	/**
	 * Utilized for reading data from inaccessible properties
	 * @param string $property property name
	 * @return mixed
	 */
	public function __get($property)
	{
		return in_array(strtolower($property), array('adminprice'))
			? ($this->shortcut_id
				? Core_Entity::factory('Shop_Item', $this->shortcut_id)->price
				: $this->price)
			: parent::__get($property);
	}

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_cart' => array(),
		'comment' => array('through' => 'comment_shop_item'),
		'shop_discount' => array('through' => 'shop_item_discount'),
		'shop_item_discount' => array(),
		'shop_item_digital' => array(),
		'shop_item' => array('foreign_key' => 'shortcut_id'),
		'modification' => array('model' => 'Shop_Item', 'foreign_key' => 'modification_id'),
		'shop_price' => array('through' => 'shop_item_price'),
		'shop_item_price' => array(),
		'item_associated' => array('through' => 'shop_item_associated', 'through_table_name' => 'shop_item_associated', 'model' => 'Shop_Item', 'dependent_key' => 'shop_item_associated_id'),
		'shop_item_associated' => array(),
		'shop_item_associated_second' => array('model' => 'Shop_Item_Associated', 'foreign_key' => 'shop_item_associated_id'),
		'shop_specialprice' => array(),
		'shop_item_reserved' => array(),
		'tag' => array('through' => 'tag_shop_item'),
		'tag_shop_item' => array(),
		'shop_warehouse' => array('through' => 'shop_warehouse_item'),
		'shop_warehouse_item' => array(),
		'vote' => array('through' => 'vote_shop_item')
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'shortcut_id' => 0,
		'siteuser_id' => 0,
		'weight' => 0,
		'price' => 0,
		'sorting' => 0,
		'image_small_height' => 0,
		'image_small_width' => 0,
		'image_large_height' => 0,
		'image_large_width' => 0,
		'yandex_market' => 1,
		'yandex_market_bid' => 0,
		'yandex_market_cid' => 0,
		'active' => 1,
		'indexing' => 1,
		'modification_id' => 0,
		'shop_measure_id' => 0,
		'length' => 0,
		'width' => 0,
		'height' => 0,
		'showed' => 0
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_measure' => array(),
		'shop_tax' => array(),
		'siteuser_group' => array(),
		'shop_seller' => array(),
		'shop_group' => array(),
		'shop_currency' => array(),
		'shop' => array(),
		'shop_producer' => array(),
		'siteuser' => array(),
		'shop_item' => array('foreign_key' => 'shortcut_id'),
		'modification' => array('model' => 'Shop_Item', 'foreign_key' => 'modification_id'),
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will be shown.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'price',
		'datetime',
		'start_datetime',
		'end_datetime',
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
			$this->_preloadValues['guid'] = Core_Guid::get();
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
		}

		if (/*$this->_loaded && */$this->shortcut_id != 0)
		{
			$this->img = 2;
		}
	}

	/**
	 * Apply tags for item
	 * @param string $sTags string of tags, separated by comma
	 * @return self
	 */
	public function applyTags($sTags)
	{
		$aTags = explode(',', $sTags);

		// Удаляем связь метками
		$this->Tag_Shop_Items->deleteAll(FALSE);

		foreach ($aTags as $tag_name)
		{
			$tag_name = trim($tag_name);

			if ($tag_name != '')
			{
				$oTag = Core_Entity::factory('Tag')->getByName($tag_name, FALSE);

				if (is_null($oTag))
				{
					$oTag = Core_Entity::factory('Tag');
					$oTag->name = $oTag->path = $tag_name;
					$oTag->save();
				}
				$this->add($oTag);
			}
		}

		return $this;
	}

	/**
	 * Values of all properties of item
	 * @var array
	 */
	protected $_propertyValues = NULL;

	/**
	 * Values of all properties of item
	 * Значения всех свойств товара
	 * @param boolean $bCache cache mode status
	 * @param array $aPropertiesId array of properties' IDs
	 * @return array Property_Value
	 */
	public function getPropertyValues($bCache = TRUE, $aPropertiesId = array())
	{
		if ($bCache && !is_null($this->_propertyValues))
		{
			return $this->_propertyValues;
		}

		if (!is_array($aPropertiesId) || !count($aPropertiesId))
		{
			$aProperties = Core_Entity::factory('Shop_Item_Property_List', $this->shop_id)
				->Properties
				->findAll();

			$aPropertiesId = array();
			foreach ($aProperties as $oProperty)
			{
				$aPropertiesId[] = $oProperty->id;
			}
		}

		$aReturn = Property_Controller_Value::getPropertiesValues($aPropertiesId, $this->id, $bCache);

		// setHref()
		foreach ($aReturn as $oProperty_Value)
		{
			if ($oProperty_Value->Property->type == 2)
			{
				$oProperty_Value
					->setHref($this->getItemHref())
					->setDir($this->getItemPath());
			}
		}

		$bCache && $this->_propertyValues = $aReturn;

		return $aReturn;
	}

	/**
	 * Get the quantity in stocks
	 * @return float
	 */
	public function getRest()
	{
		$aShop_Warehouse_Items = !$this->shortcut_id
			? $this->Shop_Warehouse_Items->findAll()
			: Core_Entity::factory('Shop_Item', $this->shortcut_id)->Shop_Warehouse_Items->findAll();

		$rest = 0;
		foreach ($aShop_Warehouse_Items as $oShop_Warehouse_Item)
		{
			$rest += $oShop_Warehouse_Item->count;
		}

		return $rest;
	}

	/**
	 * Get the quantity of reserved items
	 * @return float
	 */
	public function getReserved()
	{
		$oShop_Item = !$this->shortcut_id
			? $this
			: Core_Entity::factory('Shop_Item', $this->shortcut_id);

		$oShop_Item_Reserveds = $oShop_Item->Shop_Item_Reserveds;
		$oShop_Item_Reserveds->queryBuilder()
			->where('shop_item_reserved.datetime', '>', Core_Date::timestamp2sql(time() - $oShop_Item->Shop->reserve_hours * 60 * 60));

		$aShop_Item_Reserveds = $oShop_Item_Reserveds->findAll();

		$reserved = 0;
		foreach ($aShop_Item_Reserveds as $oShop_Item_Reserved)
		{
			$reserved += $oShop_Item_Reserved->count;
		}

		return $reserved;
	}

	/**
	 * Backend callback method
	 * @param mixed $value value
	 * @return float
	 */
	public function adminRest($value = NULL)
	{
		// Get value
		if (is_null($value) || is_object($value))
		{
			return $this->getRest();
		}

		// Save value for default warehouse
		$oDefault_Warehouse = $this->Shop->Shop_Warehouses->getDefault();

		if (!is_null($oDefault_Warehouse))
		{
			$oShop_Warehouse_Item = $oDefault_Warehouse->Shop_Warehouse_Items->getByShopItemId($this->id);

			if(is_null($oShop_Warehouse_Item))
			{
				$oShop_Warehouse_Item = Core_Entity::factory('Shop_Warehouse_Item');
				$oShop_Warehouse_Item->shop_warehouse_id = $oDefault_Warehouse->id;
				$oShop_Warehouse_Item->shop_item_id = $this->id;
			}

			$oShop_Warehouse_Item->count = $value;
			$oShop_Warehouse_Item->save();
		}
		else
		{
			throw new Core_Exception('Default warehouse does not exist.', array(), 0, FALSE);
		}

		return $this;
	}

	/**
	 * Backend callback method
	 * @param object $value value
	 * @return string
	 */
	public function adminPrice($value = NULL)
	{
		// Get value
		if (is_null($value) || is_object($value))
		{
			$oShopItem = $this->shortcut_id
				? Core_Entity::factory("Shop_Item", $this->shortcut_id)
				: $this;

			return $oShopItem->price;
		}

		$this->price = $value;
		$this->save();
	}

	/**
	 * Show item's currency
	 * @return string
	 */
	public function suffixAdminPrice()
	{
		$oShopItem = $this->shortcut_id
			? Core_Entity::factory("Shop_Item", $this->shortcut_id)
			: $this;

		echo ' ' . $oShopItem->Shop_Currency->name;
	}

	/**
	 * Get item path include CMS_FOLDER
	 * @return string
	 */
	public function getItemPath()
	{
		return $this->Shop->getPath() . '/' . Core_File::getNestingDirPath($this->id, $this->Shop->Site->nesting_level) . '/item_' . $this->id . '/';
	}

	/**
	 * Get item href
	 * @return string
	 */
	public function getItemHref()
	{
		return '/' . $this->Shop->getHref() . '/' . Core_File::getNestingDirPath($this->id, $this->Shop->Site->nesting_level) . '/item_' . $this->id . '/';
	}

	/**
	 * Get item small image path
	 * @return string
	 */
	public function getSmallFilePath()
	{
		return $this->getItemPath() . $this->image_small;
	}

	/**
	 * Get item small image href
	 * @return string
	 */
	public function getSmallFileHref()
	{
		return $this->getItemHref() . rawurlencode($this->image_small);
	}

	/**
	 * Get item large image path
	 * @return string
	 */
	public function getLargeFilePath()
	{
		return $this->getItemPath() . $this->image_large;
	}

	/**
	 * Get item large image href
	 * @return string
	 */
	public function getLargeFileHref()
	{
		return $this->getItemHref() . rawurlencode($this->image_large);
	}

	/**
	 * Set large image sizes
	 * @return self
	 */
	public function setLargeImageSizes()
	{
		$path = $this->getLargeFilePath();

		if (is_file($path))
		{
			$aSizes = Core_Image::instance()->getImageSize($path);
			if ($aSizes)
			{
				$this->image_large_width = $aSizes['width'];
				$this->image_large_height = $aSizes['height'];
				$this->save();
			}
		}
		return $this;
	}

	/**
	 * Specify large image for item
	 * @param string $fileSourcePath source file
	 * @param string $fileName target file name
	 * @return self
	 */
	public function saveLargeImageFile($fileSourcePath, $fileName)
	{
		$fileName = Core_File::filenameCorrection($fileName);
		$this->createDir();

		$this->image_large = $fileName;
		$this->save();
		Core_File::upload($fileSourcePath, $this->getItemPath() . $fileName);
		$this->setLargeImageSizes();
		return $this;
	}

	/**
	 * Set small image sizes
	 * @return self
	 */
	public function setSmallImageSizes()
	{
		$path = $this->getSmallFilePath();

		if (is_file($path))
		{
			$aSizes = Core_Image::instance()->getImageSize($path);
			if ($aSizes)
			{
				$this->image_small_width = $aSizes['width'];
				$this->image_small_height = $aSizes['height'];
				$this->save();
			}
		}
		return $this;
	}

	/**
	 * Specify small image for item
	 * @param string $fileSourcePath source file
	 * @param string $fileName target file name
	 * @return self
	 */
	public function saveSmallImageFile($fileSourcePath, $fileName)
	{
		$fileName = Core_File::filenameCorrection($fileName);
		$this->createDir();

		$this->image_small = $fileName;
		$this->save();
		Core_File::upload($fileSourcePath, $this->getItemPath() . $fileName);
		$this->setSmallImageSizes();
		return $this;
	}

	/**
	 * Check and correct duplicate path
	 * @return self
	 */
	public function checkDuplicatePath()
	{
		$oShop = $this->Shop;

		if (!$this->modification_id)
		{
			// Search the same item or group
			$oSameShopItem = $oShop->Shop_Items->getByGroupIdAndPath($this->shop_group_id, $this->path);

			if (!is_null($oSameShopItem) && $oSameShopItem->id != $this->id)
			{
				$this->path = Core_Guid::get();
			}

			$oSameShopGroup = $oShop->Shop_Groups->getByParentIdAndPath($this->shop_group_id, $this->path);
			if (!is_null($oSameShopGroup))
			{
				$this->path = Core_Guid::get();
			}
		}
		else
		{
			$oParentItem = $this->Modification;

			$aSameItems = $oParentItem->Modifications->getAllByPath($this->path);
			foreach ($aSameItems as $oSameItem)
			{
				if ($oSameItem->id != $this->id)
				{
					$this->path = Core_Guid::get();
					break;
				}
			}
		}

		return $this;
	}

	/**
	 * Make url path
	 * @return self
	 */
	public function makePath()
	{
		if ($this->Shop->url_type == 1)
		{
			try {
				$this->path = Core_Str::transliteration(
					Core::$mainConfig['translate']
						? Core_Str::translate($this->name)
						: $this->name
				);
			} catch (Exception $e) {
				$this->path = Core_Str::transliteration($this->name);
			}

			$this->checkDuplicatePath();
		}
		elseif ($this->id)
		{
			$this->path = $this->id;
		}
		else
		{
			$this->path = Core_Guid::get();
		}

		return $this;
	}

	/**
	 * Save object.
	 *
	 * @return Core_Entity
	 */
	public function save()
	{
		if (is_null($this->path))
		{
			$this->makePath();
		}
		elseif (in_array('path', $this->_changedColumns))
		{
			$this->checkDuplicatePath();
		}
		parent::save();

		if ($this->path == '' && !$this->deleted && $this->makePath())
		{
			$this->path != '' && $this->save();
		}

		return $this;
	}

	/**
	 * Create directory for item
	 * @return self
	 */
	public function createDir()
	{
		clearstatcache();

		if (!is_dir($this->getItemPath()))
		{
			try
			{
				Core_File::mkdir($this->getItemPath(), CHMOD, TRUE);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 */
	public function copy()
	{
		$newObject = parent::copy();
		$newObject->path = '';
		$newObject->showed = 0;
		$newObject->guid = Core_Guid::get();
		$newObject->save();

		if (is_file($this->getLargeFilePath()))
		{
			try
			{
				$newObject->createDir();
				Core_File::copy($this->getLargeFilePath(), $newObject->getLargeFilePath());
			}
			catch (Exception $e) {}
		}

		if (is_file($this->getSmallFilePath()))
		{
			try
			{
				$newObject->createDir();
				Core_File::copy($this->getSmallFilePath(), $newObject->getSmallFilePath());
			}
			catch (Exception $e) {}
		}

		$aWarehousesValues = $this->Shop_Warehouse_Items->findAll();
		foreach ($aWarehousesValues as $oWarehousesValue)
		{
			$newWarehouseValue = clone $oWarehousesValue;
			$newWarehouseValue->shop_item_id = $newObject->id;
			$newWarehouseValue->save();
		}

		$aPropertyValues = $this->getPropertyValues();
		foreach($aPropertyValues as $oPropertyValue)
		{
			$oNewPropertyValue = clone $oPropertyValue;
			$oNewPropertyValue->entity_id = $newObject->id;
			$oNewPropertyValue->save();

			if ($oNewPropertyValue->Property->type == 2)
			{
				$oPropertyValue->setDir($this->getItemPath());
				$oNewPropertyValue->setDir($newObject->getItemPath());

				if (is_file($oPropertyValue->getLargeFilePath()))
				{
					try
					{
						Core_File::copy($oPropertyValue->getLargeFilePath(), $oNewPropertyValue->getLargeFilePath());
					} catch (Exception $e) {}
				}

				if (is_file($oPropertyValue->getSmallFilePath()))
				{
					try
					{
						Core_File::copy($oPropertyValue->getSmallFilePath(), $oNewPropertyValue->getSmallFilePath());
					} catch (Exception $e) {}
				}
			}
		}

		// Получаем список цен для копируемого товара
		$aShop_Item_Prices = $this->Shop_Item_Prices->findAll();
		foreach($aShop_Item_Prices as $oShop_Item_Price)
		{
			$newObject->add(clone $oShop_Item_Price);
		}

		// Получаем список специальных цен для копируемого товара
		$aShop_Specialprices = $this->Shop_Specialprices->findAll();
		foreach($aShop_Specialprices as $oShop_Specialprice)
		{
			$newObject->add(clone $oShop_Specialprice);
		}

		// Список модификаций товара
		$aModifications = $this->Modifications->findAll();
		foreach($aModifications as $oModification)
		{
			//$oNewModification = clone $oModification;

			$oNewModification = $oModification->copy();
			$newObject->add($oNewModification, 'modifications');
		}

		// Список сопутствующих товаров копируемому товару
		$aShop_Item_Associateds = $this->Shop_Item_Associateds->findAll();
		foreach($aShop_Item_Associateds as $oShop_Item_Associated)
		{
			$newObject->add(clone $oShop_Item_Associated);
		}

		if (Core::moduleIsActive('tag'))
		{
			$aTags = $this->Tags->findAll();
			foreach($aTags as $oTag)
			{
				$newObject->add($oTag);
			}
		}

		return $newObject;
	}

	/**
	 * Move item to another group
	 * @param int $iShopGroupId target group id
	 * @return Core_Entity
	 */
	public function move($iShopGroupId)
	{
		$this->shop_group_id = $iShopGroupId;
		return $this->save();
	}

	/**
	 * Change item status
	 * @return self
	 */
	public function changeActive()
	{
		$this->active = 1 - $this->active;
		$this->save();

		if (Core::moduleIsActive('search') && $this->indexing && $this->active)
		{
			Search_Controller::indexingSearchPages(array($this->indexing()));
		}

		return $this;
	}

	/**
	 * Create shortcut and move into group $group_id
	 * @param int $group_id group id
	 * @return self
	 */
	public function shortcut($group_id = NULL)
	{
		$oShop_ItemShortcut = Core_Entity::factory('Shop_Item');

		$object = $this->shortcut_id
			? $this->Shop_Item
			: $this;

		$oShop_ItemShortcut->shop_id = $object->shop_id;
		$oShop_ItemShortcut->shortcut_id = $object->id;
		$oShop_ItemShortcut->name = ''/*$object->name*/;
		$oShop_ItemShortcut->type = $object->type;
		$oShop_ItemShortcut->path = '';
		$oShop_ItemShortcut->indexing = 0;

		$oShop_ItemShortcut->shop_group_id =
			is_null($group_id)
			? $object->shop_group_id
			: $group_id;

		return $oShop_ItemShortcut->save();
	}

	/**
	 * Get path for files
	 * @return string
	 */
	public function getPath()
	{
		$sPath = ($this->path == ''
			? $this->id
			: $this->path) . '/';

		if ($this->modification_id == 0)
		{
			if ($this->shop_group_id)
			{
				$sPath = $this->Shop_Group->getPath() . $sPath;
			}
		}
		else
		{
			$sPath = $this->Modification->getPath() . $sPath;
		}

		return $sPath;
	}

	/**
	 * Delete item's large image
	 * @return self
	 */
	public function deleteLargeImage()
	{
		$fileName = $this->getLargeFilePath();
		if ($this->image_large != '' && is_file($fileName))
		{
			try
			{
				Core_File::delete($fileName);
			} catch (Exception $e) {}

			$this->image_large = '';
			$this->save();
		}
		return $this;
	}

	/**
	 * Delete item's small image
	 * @return self
	 */
	public function deleteSmallImage()
	{
		$fileName = $this->getSmallFilePath();
		if ($this->image_small != '' && is_file($fileName))
		{
			try
			{
				Core_File::delete($fileName);
			} catch (Exception $e) {}

			$this->image_small = '';
			$this->save();
		}
		return $this;
	}

	/**
	 * Get the ID of the user group
	 * @return int
	 */
	public function getSiteuserGroupId()
	{
		// как у родителя
		if ($this->siteuser_group_id == -1)
		{
			$result = $this->shop_group_id
				? $this->Shop_Group->getSiteuserGroupId()
				: $this->Shop->siteuser_group_id;
		}
		else
		{
			$result = $this->siteuser_group_id;
		}

		return intval($result);
	}

	/**
	 * Search indexation
	 * @return Search_Page
	 * @hostcms-event shop_item.onBeforeIndexing
	 * @hostcms-event shop_item.onAfterIndexing
	 */
	public function indexing()
	{
		$oSearch_Page = Core_Entity::factory('Search_Page');

		Core_Event::notify($this->_modelName . '.onBeforeIndexing', $this, array($oSearch_Page));

		$oSearch_Page->text = $this->text . ' ' . $this->description . ' ' . htmlspecialchars($this->name) . ' ' . $this->id . ' ' . htmlspecialchars($this->seo_title) . ' ' . htmlspecialchars($this->seo_description) . ' ' . htmlspecialchars($this->seo_keywords) . ' ' . htmlspecialchars($this->path) . ' ' . $this->price . ' ' . htmlspecialchars($this->vendorcode) . ' ' . htmlspecialchars($this->marking) . ' ';

		$oSearch_Page->title = $this->name;

		// комментарии к информационному элементу
		$aComments = $this->Comments->findAll();
		foreach ($aComments as $oComment)
		{
			$oSearch_Page->text .= htmlspecialchars($oComment->author) . ' ' . $oComment->text . ' ';
		}

		if (Core::moduleIsActive('tag'))
		{
			$aTags = $this->Tags->findAll();
			foreach ($aTags as $oTag)
			{
				$oSearch_Page->text .= htmlspecialchars($oTag->name) . ' ';
			}
		}

		$aPropertyValues = $this->getPropertyValues();
		foreach ($aPropertyValues as $oPropertyValue)
		{
			// List
			if ($oPropertyValue->Property->type == 3 && Core::moduleIsActive('list'))
			{
				if ($oPropertyValue->value != 0)
				{
					$oList_Item = $oPropertyValue->List_Item;
					$oList_Item->id && $oSearch_Page->text .= htmlspecialchars($oList_Item->value) . ' ';
				}
			}
			// Informationsystem
			elseif ($oPropertyValue->Property->type == 5 && Core::moduleIsActive('informationsystem'))
			{
				if ($oPropertyValue->value != 0)
				{
					$oInformationsystem_Item = $oPropertyValue->Informationsystem_Item;
					if ($oInformationsystem_Item->id)
					{
						$oSearch_Page->text .= htmlspecialchars($oInformationsystem_Item->name) . ' ';
					}
				}
			}
			// Other type
			elseif ($oPropertyValue->Property->type != 2)
			{
				$oSearch_Page->text .= htmlspecialchars($oPropertyValue->value) . ' ';
			}
		}

		// Производитель
		$oShop_Producer = $this->Shop_Producer;
		if ($oShop_Producer->id)
		{
			$oSearch_Page->text .= htmlspecialchars($oShop_Producer->name) . ' ';
		}

		// Продавец
		$oShop_Seller = $this->Shop_Seller;
		if ($oShop_Seller->id)
		{
			$oSearch_Page->text .= htmlspecialchars($oShop_Seller->name) . ' ';
		}

		$oSiteAlias = $this->Shop->Site->getCurrentAlias();
		if ($oSiteAlias)
		{
			$oSearch_Page->url = 'http://' . $oSiteAlias->name
				. $this->Shop->Structure->getPath()
				. $this->getPath();
		}

		$oSearch_Page->size = mb_strlen($oSearch_Page->text);
		$oSearch_Page->site_id = $this->Shop->site_id;
		$oSearch_Page->datetime = !is_null($this->datetime) && $this->datetime != '0000-00-00 00:00:00'
			? $this->datetime
			: date('Y-m-d H:i:s');
		$oSearch_Page->module = 3;
		$oSearch_Page->module_id = $this->shop_id;
		$oSearch_Page->inner = 0;
		$oSearch_Page->module_value_type = 2; // search_page_module_value_type
		$oSearch_Page->module_value_id = $this->id; // search_page_module_value_id

		Core_Event::notify($this->_modelName . '.onAfterIndexing', $this, array($oSearch_Page));

		$oSearch_Page->save();

		Core_QueryBuilder::delete('search_page_siteuser_groups')
			->where('search_page_id', '=', $oSearch_Page->id)
			->execute();

		$oSearch_Page_Siteuser_Group = Core_Entity::factory('Search_Page_Siteuser_Group');
		$oSearch_Page_Siteuser_Group->siteuser_group_id = $this->getSiteuserGroupId();
		$oSearch_Page->add($oSearch_Page_Siteuser_Group);

		return $oSearch_Page;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function adminStatus($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		ob_start();

		$iShopItemId = intval(Core_Array::getGet('shop_item_id', 0));

		$queryBuilder = Core_QueryBuilder::select(array('COUNT(*)', 'count'))
			->from('shop_item_associated')
			->where('shop_item_id', '=', $iShopItemId)
			->where('shop_item_associated_id', '=', $this->id);

		$aItemAssociatedCount = $queryBuilder->execute()->asAssoc()->current();

		$iCount = $aItemAssociatedCount['count'];

		$window_id = $oAdmin_Form_Controller->getWindowId();

		Core::factory('Core_Html_Entity_A')
			->add(
				Core::factory('Core_Html_Entity_Img')
					->src('/admin/images/' . ($iCount ? 'check.gif' : 'not_check.gif'))
			)
			->href($oAdmin_Form_Controller->getAdminActionLoadHref
				(
					"/admin/shop/item/associated/index.php", 'adminChangeAssociated', NULL, 1, $this->id
				))
			->onclick($oAdmin_Form_Controller->getAdminActionLoadAjax
				(
					"/admin/shop/item/associated/index.php", 'adminChangeAssociated', NULL, 1, $this->id
				))
			->execute();

		return ob_get_clean();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function adminSetAssociated()
	{
		$oShopItem = Core_Entity::factory('Shop_Item', Core_Array::getGet('shop_item_id', 0));

		$oShopAssociatedItem = $oShopItem
		->Shop_Item_Associateds
		->getByAssociatedId($this->id);

		if(is_null($oShopAssociatedItem))
		{
			$oShopAssociatedItem = Core_Entity::factory('Shop_Item_Associated');
			$oShopAssociatedItem->shop_item_associated_id = $this->id;
			$oShopAssociatedItem->count = intval(Core_Array::getPost("apply_check_1_{$this->id}_fv_887", 0));
			$oShopItem->add($oShopAssociatedItem);
		}
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function adminUnsetAssociated()
	{
		$oShopItem = Core_Entity::factory('Shop_Item', Core_Array::getGet('shop_item_id', 0));

		$oShopAssociatedItem = $oShopItem
		->Shop_Item_Associateds
		->getByAssociatedId($this->id);

		if(!is_null($oShopAssociatedItem))
		{
			$oShopAssociatedItem->delete();
		}
		return $this;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function adminChangeAssociated()
	{
		$oShopItem = Core_Entity::factory('Shop_Item', Core_Array::getGet('shop_item_id', 0));

		$oShopAssociatedItem = $oShopItem
			->Shop_Item_Associateds
			->getByAssociatedId($this->id);

		!is_null($oShopAssociatedItem)
			? $this->adminUnsetAssociated()
			: $this->adminSetAssociated();
	}

	/**
	 * Delete item directory
	 * @return self
	 */
	public function deleteDir()
	{
		// Удаляем файл большого изображения элемента
		$this->deleteLargeImage();

		// Удаляем файл малого изображения элемента
		$this->deleteSmallImage();

		if (is_dir($this->getItemPath()))
		{
			try
			{
				Core_File::deleteDir($this->getItemPath());
			} catch (Exception $e) {}
		}

		return $this;
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

		// Удаляем значения доп. свойств
		$aPropertyValues = $this->getPropertyValues();
		foreach($aPropertyValues as $oPropertyValue)
		{
			$oPropertyValue->Property->type == 2 && $oPropertyValue->setDir($this->getItemPath());
			$oPropertyValue->delete();
		}

		$this->Shop_Carts->deleteAll(FALSE);

		// Удаляем комментарии
		$this->Comments->deleteAll(FALSE);

		// Удаляем связи со скидками
		$this->Shop_Item_Discounts->deleteAll(FALSE);

		// Электронные товары
		$this->Shop_Item_Digitals->deleteAll(FALSE);

		// Удаляем ярлыки
		$this->Shop_Items->deleteAll(FALSE);

		// Удаляем все модификации товара
		$this->Modifications->deleteAll(FALSE);

		// Удаляем значения дополнительных цен
		$this->Shop_Item_Prices->deleteAll(FALSE);

		// Удаляем связи с ассоциированными товарами, прямая связь
		$this->Shop_Item_Associateds->deleteAll(FALSE);

		// Удаляем связи с ассоциированными товарами, обратная связь
		$this->Shop_Item_Associated_Seconds->deleteAll(FALSE);

		// Удаляем специальные цены
		$this->Shop_Specialprices->deleteAll(FALSE);

		// Удаляем метки
		$this->Tag_Shop_Items->deleteAll(FALSE);

		// Удаляем значения из складов
		$this->Shop_Warehouse_Items->deleteAll(FALSE);

		// Удаляем связи с зарезервированными, прямая связь
		$this->Shop_Item_Reserveds->deleteAll(FALSE);

		// Удаляем директорию товара
		$this->deleteDir();

		if (!is_null($this->Shop_Group->id))
		{
			// Уменьшение количества элементов в группе
			$this->Shop_Group->decCountItems();
		}

		return parent::delete($primaryKey);
	}

	/**
	 * Get item by group id
	 * @param int $group_id group id
	 * @return array
	 */
	public function getByGroupId($group_id)
	{
		$this->queryBuilder()
			//->clear()
			->where('shop_group_id', '=', $group_id)
			->where('shortcut_id', '=', 0);

		return $this->findAll();
	}

	/**
	 * Get item by group id and path
	 * @param int $group_id group id
	 * @param string $path path
	 * @return self|NULL
	 */
	public function getByGroupIdAndPath($group_id, $path)
	{
		$this->queryBuilder()
			//->clear()
			->where('path', '=', $path)
			->where('shop_group_id', '=', $group_id)
			->where('shortcut_id', '=', 0)
			->limit(1);

		$aShop_Items = $this->findAll(FALSE);

		return isset($aShop_Items[0])
			? $aShop_Items[0]
			: NULL;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function name()
	{
		$object = $this->shortcut_id
			? $this->Shop_Item
			: $this;

		$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')->value(
			htmlspecialchars($object->name)
		);

		$bRightTime = ($this->start_datetime == '0000-00-00 00:00:00' || time() > Core_Date::sql2timestamp($this->start_datetime))
			&& ($this->end_datetime == '0000-00-00 00:00:00' || time() < Core_Date::sql2timestamp($this->end_datetime));

		!$bRightTime && $oCore_Html_Entity_Div->class('wrongTime');

		// Зачеркнут в зависимости от статуса родительского товара или своего статуса
		if (!$object->active || !$this->active)
		{
			$oCore_Html_Entity_Div->class('inactive');
		}
		elseif ($bRightTime)
		{
			$oCurrentAlias = $object->Shop->Site->getCurrentAlias();

			if ($oCurrentAlias)
			{
				$href = 'http://' . $oCurrentAlias->name
					. $object->Shop->Structure->getPath()
					. $object->getPath();

				$oCore_Html_Entity_Div
				->add(
					Core::factory('Core_Html_Entity_A')
						->href($href)
						->target('_blank')
						->add(
							Core::factory('Core_Html_Entity_Img')
							->src('/admin/images/new_window.gif')
							->class('img_line')
						)
				);
			}
		}
		elseif (!$bRightTime)
		{
			$oCore_Html_Entity_Div
				->add(
					Core::factory('Core_Html_Entity_Img')
						->src('/admin/images/mesures.gif')
						->class('img_line')
				);
		}

		$oCore_Html_Entity_Div->execute();
	}

	/**
	 * Show comments data in XML
	 * @var boolean
	 */
	protected $_showXmlComments = FALSE;

	/**
	 * Add comments XML to item
	 * @param boolean $showXmlComments mode
	 * @return self;
	 */
	public function showXmlComments($showXmlComments = TRUE)
	{
		$this->_showXmlComments = $showXmlComments;
		return $this;
	}

	/**
	 * What comments show in XML? (active|inactive|all)
	 * @var string
	 */
	protected $_commentsActivity = 'active';

	/**
	 * Set comments filter rule
	 * @param string $commentsActivity (active|inactive|all)
	 * @return self
	 */
	public function commentsActivity($commentsActivity = 'active')
	{
		$this->_commentsActivity = $commentsActivity;
		return $this;
	}

	/**
	 * Show associated items data in XML
	 * @var boolean
	 */
	protected $_showXmlAssociatedItems = FALSE;

	/**
	 * Add associated items XML to item
	 * @param boolean $showXmlAssociatedItems mode
	 * @return self;
	 */
	public function showXmlAssociatedItems($showXmlAssociatedItems = TRUE)
	{
		$this->_showXmlAssociatedItems = $showXmlAssociatedItems;
		return $this;
	}

	/**
	 * Show modifications data in XML
	 * @var boolean
	 */
	protected $_showXmlModifications = FALSE;

	/**
	 * Add modifications XML to item
	 * @param boolean $showXmlModifications mode
	 * @return self;
	 */
	public function showXmlModifications($showXmlModifications = TRUE)
	{
		$this->_showXmlModifications = $showXmlModifications;
		return $this;
	}

	/**
	 * Show special prices data in XML
	 * @var boolean
	 */
	protected $_showXmlSpecialprices = FALSE;

	/**
	 * Add special prices XML to item
	 * @param boolean $showXmlSpecialprices mode
	 * @return self;
	 */
	public function showXmlSpecialprices($showXmlSpecialprices = TRUE)
	{
		$this->_showXmlSpecialprices = $showXmlSpecialprices;
		return $this;
	}

	/**
	 * Show tags data in XML
	 * @var boolean
	 */
	protected $_showXmlTags = FALSE;

	/**
	 * Add tags XML to item
	 * @param boolean $showXmlTags mode
	 * @return self;
	 */
	public function showXmlTags($showXmlTags = TRUE)
	{
		$this->_showXmlTags = $showXmlTags;
		return $this;
	}

	/**
	 * Show items count data in XML
	 * @var boolean
	 */
	protected $_showXmlWarehousesItems = FALSE;

	/**
	 * Add warehouse information to XML
	 * @param boolean $showXmlWarehousesItems show status
	 * @return self
	 */
	public function showXmlWarehousesItems($showXmlWarehousesItems = TRUE)
	{
		$this->_showXmlWarehousesItems = $showXmlWarehousesItems;
		return $this;
	}

	/**
	 * Show user data in XML
	 * @var boolean
	 */
	protected $_showXmlSiteuser = FALSE;

	/**
	 * Add site user information to XML
	 * @param boolean $showXmlSiteuser show status
	 * @return self
	 */
	public function showXmlSiteuser($showXmlSiteuser = TRUE)
	{
		$this->_showXmlSiteuser = $showXmlSiteuser;
		return $this;
	}

	/**
	 * Show votes in XML
	 * @var boolean
	 */
	protected $_showXmlVotes = FALSE;

	/**
	 * Add votes XML to item
	 * @param boolean $showXmlSiteuser mode
	 * @return self
	 */
	public function showXmlVotes($showXmlVotes = TRUE)
	{
		$this->_showXmlVotes = $showXmlVotes;
		return $this;
	}
	
	/**
	 * Show siteuser properties in XML
	 * @var boolean
	 */
	protected $_showXmlSiteuserProperties = FALSE;

	/**
	 * Show siteuser properties in XML
	 * @param boolean $showXmlSiteuserProperties mode
	 * @return self
	 */
	public function showXmlSiteuserProperties($showXmlSiteuserProperties = TRUE)
	{
		$this->_showXmlSiteuserProperties = $showXmlSiteuserProperties;
		return $this;
	}

	/**
	 * Show properties in XML
	 * @var boolean
	 */
	protected $_showXmlProperties = FALSE;

	/**
	 * Show properties in XML
	 * @param mixed $showXmlProperties array of allowed properties ID or boolean
	 * @return Comment_Model
	 */
	public function showXmlProperties($showXmlProperties = TRUE)
	{
		$this->_showXmlProperties = is_array($showXmlProperties)
			? array_combine($showXmlProperties, $showXmlProperties)
			: $showXmlProperties;

		return $this;
	}

	/**
	 * Количество товара в корзине
	 */
	protected $_cartQuantity = 1;

	/**
	 * Set item quantity into cart
	 * @param int $cartQuantity quantity of item in the cart
	 * @return self
	 */
	public function cartQuantity($cartQuantity)
	{
		$this->_cartQuantity = $cartQuantity;
		return $this;
	}

	/**
	 * Get item quantity into cart
	 * @return int
	 */
	public function getCartQuantity()
	{
		return $this->_cartQuantity;
	}

	/**
	 * Array of comments, [parent_id] => array(comments)
	 * @var array
	 */
	protected $_aComments = array();

	/**
	 * Set array of comments for getXml()
	 * @param array $aComments
	 * @return self
	 */
	public function setComments(array $aComments)
	{
		$this->_aComments = $aComments;
		return $this;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_item.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$oShop = $this->Shop;

		$this->clearXmlTags()
			->addXmlTag('url', $this->Shop->Structure->getPath() . $this->getPath());

		!isset($this->_forbiddenTags['date']) && $this->addXmlTag('date', strftime($oShop->format_date, Core_Date::sql2timestamp($this->datetime)));

		$this
			->addXmlTag('datetime', strftime($oShop->format_datetime, Core_Date::sql2timestamp($this->datetime)))
			->addXmlTag('start_datetime', $this->start_datetime == '0000-00-00 00:00:00'
				? $this->start_datetime
				: strftime($oShop->format_datetime, Core_Date::sql2timestamp($this->start_datetime)))
			->addXmlTag('end_datetime', $this->end_datetime == '0000-00-00 00:00:00'
				? $this->end_datetime
				: strftime($oShop->format_datetime, Core_Date::sql2timestamp($this->end_datetime)));

		!isset($this->_forbiddenTags['dir']) && $this->addXmlTag('dir', $this->getItemHref());

		if ($this->_showXmlVotes && Core::moduleIsActive('siteuser'))
		{
			$aRate = Vote_Controller::instance()->getRateByObject($this);

			$this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('rate')
					->value($aRate['rate'])
					->addAttribute('likes', $aRate['likes'])
					->addAttribute('dislikes', $aRate['dislikes'])
			);

			if (!is_null($oCurrentSiteuser = Core_Entity::factory('Siteuser')->getCurrent()))
			{
				$oVote = $this->Votes->getBySiteuser_Id($oCurrentSiteuser->id);
				!is_null($oVote) && $this->addEntity($oVote);
			}
		}

		if ($this->_showXmlSiteuser && $this->siteuser_id && Core::moduleIsActive('siteuser'))
		{
			$this->Siteuser->showXmlProperties($this->_showXmlSiteuserProperties);
			$this->addEntity($this->Siteuser);
		}

		$this->_showXmlTags && Core::moduleIsActive('tag') && $this->addEntities($this->Tags->findAll());

		$this->_showXmlWarehousesItems && $this->addEntities($this->Shop_Warehouse_Items->findAll());

		// Digital item
		if ($this->type == 1)
		{
			$this->addXmlTag('digitals', $this->Shop_Item_Digitals->getCountDigitalItems());
		}

		// Warehouses rest
		!isset($this->_forbiddenTags['rest']) && $this->addXmlTag('rest', $this->getRest());

		// Reserved
		!isset($this->_forbiddenTags['reserved']) && $this->addXmlTag('reserved', $oShop->reserve
			? $this->getReserved()
			: 0);

		// Prices
		$oShop_Item_Controller = new Shop_Item_Controller();
		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
			$oSiteuser && $oShop_Item_Controller->siteuser($oSiteuser);
		}
		$oShop_Item_Controller->count($this->_cartQuantity);
		$aPrices = $oShop_Item_Controller->getPrices($this);

		// Будет совпадать с ценой вместе с налогом
		$this->addXmlTag('price', $aPrices['price_discount']);
		!isset($this->_forbiddenTags['discount']) && $this->addXmlTag('discount', $aPrices['discount']);
		!isset($this->_forbiddenTags['tax']) && $this->addXmlTag('tax', $aPrices['tax']);
		!isset($this->_forbiddenTags['price_tax']) && $this->addXmlTag('price_tax', $aPrices['price_tax']);

		count($aPrices['discounts']) && $this->addEntities($aPrices['discounts']);

		// Валюта от магазина
		$this->shop_currency_id && $this->addXmlTag('currency', $this->Shop->Shop_Currency->name);

		$this->shop_seller_id && !isset($this->_forbiddenTags['shop_seller']) && $this->addEntity($this->Shop_Seller);
		$this->shop_producer_id && !isset($this->_forbiddenTags['shop_producer']) && $this->addEntity($this->Shop_Producer);
		$this->shop_measure_id && !isset($this->_forbiddenTags['shop_measure']) && $this->addEntity($this->Shop_Measure);

		// Modifications
		if ($this->_showXmlModifications)
		{
			$oShop_Items_Modifications = $this->Modifications;

			Core_Event::notify($this->_modelName . '.onBeforeShowXmlModifications', $this, array($oShop_Items_Modifications));

			switch ($oShop->items_sorting_direction)
			{
				case 1:
					$items_sorting_direction = 'DESC';
				break;
				case 0:
				default:
					$items_sorting_direction = 'ASC';
			}

			// Определяем поле сортировки товаров
			switch ($oShop->items_sorting_field)
			{
				case 1:
					$oShop_Items_Modifications
						->queryBuilder()
						->clearOrderBy()
						->orderBy('shop_items.name', $items_sorting_direction)
						->orderBy('shop_items.sorting', $items_sorting_direction);
					break;
				case 2:
					$oShop_Items_Modifications
						->queryBuilder()
						->clearOrderBy()
						->orderBy('shop_items.sorting', $items_sorting_direction)
						->orderBy('shop_items.name', $items_sorting_direction);
					break;
				case 0:
				default:
					$oShop_Items_Modifications
						->queryBuilder()
						->clearOrderBy()
						->orderBy('shop_items.datetime', $items_sorting_direction)
						->orderBy('shop_items.sorting', $items_sorting_direction);
			}

			$aShop_Items_Modifications = $oShop_Items_Modifications->getAllByActive(1);

			if (count($aShop_Items_Modifications))
			{
				$oModificationEntity = Core::factory('Core_Xml_Entity')
					->name('modifications');

				$this->addEntity($oModificationEntity);

				$aForbiddenTags = $this->getForbiddenTags();

				foreach ($aShop_Items_Modifications as $oShop_Items_Modification)
				{
					$oShop_Items_Modification->clearEntities();

					// Apply forbidden tags for modifications
					foreach ($aForbiddenTags as $tagName)
					{
						$oShop_Items_Modification->addForbiddenTag($tagName);
					}

					$oShop_Items_Modification
						->showXmlComments($this->_showXmlComments)
						->showXmlAssociatedItems(FALSE)
						->showXmlModifications(FALSE)
						->showXmlSpecialprices($this->_showXmlSpecialprices)
						->showXmlTags($this->_showXmlTags)
						->showXmlWarehousesItems($this->_showXmlWarehousesItems)
						->showXmlSiteuser($this->_showXmlSiteuser)
						->showXmlProperties($this->_showXmlProperties);

					Core_Event::notify($this->_modelName . '.onBeforeAddModification', $this, array(
						$oShop_Items_Modification
					));

					$oModificationEntity->addEntity(
						$oShop_Items_Modification
					);
				}
			}
		}

		$this->_showXmlSpecialprices && $this->addEntities($this->Shop_Specialprices->findAll());

		// Associated items
		if ($this->_showXmlAssociatedItems)
		{
			$aShop_Item_Associateds = $this->Item_Associateds->getAllByActive(1);

			if (count($aShop_Item_Associateds))
			{
				$oAssociatedEntity = Core::factory('Core_Xml_Entity')
					->name('associated');

				$this->addEntity($oAssociatedEntity);

				$aForbiddenTags = $this->getForbiddenTags();

				foreach ($aShop_Item_Associateds as $oShop_Item_Associated_Original)
				{
					if ($oShop_Item_Associated_Original->id != $this->id)
					{
						// Сопутствующий товар может быть в списке, соответственное его модификации не выведутся из-за запрета на вывод модификаций для сопутствующих
						$oShop_Item_Associated = clone $oShop_Item_Associated_Original;
						$oShop_Item_Associated->id = $oShop_Item_Associated_Original->id;
						$oShop_Item_Associated->clearEntities();

						// Apply forbidden tags for modifications
						foreach ($aForbiddenTags as $tagName)
						{
							$oShop_Item_Associated->addForbiddenTag($tagName);
						}

						$oShop_Item_Associated
							->showXmlComments($this->_showXmlComments)
							->showXmlAssociatedItems(FALSE)
							->showXmlModifications(FALSE)
							->showXmlSpecialprices($this->_showXmlSpecialprices)
							->showXmlTags($this->_showXmlTags)
							->showXmlWarehousesItems($this->_showXmlWarehousesItems)
							->showXmlSiteuser($this->_showXmlSiteuser)
							->showXmlProperties($this->_showXmlProperties);

						Core_Event::notify($this->_modelName . '.onBeforeAddAssociatedEntity', $this, array(
							$oShop_Item_Associated
						));

						$oAssociatedEntity->addEntity(
							$oShop_Item_Associated
						);
					}
				}
			}
		}

		if ($this->_showXmlComments)
		{
			$this->_aComments = array();

			$gradeSum = 0;
			$gradeCount = 0;

			$oComments = $this->Comments;
			$oComments->queryBuilder()
				->orderBy('datetime', 'DESC');

			// учитываем заданную активность комментариев
			$this->_commentsActivity = strtolower($this->_commentsActivity);
			if ($this->_commentsActivity != 'all')
			{
				$oComments->queryBuilder()
					->where('active', '=', $this->_commentsActivity == 'inactive' ? 0 : 1);
			}

			$aComments = $oComments->findAll();
			foreach ($aComments as $oComment)
			{
				if ($oComment->grade > 0)
				{
					$gradeSum += $oComment->grade;
					$gradeCount++;
				}
				$this->_aComments[$oComment->parent_id][] = $oComment;
			}

			// Средняя оценка
			$avgGrade = $gradeCount > 0
				? $gradeSum / $gradeCount
				: 0;

			$fractionalPart = $avgGrade - floor($avgGrade);
			$avgGrade = floor($avgGrade);

			if ($fractionalPart >= 0.25 && $fractionalPart < 0.75)
			{
				$avgGrade += 0.5;
			}
			elseif ($fractionalPart >= 0.75)
			{
				$avgGrade += 1;
			}

			!isset($this->_forbiddenTags['comments_count']) && $this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('comments_count')
					->value(count($aComments))
			);

			!isset($this->_forbiddenTags['comments_grade_sum']) && $this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('comments_grade_sum')
					->value($gradeSum)
			);

			!isset($this->_forbiddenTags['comments_grade_count']) && $this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('comments_grade_count')
					->value($gradeCount)
			);

			!isset($this->_forbiddenTags['comments_average_grade']) && $this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('comments_average_grade')
					->value($avgGrade)
			);

			$this->_addComments(0, $this);
		}

		$this->_aComments = array();

		if ($this->_showXmlProperties)
		{
			if (is_array($this->_showXmlProperties))
			{
				$aProperty_Values = Property_Controller_Value::getPropertiesValues($this->_showXmlProperties, $this->id);

				foreach ($aProperty_Values as $oProperty_Value)
				{
					if ($oProperty_Value->Property->type == 2)
					{
						$oProperty_Value
							->setHref($this->getItemHref())
							->setDir($this->getItemPath());
					}

					/*isset($this->_showXmlProperties[$oProperty_Value->property_id]) && */$this->addEntity(
						$oProperty_Value
					);
				}
			}
			else
			{
				$aProperty_Values = $this->getPropertyValues();
				// Add all values
				$this->addEntities($aProperty_Values);
			}
		}

		return parent::getXml();
	}

	/**
	 * Add comments into object XML
	 * @param int $parent_id parent comment id
	 * @param Core_Entity $parentObject object
	 * @return self
	 * @hostcms-event shop_item.onBeforeAddComments
	 * @hostcms-event shop_item.onAfterAddComments
	 */
	protected function _addComments($parent_id, $parentObject)
	{
		Core_Event::notify($this->_modelName . '.onBeforeAddComments', $this, array(
			$parent_id, $parentObject, $this->_aComments
		));

		if (isset($this->_aComments[$parent_id]))
		{
			foreach ($this->_aComments[$parent_id] as $oComment)
			{
				$parentObject->addEntity($oComment
					->clearEntities()
					->showXmlProperties($this->_showXmlSiteuserProperties)
					->showXmlVotes($this->_showXmlVotes)
					->dateFormat($this->Shop->format_date)
					->dateTimeFormat($this->Shop->format_datetime)
				);

				$this->_addComments($oComment->id, $oComment);
			}
		}

		Core_Event::notify($this->_modelName . '.onAfterAddComments', $this, array(
			$parent_id, $parentObject, $this->_aComments
		));

		return $this;
	}

	/**
	 * Create item
	 * @return self
	 */
	public function create()
	{
		$return = parent::create();

		if (!is_null($this->Shop_Group->id))
		{
			// Увеличение количества элементов в группе
			$this->Shop_Group->incCountItems();
		}

		return $return;
	}
}