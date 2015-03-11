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
class Tag_Module extends Core_Module
	 * Module version
	 * @var string
	 */
	public $version = '6.1';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2015-01-29';
	/**
	 * Constructor.
	 */
		parent::__construct();
		
				'ico' => 'fa-tags',