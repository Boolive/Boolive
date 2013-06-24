<?php
/**
 * Boolive!
 * Главный исполняемый файл. Запуск движка и проекта
 *
 * @version 2
 * @author Vladimir Shestakov <boolive@yandex.ru>
 * @link http://boolive.ru
 */
use Boolive\Boolive,
    Boolive\data\Data,
    Boolive\commands\Commands,
    Boolive\input\Input,
    Boolive\installer\Installer;
// Подключение конфигурации путей
require 'config.php';
// Подключение движка Boolive
require DIR_SERVER_ENGINE.'Boolive.php';
// Активация Boolive
if (Boolive::activate()){
    // Исполнение корневого объекта. Передаётся экземпляр команд и все входящие данные.
    // Вывод результата клиенту
    echo Data::read()->start(new Commands(), Input::getSource());
}else{
    // Запуск установщика
    include DIR_SERVER_ENGINE.'installer/Installer.php';
    Installer::start();
}

//
//trace(Data::read(array(
//    'from' => array('/Contents/main', '/Contents/news'),
//    'select' => array('tree'),
//    'depth' => array(1,2),
//    'key' => 'name'
//)), false, false, false);