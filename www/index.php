<?php
/**
 * Boolive CMS
 * Главный исполняемый файл. Запуск системы
 *
 * @version 2
 * @author Vladimir Shestakov <boolive@yandex.ru>
 * @see http://boolive.ru
 */
use \Boolive\Boolive,
    \Boolive\data\Data,
    \Boolive\commands\Commands,
    \Boolive\input\Input;

// Подключение базовой конфигурации сайта
require 'config.php';

// Подключение и активация движка Boolive
require DIR_SERVER_ENGINE.'Boolive.php';
Boolive::activate();

// Исполнение корневого объекта сайта и вывод результата
echo Data::object('')->start(new Commands(), Input::getSource());