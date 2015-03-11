<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * span entity
 *
 * @package HostCMS 6\Core\Html
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2012 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Html_Entity_Span extends Core_Html_Entity
{
	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperies = array(
		'value' // идет в значение <span>
	);
	
	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();

		echo PHP_EOL;
		
		// htmlspecialchars($this->value) - acronym
		?><span <?php echo implode(' ', $aAttr) ?>><?php echo $this->value?><?php
		parent::execute();
		?></span><?php
	}
}