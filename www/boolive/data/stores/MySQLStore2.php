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
    private $config;
    private $uri_id = array();
    private $uri_sec = array();

    /**
     * Конструктор экземпляра хранилища
     * @param array $config Параметры подключения к базе данных
     */
    function __construct($config)
    {
        $this->config = $config;
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
     * @param Entity $entity
     * @param $access
     */
    function write($entity, $access)
    {
        // Атрибуты отфильтрованы, так как нет ошибок
        $attr = $entity->_attribs;
        // Идентификатор объекта
        // Родитель и урвень вложенности
        $attr['parent'] = isset($attr['parent']) ? $this->getId($attr['parent']) : 0;
        $attr['parent_cnt'] = $entity->parentCount();
        // Прототип и уровень наследования
        $attr['proto'] = isset($attr['proto']) ? $this->getId($attr['proto']) : 0;
        $attr['proto_cnt'] = $entity->protoCount();
        // Автор
        $attr['author'] = isset($attr['author']) ? $this->getId($attr['author']) : (IS_INSTALL ? $this->getId(Auth::getUser()->key()): 0);
        // Числовое значение
        $attr['valuef'] = floatval($attr['value']);
        // Переопределено ли значение и кем
        $attr['is_default_value'] = (!is_null($attr['is_default_value']) && $attr['is_default_value'] != Entity::ENTITY_ID)? $this->localId($attr['is_default_value']) : $attr['is_default_value'];
        // Чей класс
        $attr['is_default_class'] = (strval($attr['is_default_class']) !== '0' && $attr['is_default_class'] != Entity::ENTITY_ID)? $this->localId($attr['is_default_class']) : $attr['is_default_class'];
        // Ссылка
        $attr['is_link'] = (strval($attr['is_link']) !== '0' && $attr['is_link'] != Entity::ENTITY_ID)? $this->localId($attr['is_link']) : $attr['is_link'];
        // Дата обновления
        $attr['date_update'] = time();
        // Тип по умолчанию
        if ($attr['value_type'] == Entity::VALUE_AUTO) $attr['value_type'] = Entity::VALUE_SIMPLE;
//        // URI до сохранения объекта
//        $curr_uri = $attr['uri'];

        $attr['sec'] = $this->getSection($entity->uri2());

        // Подбор уникального имени, если указана необходимость в этом
        if ($entity->_autoname){
            //Выбор записи по шаблону имени с самым большим префиксом
            $q = $this->db->prepare('
                SELECT CAST((SUBSTRING_INDEX(`name`, "_", -1)) AS SIGNED) AS num FROM {objects}
                WHERE sec=? AND parent=? AND `name` REGEXP ?
                ORDER BY num DESC
                LIMIT 0,1
            ');
            $q->execute(array($attr['sec'], $attr['parent'], '^'.$entity->_autoname.'(_[0-9]+)?$'));
            if ($row = $q->fetch(DB::FETCH_ASSOC)){
                $entity->_autoname.= '_'.($row['num']+1);
            }
//            $temp_name = $attr['name'];
            $attr['name'] = $entity->_attribs['name'] = $entity->_autoname;
            $attr['uri'] = $entity->uri2();
        }else{
            $attr['uri'] = $entity->uri2();
        }

        // Локальный идентификатор объекта
        if (empty($attr['id']) || $attr['id'] == Entity::ENTITY_ID){
            $attr_names = array(
                'sec', 'is_draft', 'is_hidden', 'is_mandatory', 'is_property', 'is_relative', 'is_completed',
                'date_create', 'date_update', 'order', 'name', 'parent', 'parent_cnt', 'proto', 'proto_cnt', 'author',
                'value', 'value_type', 'uri', 'is_link', 'is_default_value', 'is_default_class'
            );
            $q = $this->db->prepare('
                INSERT INTO {objects} ('.implode(',',$attr_names).')
                VALUES ('.rtrim(str_repeat('?, ', count($attr_names)),', ').')
            ');
            $binds = array();
            foreach ($attr_names as $attr_name){
                $binds[] = $attr[$attr_name];
            }
            $q->execute($binds);
            $attr['id'] = $this->db->lastInsertId();
        }else{
            //$attr['id'] = $this->getId($attr['id'], true, $new_id);
            $q = $this->db->prepare('
                UPDATE {objects} SET sec=?, is_draft=?, is_hidden=?, is_mandatory=?, is_property=?, is_relative=?, is_completed=?,
                date_create=?, date_update=?, order=?, name=?, parent=?, parent_cnt=?, proto=?, proto_cnt=?, author=?,
                value=?, value_type=?, uri=?, is_link=?, is_default_value=?, is_default_class=?
                WHERE id = ?
            ');
        }
    }

    /**
     * Возвращает код секции по uri. По умолчанию 0
     * Секция определяется по настройкам подключения
     * @param string $uri URI, для которого определяется секция
     * @return int Код секции
     */
    function getSection($uri)
    {
        if (!isset($this->uri_sec[$uri])){
            if (isset($this->config['sections'])){
                $i = count($this->config['sections']);
                while (--$i>=0){
                    if ($this->config['sections'][$i]['uri'] == '' || mb_strpos($uri, $this->config['sections'][$i]['uri']) === 0){
                        return $this->uri_sec[$uri] = $this->config['sections'][$i]['code'];
                    }
                }
            }
            return 0;
        }
        return $this->uri_sec[$uri];
    }

    function getId($uri, $create = false, &$is_created = false)
    {
        if (is_null($uri) || is_int($uri)) return $uri;
        if (preg_match('/^[0-9]+$/', $uri)) return intval($uri);
        if (!isset($this->uri_id[$uri])){
            // Поиск идентифкатора URI
            $q = $this->db->prepare('SELECT id FROM {objects} WHERE `uri`=? LIMIT 0,1 FOR UPDATE');
            $q->execute(array($uri));
            if ($row = $q->fetch(DB::FETCH_ASSOC)){
                $this->uri_id[$uri] = intval($row['id']);
                $is_created = false;
            }else
            if ($create){
                // Создание идентификатора для URI
                $names = F::splitRight('/', $uri);
                $parant = isset($names[0])? $this->getId($names[0], true) : 0;
                $parant_cnt = mb_substr_count($uri, '/');
                $q = $this->db->prepare('
                    INSERT INTO {objects} (`sec`, `parent`, `parent_cnt`, `name`, `uri`)
                    VALUES (?, ?, ?, ?, ?)
                ');
                $q->execute(array($this->getSection($uri), $parant, $parant_cnt, $names[1], $uri));
                $this->uri_id[$uri] = intval($this->db->lastInsertId('id'));
                $is_created = true;
            }else{
                return 0;
            }
        }
        return $this->uri_id[$uri];
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
//            $db->exec("
//                CREATE TABLE {ids} (
//                  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
//                  `sec` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Код секции',
//                  `uri` VARCHAR(1000) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
//                  PRIMARY KEY (`id`, `sec`),
//                  KEY `uri` (`uri`(255))
//                )
//                ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='Идентификация путей (URI)'
//                PARTITION BY LIST(sec) ($sects)
//            ");
            $db->exec("
                CREATE TABLE {objects} (
                  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор по таблице ids',
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
                  `date_create` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Дата создания',
                  `date_update` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Дата обновления',
                  `author` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Идентификатор автора',
                  `proto` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Идентификатор прототипа',
                  `proto_cnt` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Уровень наследования (кол-во прототипов)',
                  `parent` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Идентификатор родителя',
                  `parent_cnt` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Уровень вложенности (кол-во родителей)',
                  `order` INT(11) NOT NULL DEFAULT '0' COMMENT 'Порядковый номер. Уникален в рамках родителя',
                  `name` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Имя',
                  `value` VARCHAR(256) NOT NULL DEFAULT '' COMMENT 'Строковое значение',
                  `valuef` DOUBLE NOT NULL DEFAULT '0' COMMENT 'Числовое значение для правильной сортировки и поиска',
                  `value_type` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Тип значения. 1 - строка, 2 - текст, 3 - файл',
                  `uri` VARCHAR(1024) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
                  PRIMARY KEY (`id`,`sec`),
                  UNIQUE KEY `name` (`parent`,`name`,`sec`),
                  KEY `child` (`parent`,`order`,`name`,`value`,`valuef`,`sec`),
                  KEY `uri` (`uri`(255))
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