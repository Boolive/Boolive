<?php
/**
 * Конфигурация хранилища данных
 * Указывается класс хранидища, параметры подключения и секций.
 */
$store = array(
    'class' => '\boolive\data\stores\MySQLStore2',
    'connect' => array(
        // Имя базы данных
        'dbname' => 'boolive-data2-23',
        // Адрес сервера
        'host' => '127.0.0.1',
        // Порт
        'port' => '3306',
        // Имя пользователя для подключения к базе данных
        'user' => 'root',
        // Пароль
        'password' => '',
        // Опции подключения
        'options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "utf8" COLLATE "utf8_bin"'
        ),
        // Префикс к таблицам
        'prefix' => '',
        // Режим отладки с трассировкой запросов
        'trace_sql' => false,
        // Режим отладки с подсчётом количества запросов
        'trace_count' => false,
        // Настройка секционирования базы данных
        'sections' => array(
            array('code' => 0, 'uri' => ''),
            array('code' => 1, 'uri' => '/library')
        )
    )
);