<?php
/**
 * Boolive CMS
 * Главный исполняемый файл. Запуск системы
 *
 * @version 2
 * @author Vladimir Shestakov <boolive@yandex.ru>
 * @link http://boolive.ru
 */
use Boolive\Boolive,
    Boolive\data\Data,
    Boolive\commands\Commands,
    Boolive\input\Input;

// Подключение конфигурации путей
require 'config.php';
// Подключение главного класса движка Boolive
require DIR_SERVER_ENGINE.'Boolive.php';
// Активация Boolive
Boolive::activate();
// Исполнение объекта интерфейса. Вывод результата клиенту
echo Data::object('/Interfaces')->start(new Commands, Input::getSource());