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
    function write($entity, $access = true)
    {
        try{
            // Атрибуты отфильтрованы, так как нет ошибок
            $attr = $entity->_attribs;
            // Идентификатор объекта
            // Родитель и урвень вложенности
            $attr['parent'] = isset($attr['parent']) ? $this->getId($attr['parent'], true) : 0;
            //$attr['parent_cnt'] = $entity->parentCount();
            // Прототип и уровень наследования
            $attr['proto'] = isset($attr['proto']) ? $this->getId($attr['proto'], true) : 0;
            //$attr['proto_cnt'] = $entity->protoCount();
            // Автор
            //$attr['author'] = isset($attr['author']) ? $this->getId($attr['author']) : (IS_INSTALL ? $this->getId(Auth::getUser()->key()): 0);
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

            $is_new = empty($attr['id']) || $attr['id'] == Entity::ENTITY_ID;

            // Если больше 255, то тип текстовый
            $value_src = $attr['value'];// для сохранения в текстовой таблице
            if (mb_strlen($attr['value']) > 255){
                $attr['value'] = mb_substr($attr['value'],0,255);
                $attr['value_type'] = Entity::VALUE_TEXT;
            }

            // Если значение файл, то подготовливаем для него имя
            if (isset($attr['file'])){
                $attr['value_type'] = Entity::VALUE_FILE;
                // Если нет временного имени, значит создаётся из значения
                if (empty($attr['file']['tmp_name'])){
                    if (!isset($attr['file']['content'])) $attr['file']['content'] = '';
                    if (!isset($attr['file']['name'])) $attr['file']['name'] = $attr['name'].'.txt';
                    $f = File::fileInfo($attr['file']['name']);
                }else{
                    if (isset($attr['file']['content'])) unset($attr['file']['content']);
                    $f = File::fileInfo($attr['file']['tmp_name']);
                }
                $attr['value'] = ($f['back']?'../':'').$attr['name'];
                // расширение
                if (empty($attr['file']['name'])){
                    if ($f['ext']) $attr['value'].='.'.$f['ext'];
                }else{
                    $f = File::fileInfo($attr['file']['name']);
                    if ($f['ext']) $attr['value'].='.'.$f['ext'];
                }
                $value_src = $attr['value'];
            }

            // Выбор текущего состояния объекта
            if (!$is_new){
                $q = $this->db->prepare('SELECT * FROM {objects} WHERE id=? LIMIT 0,1');
                $q->execute(array($attr['id']));
                $current = $q->fetch(DB::FETCH_ASSOC);
            }else{
                $attr['id'] = $this->reserveId();
            }

            // @todo Контроль доступа

            $temp_name = $attr['name'];
            // Уникальность имени объекта
            if ($entity->_autoname){

                // Подбор уникального имени
                $attr['name'] = $this->nameMakeUnique($attr['sec'], $attr['parent'], $entity->_autoname);
            }else
            if ($is_new || $attr['name']!=$current['name'] || $attr['parent'] != $current['name']){
                // Проверка уникальности для новых объектов или при измененении имени или родителя
                if ($this->nameIsExists($attr['sec'], $attr['parent'], $attr['name'])){
                    $entity->errors()->_attribs->name->unique = 'Уже имеется объект с таким именем';
                }
            }
            $attr['uri'] = $entity->uri2();

            //@todo Если новое имя или родитель, то обновить свой URI и URI подчиненных, перенести папки, переименовать файлы
            if (!empty($current) && ($current['name']!==$attr['name'] || $current['parent']!=$attr['parent'])){
                // Текущий URI
                $names = F::splitRight('/', empty($current)? $attr['uri'] : $current['uri'], true);
                $uri = (isset($names[0])?$names[0].'/':'').(empty($current)? $temp_name : $current['name']);
                // Новый URI
                $names = F::splitRight('/', $attr['uri'], true);
                $uri_new = (isset($names[0])?$names[0].'/':'').$attr['name'];
                $entity->_attribs['uri'] = $uri_new;
                //
                $q = $this->db->prepare('UPDATE {ids}, {parents} SET {ids}.uri = CONCAT(?, SUBSTRING(uri, ?)) WHERE {parents}.parent_id = ? AND {parents}.object_id = {ids}.id AND {parents}.is_delete=0');
                $v = array($uri_new, mb_strlen($uri)+1, $attr['id']);
                $q->execute($v);
                // Обновление уровней вложенностей в objects
                if (!empty($current) && $current['parent']!=$attr['parent']){
                    $dl = $attr['parent_cnt'] - $current['parent_cnt'];
                    $q = $this->db->prepare('UPDATE {objects}, {parents} SET parent_cnt = parent_cnt + ? WHERE {parents}.parent_id = ? AND {parents}.object_id = {objects}.id AND {parents}.is_delete=0');
                    $q->execute(array($dl, $attr['id']));
                    // @todo Обновление отношений
//                    $this->makeParents($attr['id'], $attr['parent'], $dl, true);
                }
                if (!empty($uri) && is_dir(DIR_SERVER.'site'.$uri)){
                    // Переименование/перемещение папки объекта
                    $dir = DIR_SERVER.'site'.$uri_new;
                    File::rename(DIR_SERVER.'site'.$uri, $dir);
                    if ($current['name'] !== $attr['name']){
                        // Переименование файла, если он есть
                        if ($current['value_type'] == Entity::VALUE_FILE){
                            $attr['value'] = File::changeName($current['value'], $attr['name']);
                        }
                        File::rename($dir.'/'.$current['value'], $dir.'/'.$attr['name']);
                        // Переименование файла класса
                        File::rename($dir.'/'.$current['name'].'.php', $dir.'/'.$attr['name'].'.php');
                        // Переименование .info файла
                        File::rename($dir.'/'.$current['name'].'.info', $dir.'/'.$attr['name'].'.info');
                    }
                }
                unset($q);
            }

            //@todo Загрузка файла
            // Если редактирование записи с загрузкой нового файла, при этом старая запись имеет файл, то удаляем старый файл
            if (!empty($current) && isset($attr['file']) && $current['value_type'] == Entity::VALUE_FILE){
                File::delete($entity->dir(true).$current['value']);
            }
            // Связывание с новым файлом
            if (isset($attr['file'])){
                $path = $entity->dir(true).$attr['value'];
                if (isset($attr['file']['content'])){
                    File::create($attr['file']['content'], $path);
                }else{
                    if ($attr['file']['tmp_name']!=$path){
                        if (!File::upload($attr['file']['tmp_name'], $path)){
                            // @todo Проверить безопасность.
                            // Копирование, если объект-файл создаётся из уже имеющихся на сервере файлов, например при импорте каталога
                            if (!File::copy($attr['file']['tmp_name'], $path)){
                                $attr['value_type'] = Entity::VALUE_SIMPLE;
                                $attr['value'] = '';
                            }
                        }
                    }
                }
                unset($attr['file']);
            }

            // Порядковый номер
            if ($is_new){
                if ($attr['order'] == Entity::MAX_ORDER){
                    $attr['order'] = $attr['id'];
                }else
                if ($this->orderIsExists($attr['sec'], $attr['parent'],$attr['order'])){
                    $this->ordersShift($attr['sec'], $attr['parent'], Entity::MAX_ORDER, $attr['order']);
                }
            }else{
                if ($attr['parent'] != $current['parent']) $attr['order'] = Entity::MAX_ORDER;
                if ($attr['order'] != $current['order']){
                    if ($attr['order'] == Entity::MAX_ORDER) $attr['order'] = $this->orderMax($attr['sec'], $attr['parent']);
                    $this->ordersShift($current['sec'], $current['parent'], $current['order'], $attr['order']);
                }
            }

            // @todo Вствка или обновление записи объекта

            // @todo Вставка или обновления текста

            // @todo Создание или обновление отношений в protos & parents

            // @todo Обновление наследников

            // @todo Запись в лог об изменениях в объекте

            $this->db->commit();
        }catch (\Exception $e){
            $this->db->rollBack();
            if (!$e instanceof Error) throw $e;
        }



        trace($attr);
        return;

        // Локальный идентификатор объекта
        if (empty($attr['id']) || $attr['id'] == Entity::ENTITY_ID){

            $attr['id'] = $this->reserveId();
            if ($attr['order'] == Entity::MAX_ORDER){
                $attr['order'] = $attr['id'];
            }

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

    /**
     * Определение идентификатора по URI
     * МОжет создавать идентификатор для URI, если передать аргумент $create = true
     * @param string $uri
     * @param bool $create
     * @param bool $is_created
     * @return int
     */
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
                $this->uri_id[$uri] = $this->reserveId();
                // Создание идентификатора для URI
                $names = F::splitRight('/', $uri);
                $parant = isset($names[0])? $this->getId($names[0], true) : 0;
                $parant_cnt = mb_substr_count($uri, '/');
                $q = $this->db->prepare('
                    INSERT INTO {objects} (`id`, `sec`, `parent`, `parent_cnt`, `order`, `name`, `uri`)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ');
                $q->execute(array($this->uri_id[$uri], $this->getSection($uri), $parant, $parant_cnt, $this->uri_id[$uri], $names[1], $uri));
                $is_created = true;
            }else{
                return 0;
            }
        }
        return $this->uri_id[$uri];
    }

    /**
     * Резервирование идентификатора и его получение
     * @return int
     */
    function reserveId()
    {
        $this->db->exec('REPLACE {auto_increment} (`key`) VALUES (0)');
        return intval($this->db->lastInsertId());
    }

    /**
     * Проверка существования порядкового номера
     * @param int $sec Код секции
     * @param int $parent Идентификатор родителя, в рамках которого проверяется порядковый номер
     * @param int $order Проверяемый номер
     * @return bool
     */
    function orderIsExists($sec, $parent, $order)
    {
        $q = $this->db->prepare('SELECT 1 FROM {objects} WHERE `sec`=? AND `parent`=? AND `order`=?');
        $q->execute(array($sec, $parent, $order));
        return $q->fetch() ? true : false;
    }

    /**
     * Смещение порядковых значений
     * @param int $sec Код секции
     * @param int $parent Идентификатор родителя, в рамках которого проверяется порядковый номер
     * @param int $curr_order С какого порядка смещать
     * @param int $new_order До какого порядка смещать
     */
    function ordersShift($sec, $parent, $curr_order, $new_order)
    {
        if ($curr_order != $new_order){
            if ($curr_order > $new_order){
                F::swap($curr_order, $new_order);
                $shift = '+1';
            }else{
                $shift = '-1';
            }
            $q = $this->db->prepare("UPDATE {objects} SET `order`=`order`$shift WHERE `sec`=? AND `parent`=? AND `order` BETWEEN ? AND ?");
            $q->execute(array($sec, $parent, $curr_order, $new_order));
        }
    }

    /**
     * Максимальный порядковый номер
     * @param int $sec Код секции
     * @param int $parent Идентификатор родителя, в рамках которого определяется максимальный порядковый номер
     * @return int
     */
    function orderMax($sec, $parent)
    {
        $q = $this->db->prepare('SELECT MAX(`order`) m FROM {objects} WHERE sec=? AND parent=?');
        $q->execute(array($sec, $parent));
        if ($row = $q->fetch(DB::FETCH_ASSOC)){
            return $row['m'];
        }else{
            return 0;
        }
    }

    /**
     * Проверка сущестования объекта с указанным именем и родителем
     * @param int $sec Код секции
     * @param int $parent Идентификатор родителя, в рамках которого проверяется имя
     * @param string $name Проверяемое на существование имя
     * @return bool
     */
    function nameIsExists($sec, $parent, $name)
    {
        $q = $this->db->prepare('SELECT 1 FROM {objects} WHERE `sec`=? AND `parent`=? AND `name`=?');
        $q->execute(array($sec, $parent, $name));
        return $q->fetch() ? true : false;
    }

    /**
     * Формирование уникального имени
     * @param int $sec Код секции
     * @param int $parent Идентификатор родителя, в рамках которого проверяется уникальность имени
     * @param string $name Имя, которое нужно сделать уникальным, добавлением в конец чисел
     * @return string
     */
    function nameMakeUnique($sec, $parent, $name)
    {
        $q = $this->db->prepare('
            SELECT CAST((SUBSTRING_INDEX(`name`, "_", -1)) AS SIGNED) AS num FROM {objects}
            WHERE sec=? AND parent=? AND `name` REGEXP ?
            ORDER BY num DESC
            LIMIT 0,1
        ');
        $q->execute(array($sec, $parent, '^'.$name.'(_[0-9]+)?$'));
        if ($row = $q->fetch(DB::FETCH_ASSOC)){
            $name.= '_'.($row['num']+1);
        }
        return $name;
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
                    UNIQUE KEY `name` (`sec`,`parent`,`name`),
                    KEY `uri` (`uri`(255)),
                    KEY `order` (`sec`,`parent`,`order`),
                    KEY `child` (`sec`,`parent`,`name`,`value`(255),`valuef`)
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
            $db->exec("
                CREATE TABLE `auto_increment` (
                  `key` TINYINT(1) NOT NULL,
                  `value` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                  PRIMARY KEY (`key`),
                  UNIQUE KEY `value` (`value`)
                ) ENGINE=INNODB DEFAULT CHARSET=utf8
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