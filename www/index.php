<?php
/**
 * Boolive CMS
 * Главный исполняемый файл. Запуск системы
 *
 * @version 2
 * @author Vladimir Shestakov <boolive@yandex.ru>
 * @see http://boolive.ru
 */
// Подключение базовой конфигурации проекта
require 'config.php';
// Подключение главного класса системы
require DIR_SERVER_ENGINE.'Engine.php';
// Запуск системы
Engine\Engine::Start();
