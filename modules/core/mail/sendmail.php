<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Sendmail driver
 *
 * @package HostCMS 6\Core\Mail
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2012 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Mail_Sendmail extends Core_Mail
{
	/**
	 * Send mail
	 * @param string $to recipient
	 * @param string $subject subject
	 * @param string $message message
	 * @param string $additional_headers additional headers
	 * @return self
	 */
	protected function _send($to, $subject, $message, $additional_headers)
	{
		$this->_status = @mail($to, $subject, $message, $additional_headers);
		return $this;
	}
}