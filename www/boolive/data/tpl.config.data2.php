<?php
/**
 * Конфигурация хранилища данных
 * Указывается класс хранидища, параметры подключения и секций.
 */
$store = array(
    'class' => '\boolive\data\stores\MySQLStore2',
    'connect' => array(
        // Имя базы данных
        'dbname' => '_dbname_',
        // Адрес сервера
        'host' => '_host_',
        // Порт
        'port' => '_port_',
        // Имя пользователя для подключения к базе данных
        'user' => '_user_',
        // Пароль
        'password' => '_password_',
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
            _sections_
        )
    )
);