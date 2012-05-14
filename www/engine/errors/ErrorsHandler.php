<?php
/**
 * Обработчик ошибок и исключений системы
 *
 * @version	2.0
 */
namespace Engine;

use Exception,
	ErrorException;

class ErrorsHandler{
	static private $error_reporting;
	/**
	 * Активация модуля
	 */
	static function Activate(){
		self::$error_reporting = error_reporting();
		// Регистрация обработчика исключений
		set_exception_handler(array('\Engine\ErrorsHandler', 'ExceptionHandler'));
		// Регистрация обработчика ошибок
		set_error_handler(array('\Engine\ErrorsHandler', 'ErrorHandler'));
	}

	/**
	 * Обработчик исключений
	 * Вызывается автоматически при исключениях и ошибках
	 *
	 * @param \Exception $e Исключение
	 * @param bool $fatal Признак фатальности ошибки
	 * @return bool
	 */

	static function ExceptionHandler($e, $fatal = true){
		self::Log($e);
//		if ($fatal){
//			$result = Events::Send('ERRORS_SYSTEM', $e);
//			if ($result->count == 0){
//				if (DEBUG){
					//echo 'System error';
		ob_clean();
					echo trace($e, 'SYSTEM ERROR');
//				}
//			}
//		}
		return true;
	}

	/**
	 * Обработчик ошбок PHP
	 * Преобразование php ошибки в исключение для стандартизации их обработки
	 * @param $errno
	 * @param $errstr
	 * @param $errfile
	 * @param $errline
	 * @throws \ErrorException
	 * @return bool
	 */
	static function ErrorHandler($errno, $errstr, $errfile, $errline){
		if (!(self::$error_reporting & $errno)){
			return false;
		}
		throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
	}

	/**
	 * Запись ошибки в log-файл
	 * @param \Exception $e Исключение
	 */
	static function Log($e){
		error_log((string)$e);
	}
}
