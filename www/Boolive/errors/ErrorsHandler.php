<?php
/**
 * Обработчик ошибок и исключений системы
 *
 * @version 2.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\errors;

use Exception, ErrorException,
    Boolive\events\Events;

class ErrorsHandler
{
    /** @var Текущий уровень фиксации ошибок в настройках PHP */
    static private $error_reporting;

    /**
     * Активация модуля
     */
    static function activate(){
        self::$error_reporting = error_reporting();
        // Регистрация обработчика исключений
        set_exception_handler(array('\Boolive\errors\ErrorsHandler', 'exceptionHandler'));
        // Регистрация обработчика ошибок
        set_error_handler(array('\Boolive\errors\ErrorsHandler', 'errorHandler'));
    }

    /**
     * Обработчик исключений
     * Вызывается автоматически при исключениях и ошибках
     *
     * @param \Exception $e Исключение
     * @return bool
     */
    static function exceptionHandler($e)
    {
        Events::send('ERRORS_SYSTEM', $e);
        error_log((string)$e);
        // @TODO Заменить на юзабильное отображение
        if (isset($e->xdebug_message)){
            echo '<table cellspacing="0" cellpadding="1" border="1" dir="ltr">'.$e->xdebug_message.'</table>';
        }else{
            trace($e, 'SYSTEM ERROR');
        }
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
    static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        if (!(self::$error_reporting & $errno)){
            return false;
        }
        throw new errorException($errstr, $errno, 0, $errfile, $errline);
    }
}
