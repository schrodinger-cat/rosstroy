<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Http socket driver
 *
 * @package HostCMS 6\Core\Http
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2014 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Http_Socket extends Core_Http
{
	/**
	 * Send request
	 * @param string $host host
	 * @param string $path path
	 * @param string $query query
	 * @return self
	 */
	protected function _execute($host, $path, $query)
	{
		// для 443 порта в fsockopen перед хостом нужно добавлять ssl://
		$socketHost = $this->_port == 443
			? 'ssl://' . $host
			: $host;

		$fp = @fsockopen($socketHost, $this->_port, $errno, $errstr, $this->_timeout);

		if (!$fp)
		{
			throw new Core_Exception("Fsockopen failed '%errstr' (%errno)",
				array('%errstr' => $errstr, '%errno' => $errno), $errno
			);
		}

		$out = "{$this->_method} {$path}{$query} HTTP/1.0\r\n";
		$out .= "Content-Type: {$this->_contentType}\r\n";
		$out .= "Referer: {$this->_referer}\r\n";
		$out .= "User-Agent: {$this->_userAgent}\r\n";

		// Additional headers
		foreach ($this->_additionalHeaders as $name => $value)
		{
			$out .= "{$name}: {$value}\r\n";
		}

		$out .= "Host: {$host}\r\n";

		$bIsPost = $this->_method != 'GET' && ($this->_rawData || count($this->_data) > 0);

		if ($bIsPost)
		{
			if ($this->_rawData)
			{
				$sPost = $this->_rawData;
			}
			else
			{
				$aData = array();
				foreach ($this->_data as $key => $value)
				{
					$aData[] = urlencode($key) . '=' . urlencode($value);
				}

				$sPost = implode('&', $aData);
			}

			$out .= "Content-length: " . strlen($sPost) . "\r\n";
		}

		$out .= "Connection: Close\r\n\r\n";

		if ($bIsPost)
		{
			$out .= $sPost . "\r\n\r\n";
		}

		fwrite($fp, $out);

		if (function_exists('stream_set_timeout'))
		{
			stream_set_timeout($fp, $this->_timeout);
		}

		$datastr = '';

		while (!feof($fp))
		{
			$datastr .= fgets($fp, 65536);
		}

		fclose($fp);

		$aTmp = explode("\r\n\r\n", $datastr, 2);
		unset ($datastr);

		$this->_headers = Core_Array::get($aTmp, 0);
		$this->_body = Core_Array::get($aTmp, 1);

		return $this;
	}
}