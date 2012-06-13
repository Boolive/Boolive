<?php
/**
 * Параметры подключения к базе данных. Используется PDO
 * @link http://php.net/manual/en/book.pdo.php
 * @var array
 */
$config = array(
	// Имя источника данных
	'dsn' => array(
		// Тип СУБД
		'driver' => 'mysql',
		// Имя базы данных
		'dbname' => 'boolive-web',
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
	// Префикс к таблицам БД.
	'prefix' => '',
	// Признак, включен или нет режим отладки. В режиме отладки трассируются запросы и подсчитывается их кол-во
	'debug' => false
);
