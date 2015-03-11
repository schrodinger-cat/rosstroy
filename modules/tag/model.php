<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Tags.
 *
 * @package HostCMS 6\Tag
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2014 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Tag_Model extends Core_Entity
	/**
	 * Backend property
	 * @var int
	 */

	/**
	 * Backend property
	 * @var string
	 */

	/**
	 * Backend property
	 * @var string
	 */

	/**
	 * Backend property
	 * @var string
	 */
	public $count = NULL;
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	/**
	 * Utilized for reading data from inaccessible properties
	 * @param string $property property name
	 * @return mixed
	 */
	public function __get($property)
	{
		if (in_array($property, array('site_count', 'all_count')))
		{
			$this->_calculateCounts();
			$name = '_' . $property;
			return $this->$name;
		}

		return parent::__get($property);
	}

	/**
	 * Triggered by calling isset() or empty() on inaccessible properties
	 * @param string $property property name
	 * @return boolean
	 */
	public function __isset($property)
	{
		$property = strtolower($property);
		if (in_array($property, array('site_count', 'all_count')))
		{
			return TRUE;
		}

		return parent::__isset($property);
	}

	/**
	 * Calculate count
	 */
	/**
	 * Set path
	 * @return self
	 */
	/**
	 * Check if there another tag with this name is
	 * @return self
	 */
	/**
	 * Check if there another tag with this name is
	 * @return self
	 */

	/**
	 * Save object. Use self::update() or self::create()
	 * @return Tag_Model
	 */
	/**
	 * Merge tags
	 * @param Tag_Model $oObject
	 * @return self
	 */
			$oTmp = $this->Tag_Informationsystem_Items->getByInformationsystem_item_id($oTag_Informationsystem_Item->informationsystem_item_id, FALSE);

			is_null($oTmp)
				? $this->add($oTag_Informationsystem_Item)
				: $oTag_Informationsystem_Item->delete();

			is_null($oTmp)
				? $this->add($oTag_Shop_Item)
				: $oTag_Shop_Item->delete();
	/**
	 * Move tag to another dir
	 * @param int $tag_dir_id dir id
	 * @return self
	 */
	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 */
		return parent::delete($primaryKey);
	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event tag.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->clearXmlTags()
			->addXmlTag('urlencode', rawurlencode($this->path));

		if (!is_null($this->count))
		{
			$this->addXmlTag('count', $this->count);
		}

		return parent::getXml();
	}

	/**
	 * Convert object to string
	 * @return string
	 */
}