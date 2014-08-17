<?php
/**
 * Модуль для отправки электронной почты
 *
 * @version	1.0
 */
namespace boolive\mail;

class Mail
{
	/**
	 * Оправка письма.
	 * Внимание: никакие входящие параметры не фильтруются
	 * в т.ч. не учитываются проблемы кодировки $from, $reply и $to если там будет написано имя (хотя $subject кодируется норм)
	 *
	 * @param string $to Валидный e-mail получателя
	 * @param string $subject Тема письма
	 * @param string $message Текст письма
	 * @param string $from Валидный e-mail отправителя
	 * @param string $reply Валидный e-mail того, кому будет посылаться ответ на письмо
     * @return bool
     */
	static function send($to, $subject, $message, $from, $reply = null)
    {
		// Подготавливаем заголовки
		if (empty($reply)){
			$reply = $from;
		}
		$reply = "Reply-To: $reply\r\n";
		$headers = "MIME-Version: 1.0\r\n".
				"Content-Type: text/html; charset=UTF-8\r\n".
				"From: $from\r\n".
				$reply.
				"X-Mailer: PHP/".phpversion();
		$subject = mb_encode_mimeheader($subject, 'UTF-8', 'B', "\r\n", 9);

		// Отправляем либо кладем в очередь
		return mail($to, $subject, $message, $headers);
	}
}
?>