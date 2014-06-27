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
include DIR_SERVER.'boolive/Boolive.php';
// Активация Boolive
if (boolive\Boolive::activate()){
    // Запуск ядра, обработка запроса
    //echo boolive\data\Data::read()->start(new boolive\commands\Commands(), boolive\input\Input::getSource());
}else{
    // Запуск установщика, если Boolive не активирован
    include DIR_SERVER.'boolive/installer/Installer.php';
    boolive\installer\Installer::start();
}

trace(\boolive\data\Data2::getStore()->db);