<?php
/**
 * Конфигурация хранилищ данных
 * Хранилище определяется совпадением ключа хранилища с левой частью URI объекта
 * Указывается класс хранидища и параметры подключения. Ключ хранинилища - это ключ массива конфигурации
 */
$stores = array(
    // Внешнее хранилище
    'http:' => array(
        'class' => '\Boolive\data\stores\HTTPStore',
        'connect' => array(
        )
    ),
    // Главное локальное хранилище
    '' => array(
        'class' => '\Boolive\data\stores\MySQLStore',
        'connect' => array(
            // Имя источника данных
            'dsn' => array(
                // Тип СУБД
                'driver' => 'mysql',
                // Имя базы данных
                'dbname' => 'boolive-test8',
                // Адрес сервера
                'host' => 'localhost',
                // Порт
                'port' => '3306'
            ),
            // Имя пользователя для подключения к базе данных
            'user' => 'root',
            // Пароль
            'password' => 'proot',
            // Опции подключения
            'options' => array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "utf8" COLLATE "utf8_bin"'
            ),
                        // Префикс к таблицам
            'prefix' => '',
            // Признаки, включен или нет режим отладки. В режиме отладки трассируются запросы и подсчитывается их кол-во
            'trace_sql' => false,
            'trace_count' => true,
        )
    )
);