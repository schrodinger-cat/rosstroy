<?php

/**
 * PayAnyWay
 *
 * https://code.google.com/p/payanyway/wiki/hostcms6
 *
 * Для установки платежного модуля PayAnyWay необходимо произвести следующие действия:
 * 1. В код настроек типовой динамической страницы корзины добавляем содержимое файла page_config.php
 * 2. В код типовой динамической страницы корзины добавляем содержимое файла page.php
 * 3. Добавляем обработчик платежной системы. Вставляем полностью код из файла payanyway.php
 *    Внимание! Имя класса зависит от идентификатора платежной системы, например, для платежной системы 23 имя будет
 *    class Shop_Payment_System_Handler23 extends Shop_Payment_System_Handler
 *
 *  Измените настройки модуля PayAnyWay:
 *
 *     Номер счета - номер счета в платежной системе PayAnyWay
 *     Код проверки целостности данных - должен совпадать с кодом, указанным в настройках счета
 *     Тестовый режим - идентификатор режима ("0" или "1")
 *     URL сервера оплаты - возможны два варианта:
 * 	- demo.moneta.ru (для тестового аккаунта на demo.moneta.ru)
 * 	- www.payanyway.ru (для рабочего аккаунта в платежной системе PayAnyWay)
 *
 * 4. Зайдите в ваш акаунт в платежной системе и перейдите в раздел «Счета» -> «Управление» -> «Редактировать счет»
 *    Впишите следующий адрес в поле «Pay URL»: http://имя_вашего_сайта/shop/cart/
 *
 * Удачных платежей!
 *
 */
class Shop_Payment_System_Handler23 extends Shop_Payment_System_Handler
{
	public $_rub_currency_id = 1;

	/* Номер счета в платежной системе PayAnyWay */
	private $_MNT_ID = 99999999;

	/* Код проверки целостности данных */
	private $_MNT_DATAINTEGRITY_CODE = 'xxxxx';

	/* URL сервера оплаты */
	private $_MNT_PAYMENT_URL = 'www.payanyway.ru';

	/* Тестовый режим */
	private $_MNT_TEST_MODE = 1;

	/* Вызывается на 4-ом шаге оформления заказа*/
	public function execute()
	{
		parent::execute();

		$this->printNotification();

		return $this;
	}

	public function paymentProcessing()
	{
		/* обработка ответа от платёжной системы */
		if (isset($_REQUEST['MNT_OPERATION_ID']))
		{
			$this->ProcessResult();
			return TRUE;
		}
		else
		{
			$this->ShowResultMessage();
			return TRUE;
		}
	}

	/* вычисление суммы товаров заказа */
	public function getSumWithCoeff()
	{
		return Shop_Controller::instance()->round(($this->_rub_currency_id > 0
				&& $this->_shopOrder->shop_currency_id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$this->_shopOrder->Shop_Currency,
				Core_Entity::factory('Shop_Currency', $this->_rub_currency_id)
			)
			: 0) * $this->_shopOrder->getAmount() );
	}

	protected function _processOrder()
	{
		parent::_processOrder();

		// Установка XSL-шаблонов в соответствии с настройками в узле структуры
		$this->setXSLs();

		// Отправка писем клиенту и пользователю
		$this->send();

		return $this;
	}

	/* оплачивает заказ */
	function ProcessResult()
	{
		$order_id = intval(Core_Array::getRequest('MNT_TRANSACTION_ID'));
		$oShop_Order = Core_Entity::factory('Shop_Order')->find($order_id);
		if ($oShop_Order)
		{
			$signature = md5(
				Core_Array::getRequest('MNT_ID') .
				Core_Array::getRequest('MNT_TRANSACTION_ID') .
				Core_Array::getRequest('MNT_OPERATION_ID') .
				Core_Array::getRequest('MNT_AMOUNT') .
				Core_Array::getRequest('MNT_CURRENCY_CODE') .
				Core_Array::getRequest('MNT_TEST_MODE') .
				$this->_MNT_DATAINTEGRITY_CODE
			);

			if (Core_Array::getRequest('MNT_SIGNATURE') == $signature)
			{
				$this->shopOrder($oShop_Order)->shopOrderBeforeAction(clone $oShop_Order);

				$oShop_Order->system_information = "Товар оплачен через PayAnyWay.\n";
				$oShop_Order->paid();
				$this->setXSLs();
				$this->send();

				ob_start();
				$this->changedOrder('changeStatusPaid');
				ob_get_clean();

				die("SUCCESS");
			}
			else
			{
				die('FAIL');
			}
		}
		else
		{
			die('FAIL');
		}
	}

	// Вывод сообщения об успешности/неуспешности оплаты
	function ShowResultMessage()
	{
		$oShop_Order = Core_Entity::factory('Shop_Order')->find(Core_Array::getRequest('MNT_TRANSACTION_ID', 0));

		if(is_null($oShop_Order->id))
		{
			// Заказ не найден
			return FALSE;
		}

		$sStatus = $oShop_Order->paid == 1 ? "оплачен" : "не оплачен";

		?><h1>Заказ <?php echo $sStatus?></h1>
		<p>Заказ <strong>№ <?php echo $oShop_Order->invoice?></strong> <?php echo $sStatus?>.</p>
		<?php
	}

	/* печатает форму отправки запроса на сайт платёжной системы */
	public function getNotification()
	{
		$Sum = $this->getSumWithCoeff();
		$Signature = md5("{$this->_MNT_ID}{$this->_shopOrder->id}{$Sum}{$this->_shopOrder->Shop_Currency->code}{$this->_MNT_TEST_MODE}{$this->_MNT_DATAINTEGRITY_CODE}");
		?>
		<h2>Оплата через систему PayAnyWay</h2>

		<form method="POST" action="https://<?php echo $this->_MNT_PAYMENT_URL?>/assistant.htm">
		<input type="hidden" name="MNT_ID" value="<?php echo $this->_MNT_ID?>">
		<input type="hidden" name="MNT_TRANSACTION_ID" value="<?php echo $this->_shopOrder->id?>">
		<input type="hidden" name="MNT_CURRENCY_CODE" value="<?php echo $this->_shopOrder->Shop_Currency->code?>">
		<input type="hidden" name="MNT_TEST_MODE" value="<?php echo $this->_MNT_TEST_MODE?>">
		<input type="hidden" name="MNT_SIGNATURE" value="<?php echo $Signature?>">

		<table border = "1" cellspacing = "0" width = "400" bgcolor = "#FFFFFF" align = "center" bordercolor = "#000000">
			<tr>
				<td>Сумма, руб.</td>
				<td> <input type="text" name="MNT_AMOUNT" value="<?php echo $Sum?>" readonly="readonly"> </td>
			</tr>
			<tr>
				<td>Номер заказа</td>
				<td> <input type="text" name="AccountNumber" value="<?php echo $this->_shopOrder->invoice?>" readonly="readonly"> </td>
			</tr>
		</table>

		<table border="0" cellspacing="1" align="center" width="400" bgcolor="#CCCCCC" >
			<tr bgcolor="#FFFFFF">
				<td width="490"></td>
				<td width="48"><input type="submit" name = "BuyButton" value = "Submit"></td>
			</tr>
		</table>
		</form>
	<?php
	}

	public function getInvoice()
	{
		return $this->getNotification();
	}
}