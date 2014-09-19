<?php
/**
 * Boolive!
 * Главный исполняемый файл. Запуск проекта
 *
 * @version 2
 * @author Vladimir Shestakov <boolive@yandex.ru>
 * @link http://boolive.ru
 * @requirement PHP 5.3 или новее
 */
/** @const Времея начала работы системы */
define('START_TIME', microtime(true));
/** @cont bool Установлена ли система Boolive? */
define('IS_INSTALL', true);
/** @const Версия системы Boolive */
define('VERSION', '2.0.beta.2014.02.10');
/** @cont string Директория сайта на сервере. Слеш в конце обязателен! */
define('DIR', __DIR__.'/');
/** @const string Директория временных файлов на сервере. Слеш в конце обязателен! */
define('DIR_TEMP', DIR.'_temp_/');
/** @const string Директория конфигураций. Слеш в конце обязателен! */
define('DIR_CONFIG', DIR.'config/');
// Адрес сайта, например: boolive.ru. Значение по умолчанию для CLI режима
define('HTTP_HOST', empty($_SERVER['HTTP_HOST'])?'boolive.ru' : $_SERVER['HTTP_HOST']);
/* Признак, выводить всю трассировку?*/
define('GLOBAL_TRACE', true);
/* Признак, профилировать запросы к модулю даных?*/
define('PROFILE_DATA', false);

// Подключение конфигурации путей
//include 'config.php';
// Composer autoload
include DIR.'vendor/autoload.php';
// Подключение движка Boolive
include DIR.'boolive/Boolive.php';
// Запуск проекта на Boolive
boolive\Boolive::start();

//boolive\Boolive::activate();