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
// Подключение конфигурации путей
include 'config.php';
// Подключение движка Boolive
include DIR_SERVER.'Boolive/Boolive.php';
// Активация Boolive
if (\Boolive\Boolive::activate()){
    // Запуск ядра, обработка запроса
    echo \Boolive\data\Data::read()->start(new \Boolive\commands\Commands(), \Boolive\input\Input::getSource());
}else{
    // Запуск установщика, если Boolive не активирован
    include DIR_SERVER.'Boolive/installer/Installer.php';
    \Boolive\installer\Installer::start();
}