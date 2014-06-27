<?php
/**
 * Хранилище в MySQL
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace boolive\data\stores;

use boolive\auth\Auth,
    boolive\cache\Cache,
    boolive\database\DB,
    boolive\data\Entity,
    boolive\data\Data2,
    boolive\functions\F,
    boolive\file\File,
    boolive\errors\Error,
    boolive\events\Events,
    boolive\develop\Trace;

class MySQLStore2 extends Entity
{
    /** @var \boolive\database\DB */
    public $db;

    /**
     * Конструктор экземпляра хранилища
     * @param array $config Параметры подключения к базе данных
     */
    function __construct($config)
    {
        $config['dsn'] = array(
            'driver' => 'mysql',
            'dbname' => $config['dbname'],
            'host' => $config['host'],
            'port' => $config['port']
        );
        $this->db = DB::connect($config);
        Events::on('Boolive::deactivate', $this, 'deactivate');
    }

    /**
     * Обработчик системного события deactivate (завершение работы системы)
     */
    function deactivate()
    {

    }

    /**
     * Чтение объектов
     * @param string|array $cond Условие на выбираемые объекты.
     * @param bool $index Признак, выполнять индексацию данных перед чтением или нет?
     * @return array|\boolive\data\Entity|null Массив объектов. Если глубина поиска ровна 0, то возвращается объект или null
     * @throws \Exception
     */
    function read($cond, $index = false)
    {
        return 'a';
    }

        /**
     * Создание хранилища
     * @param $connect
     * @param null $errors
     * @throws \boolive\errors\Error|null
     */
    static function createStore($connect, &$errors = null)
    {
        try{
            if (!$errors) $errors = new \boolive\errors\Error('Некоректные параметры доступа к СУБД', 'db');
            // Проверка подключения и базы данных
            $db = new DB('mysql:host='.$connect['host'].';port='.$connect['port'], $connect['user'], $connect['password'], array(DB::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "utf8" COLLATE "utf8_bin"'), $connect['prefix']);
            $db_auto_create = false;
            try{
                $db->exec('USE `'.$connect['dbname'].'`');
            }catch (\Exception $e){
                // Проверка исполнения команды USE
                if ((int)$db->errorCode()){
                    $info = $db->errorInfo();
                    // Нет прав на указанную бд (и нет прав для создания бд)?
                    if ($info[1] == '1044'){
                        $errors->dbname->no_access = "No access";
                        throw $errors;
                    }else
                    // Отсутсвует указанная бд?
                    if ($info[1] == '1049'){
                        // создаем
                        $db->exec('CREATE DATABASE `'.$connect['dbname'].'` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci');
                        $db_auto_create = true;
                        $db->exec('USE `'.$connect['dbname'].'`');
                    }
                }
            }
            // Проверка поддержки типов таблиц InnoDB
            $support = false;
            $q = $db->query('SHOW ENGINES');
            while (!$support && ($row = $q->fetch(\PDO::FETCH_ASSOC))){
                if ($row['Engine'] == 'InnoDB' && in_array($row['Support'], array('YES', 'DEFAULT'))) $support = true;
            }
            if (!$support){
                // Удаляем автоматически созданную БД
                if ($db_auto_create) $db->exec('DROP DATABASE IF EXISTS `'.$connect['dbname'].'`');
                $errors->common->no_innodb = "No InnoDB";
                throw $errors;
            }
            // Есть ли таблицы в БД?
            $pfx = $connect['prefix'];
            $tables = array($pfx.'ids', $pfx.'objects', $pfx.'protos', $pfx.'parents');
            $q = $db->query('SHOW TABLES');
            while ($row = $q->fetch(DB::FETCH_NUM)/* && empty($config['prefix'])*/){
                if (in_array($row[0], $tables)){
                    // Иначе ошибка
                    $errors->dbname->db_not_empty = "Database is not empty";
                    throw $errors;
                }
            }
            // Секционирование таблиц
            $sects = array();
            foreach ($connect['sections'] as $sec){
                $sects[] = 'PARTITION `sec_'.$sec['code'].'` VALUES IN ('.$sec['code'].')';
            }
            $sects = implode(',', $sects);
            // Создание таблиц
            $db->exec("
                CREATE TABLE {ids} (
                  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `sec` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Код секции',
                  `uri` VARCHAR(1000) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
                  PRIMARY KEY (`id`, `sec`),
                  KEY `uri` (`uri`(255))
                )
                ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='Идентификация путей (URI)'
                PARTITION BY LIST(sec) ($sects)
            ");
            $db->exec("
                CREATE TABLE {objects} (
                  `id` INT(10) UNSIGNED NOT NULL COMMENT 'Идентификатор по таблице ids',
                  `sec` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Код секции',
                  `is_draft` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Черновик (1) или нет (0)?',
                  `is_hidden` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Скрыт (1) или нет (0)?',
                  `is_mandatory` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Обязательный (1) или нет (0)? ',
                  `is_property` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Свойство (1) или нет (0)? ',
                  `is_relative` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Относительный (1) или нет (0) прототип?',
                  `is_completed` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Дополнен свйствами прототипа или нет (0 - нет, 1 - да)?',
                  `is_link` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Используетя как ссылка или нет? Для оптимизации указывается идентификатор объекта, на которого ссылается ',
                  `is_default_value` INT(10) UNSIGNED NOT NULL DEFAULT '4294967295' COMMENT 'Идентификатор прототипа, чьё значение наследуется (если не наследуется, то свой id)',
                  `is_default_class` INT(10) UNSIGNED NOT NULL DEFAULT '4294967295' COMMENT 'Используется класс прототипа или свой?',
                  `с_date` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Дата создания',
                  `u_date` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Дата обновления',
                  `author` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Идентификатор автора',
                  `proto` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Идентификатор прототипа',
                  `proto_cnt` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Уровень наследования (кол-во прототипов)',
                  `parent` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Идентификатор родителя',
                  `parent_cnt` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Уровень вложенности (кол-во родителей)',
                  `order` INT(11) NOT NULL DEFAULT '0' COMMENT 'Порядковый номер. Уникален в рамках родителя',
                  `name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Имя',
                  `value` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Строковое значение',
                  `valuef` DOUBLE NOT NULL DEFAULT '0' COMMENT 'Числовое значение для правильной сортировки и поиска',
                  `value_type` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Тип значения. 1 - строка, 2 - текст, 3 - файл',
                  PRIMARY KEY (`id`, `sec`),
                  KEY `child` (`parent`,`order`,`name`,`value`,`valuef`, `sec`),
                  KEY `indexation` (`parent`,`id`, `sec`),
                  KEY `default_value` (`is_default_value`, `sec`)
                )
                ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='Объекты'
                PARTITION BY LIST(sec) ($sects)
            ");
            $db->exec("
                CREATE TABLE {parents} (
                  `object_id` INT(10) UNSIGNED NOT NULL COMMENT 'Идентификатор объекта',
                  `parent_id` INT(10) UNSIGNED NOT NULL COMMENT 'Идентификатор родителя',
                  `level` INT(10) UNSIGNED NOT NULL COMMENT 'Уровень родителя от корня',
                  `is_delete` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Удалено отношение или нет',
                  `sec` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Код секции',
                  PRIMARY KEY (`object_id`,`parent_id`, `sec`),
                  UNIQUE KEY `children` (`parent_id`,`object_id`, `sec`)
                )
                ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='Отношения объектов с родителями'
                PARTITION BY LIST(sec) ($sects)
            ");
            $db->exec("
                CREATE TABLE {protos} (
                  `object_id` INT(10) UNSIGNED NOT NULL COMMENT 'Идентификатор объекта',
                  `proto_id` INT(10) UNSIGNED NOT NULL COMMENT 'Идентификатор прототипа',
                  `level` INT(10) UNSIGNED NOT NULL COMMENT 'Уровень прототипа от базового',
                  `is_delete` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Признак, удалено отношение или нет',
                  `sec` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Код секции',
                  PRIMARY KEY (`object_id`,`proto_id`, `sec`),
                  UNIQUE KEY `heirs` (`proto_id`,`object_id`, `sec`)
                )
                ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='Отношения объектов с прототипами'
                PARTITION BY LIST(sec) ($sects)
            ");
            $db->exec("
                CREATE TABLE `text` (
                  `id` INT(10) UNSIGNED NOT NULL COMMENT 'Идентификатор объекта',
                  `value` TEXT NOT NULL DEFAULT '' COMMENT 'Текстовое значение',
                  PRIMARY KEY (`id`),
                  FULLTEXT KEY `fulltext` (`value`)
                )
                ENGINE=MYISAM DEFAULT CHARSET=utf8 COMMENT='Текстовые значения объектов'
            ");
        }catch (\PDOException $e){
			// Ошибки подключения к СУБД
			if ($e->getCode() == '1045'){
				$errors->user->no_access = "No accecss";
				$errors->password->no_access = "No accecss";
			}else
			if ($e->getCode() == '2002'){
				$errors->host->not_found = "Host not found";
                if ($connect['port']!=3306){
                    $errors->port->not_found = "Port no found";
                }
			}else{
				$errors->common = $e->getMessage();
			}
			if ($errors->isExist()) throw $errors;
		}
    }
}