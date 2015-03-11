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
class Shop_Item_Comment_Model extends Comment_Model
	/**
	 * Name of the table
	 * @var string
	 */

	/**
	 * Name of the model
	 * @var string
	 */
	/**
	 * Backend callback method
	 * @return string
	 */
		$oSite = $oShop_Item->Shop->Site;
		$oSite_Alias = $oSite->getCurrentAlias();
		!is_null($oSite_Alias) && $href = 'http://' . $oSite_Alias->name . $href;


	/**
	 * Copy object
	 * @return Core_Entity
	 */
	public function copy()
	{
		// save original _nameColumn
		$nameColumn = $this->_nameColumn;
		$this->_nameColumn = 'subject';


		// restore original _nameColumn
		$this->_nameColumn = $nameColumn;
