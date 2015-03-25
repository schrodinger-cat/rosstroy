<div class="r-title">Обратная связь</div>

<?php
	if(isset($_POST['r-submit'])) {
		if(!empty($_POST['r-mail']) && !empty($_POST['r-text'])) {

			$email = 'mail@mail.com';
			$subject = 'Форма обратной связи на сайте';

			$message_mail  = 'На сайте была дана обратная связь<br>';
			$message_mail .= 'e-mail: '.htmlspecialchars($_POST['r-mail']).'<br>';
			$message_mail .= 'Сообщение: '.htmlspecialchars($_POST['r-text']);

			Core_Mail::instance()
			    ->to($email)
			    ->from($email)
			    ->subject($subject)
			    ->message($message_mail)
			    ->contentType('text/html')
			    ->header('X-HostCMS-Reason', 'Alert')
			    ->header('Precedence', 'bulk')
			    ->send();
		} else {
			print "Ошибка: не заполнены необходимые поля";
		}
	}
?>

<div class="r-form">
	<form action="/contacts/feedback/" method="post">
		<div class="r-form__elem">
			e-mail
			<input type="text" name="r-mail" class="r-form__input">
		</div>

		<div class="r-form__elem">
			Сообщение
			<textarea name="r-text" class="r-form__input r-form__input_textarea"></textarea>
		</div>

		<div class="r-form__elem r-form__elem_center">
			<input type="submit" class="r-form__button" name="r-submit" value="Отправить">
		</div>
	</form>
</div>