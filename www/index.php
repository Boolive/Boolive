<?php
/**
 * Boolive CMS
 * Главный исполняемый файл. Запуск системы
 *
 * @version 2
 * @see http://boolive.ru
 */
// Подключение базовой конфигурации проекта
require 'config.php';
// Подключение главного класса системы
require ROOT_DIR_ENGINE.'Engine.php';
// Запуск системы
Engine\Engine::Start();
