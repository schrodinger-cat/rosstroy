<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Information systems.
 *
 * @package HostCMS 6\Informationsystem
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2014 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Informationsystem_Item_Comment_Controller_Edit extends Comment_Controller_Edit
	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Informationsystem_Item_Comment_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
				? Core_Entity::factory('Comment', $this->_object->parent_id)->Comment_Informationsystem_Item->informationsystem_item_id
				: Core_Array::getGet('informationsystem_item_id'));

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));