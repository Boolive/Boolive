<?php
/**
 * Конфигурация хранилища данных
 * Указывается класс хранидища и параметры подключения.
 */
$store = array(
    'class' => '\boolive\data\stores\MySQLStore2',
    'connect' => array(
        // Имя источника данных
        'dsn' => array(
            // Тип СУБД
            'driver' => 'mysql',
            // Имя базы данных
            'dbname' => 'boolive-test6',
            // Адрес сервера
            'host' => '127.0.0.1',
            // Порт
            'port' => '3306'
        ),
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
        // Признаки, включен или нет режим отладки. В режиме отладки трассируются запросы и подсчитывается их кол-во
        'trace_sql' => false,
        'trace_count' => false,
    )
);