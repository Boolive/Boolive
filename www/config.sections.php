<?php
/**
 * Конфигурация секционирования данных
 * Все данные сайта представляют собой иерархию, узлы которой могут храниться в разных секциях.
 * Секция определяется на подчиенных объекта. Указывается путь объекта и параметры соединения с секцией в
 * соответсвии с выбранным классом (модулем) секции
 */
$config = array(
    // Корневая секция
    '' => array(
        'class' => '\Boolive\data\Sections\MySQLSection',
        'connect' => array(
            // Имя источника данных
            'dsn' => array(
                // Тип СУБД
                'driver' => 'mysql',
                // Имя базы данных
                'dbname' => 'boolive-git',
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
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "utf8" COLLATE "utf8_general_ci"'
            ),
            // Признак, включен или нет режим отладки. В режиме отладки трассируются запросы и подсчитывается их кол-во
            'debug' => false
            ),
        'table' => 'site'
    ),
    // Библиотека
    '/library' => array(
        'extends' => '',
        'table' => 'library'
    ),
    // Интерфейс
    '/interfaces' => array(
        'extends' => '',
        'table' => 'interfaces'
    ),
    // Пользователи
    '/members' => array(
        'extends' => '',
        'table' => 'members'
    ),
    // Содержимое
    '/contents' => array(
        'extends' => '',
        'table' => 'contents'
    ),
    // Ключевые слова
    '/keywords' => array(
        'extends' => '',
        'table' => 'keywords'
    )
);