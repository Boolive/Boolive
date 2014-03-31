<?php
/**
 * Boolive!
 * Главный исполняемый файл. Запуск движка и проекта
 *
 * @version 2
 * @author Vladimir Shestakov <boolive@yandex.ru>
 * @link http://boolive.ru
 * @requirement PHP 5.3 или новее
 */
use Boolive\Boolive;
use Boolive\installer\Installer;
// Подключение конфигурации путей
include 'config.php';
// Подключение движка Boolive
include DIR_SERVER.'Boolive/Boolive.php';
// Активация Boolive
if (Boolive::activate()){
    // Запуск ядра, обработка запроса
    Boolive::start();
}else{
    // Запуск установщика, если Boolive не активирован
    include DIR_SERVER.'Boolive/installer/Installer.php';
    Installer::start();
}