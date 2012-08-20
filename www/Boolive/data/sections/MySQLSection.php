<?php
/**
 * Секция в MySQL
 *
 * @link http://boolive.ru/createcms/sectioning
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\data\sections;

use Boolive\database\DB,
    Boolive\file\File,
    Boolive\data\Section,
    Boolive\data\Data,
    Boolive\calls\Calls,
    Boolive\errors\Error;

class MySQLSection extends Section
{
    /** @var \Boolive\database\DB */
    private $db;
    /** @var string Имя таблицы */
    private $table;

    public function __construct($config)
    {
        parent::__construct($config);
        if (isset($config['connect'])){
            $this->db = DB::connect($config['connect']);
        }
        if (isset($config['table'])){
            $this->table = $config['table'];
        }
        if (empty($this->db) || empty($this->table)){
            throw new Error('MySQLSection: Incorrect configuration');
        }
    }

    /**
     * Выбор объекта по его uri
     * @param string $uri URI объекта
     * @param string $lang Код языка из 3 символов. Если не указан, то выбирается общий
     * @param int $owner Код владельца. Если не указан, то выбирается общий
     * @param null|int $date Дата создания (версия). Если не указана, то выбирается актуальная
     * @param null|bool $is_history Объект в истории (true) или нет (false) или без разницы (null). Если указана дата, то всегда null
     * @return \Boolive\data\Entity|null
     */
    public function read($uri, $lang = '', $owner = 0, $date = null, $is_history = false)
    {
        $where = 'uri=? AND lang=? AND owner=?';
        $values = array($uri, $lang, $owner);
        if (isset($date)){
            $where.=' AND date=?';
            $values[] = $date;
            $is_history = null;
        }
        if (isset($is_history)){
            $where.=' AND is_history=?';
            $values[] = (int)$is_history;
        }
        $q = $this->db->prepare('SELECT * FROM `'.$this->table.'`WHERE '.$where.' LIMIT 0,1');
        $q->execute($values);
        if ($row = $q->fetch(DB::FETCH_ASSOC)){
            $obj = Data::makeObject($row);
            $obj->_virtual = false;
            $obj->_changed = false;
            return $obj;
        }
        return null;
    }

    /**
     * Обновление объекта или добавление, если объект не существует
     * Идентификация объекта выполняется по uri
     * @param \Boolive\data\Entity $entity
     * @return mixed
     */
    public function put($entity)
    {
        if ($entity->check()){
            // Атрибуты отфильтрованы, так как нет ошибок
            $attr = $entity->_attribs;

            // Подбор уникального имени (uri), если указана необходимость в этом
            if ($entity->_rename){
                $base_uri = $entity->getParentUri().'/'.$entity->_rename;
                $i = time() - 1341919854; //чтобы поменьше цифр было
                $attr['uri'] = $base_uri;
                $q = $this->db->prepare('SELECT 1 FROM `'.$this->table.'` WHERE uri=? LIMIT 0,1');
                $q->bindParam(1, $attr['uri']);
                $q->execute();
                while ($q->fetch()){
                    $attr['uri'] = $base_uri.(++$i);
                    $q->execute();
                };
                $entity->_attribs['uri'] = $attr['uri'];
                $entity->_name = null; // Чтобы получать актуальное имя методом getName();
                unset($entity->_rename);
            }

            // Значения превращаем в файл, если больше 255
            if (isset($attr['value']) && mb_strlen($attr['value']) > 255){
                $attr['file'] = array(
                    'data' => $attr['value'],
                    'name' => $entity->getName().'.value'
                );
            }

            // Если значение файл, то подготовливаем для него имя
            if (isset($attr['file'])){
                $attr['is_file'] = true;
                // Если нет временного имени, значит создаётся из значения
                if (empty($attr['file']['tmp_name'])){
                    $attr['value'] = $attr['file']['name'];
                }else{
                    $f = File::fileInfo($attr['file']['tmp_name']);
                    $attr['value'] = ($f['back']?'../':'').$entity->getName();
                    // расширение
                    if (empty($attr['file']['name'])){
                        if ($f['ext']) $attr['value'].='.'.$f['ext'];
                    }else{
                        $f = File::fileInfo($attr['file']['name']);
                        if ($f['ext']) $attr['value'].='.'.$f['ext'];
                    }
                    unset($attr['file']['data']);
                }
                if ($attr['lang']) $attr['value'] = $attr['lang'].'-'.$attr['value'];
                if ($attr['owner']) $attr['value'] = $attr['owner'].'@'.$attr['value'];
            }

            // По умолчанию считаем, что запись добавляется
            $add = true;
            // Проверяем, может запись с указанной датой существует и её тогда редактировать?
            if (!empty($attr['date'])){
                // Поиск записи по полному ключю uri+lang+owner+date
                $q = $this->db->prepare('SELECT * FROM `'.$this->table.'` WHERE uri=? AND lang=? AND owner=? AND date=? LIMIT 0,1');
                $q->execute(array($attr['uri'], $attr['lang'], $attr['owner'], $attr['date']));
                // Если объект есть в БД
                if ($current = $q->fetch(DB::FETCH_ASSOC)){
                    $add = false;
                    $entity->_virtual = false;
                }
            }else{
                $attr['date'] = time();
            }

            // Если новое значение не отличается от старого, то будем редактировать страую запись. Поиск какой именно.
            if ($add){
                // Поиск самой свежей записи с учётом is_histrory
                $q = $this->db->prepare('SELECT * FROM `'.$this->table.'` WHERE uri=? AND lang=? AND owner=? AND is_history=? ORDER BY `date` DESC LIMIT 0,1');
                $q->execute(array($attr['uri'], $attr['lang'], $attr['owner'], $attr['is_history']));
                if ($current = $q->fetch(DB::FETCH_ASSOC)){
                    if (empty($attr['file']) && $current['value']==$attr['value'] && $current['is_file']==$attr['is_file']){
                        $add = false;
                        $entity->_virtual = false;
                        $attr['date'] = $current['date'];
                    }
                }
            }

            // Уровень вложенности
            $attr['level'] = $entity->getLevel();

            // Уникальность order, если задано значение и записываемый объект не в истории
            // Если запись в историю, то вычисляем только если не указан order
            if (!$attr['is_history'] && isset($attr['order']) && (!isset($current) || $current['order']!=$attr['order'])){
                // Сдвиг order существующих записей, чтоб освободить значение для новой
                $q = $this->db->prepare('
                    UPDATE `'.$this->table.'` SET `order` = `order`+1
                    WHERE `uri`!=? AND `uri` like ? AND lang =? AND owner=? AND is_history=0 AND level=? AND`order`>=?'
                );
                $q->execute(array($attr['uri'], $entity->getParentUri().'/%', $attr['lang'], $attr['owner'], $attr['level'], $attr['order']));
            }else
            // Новое максимальное значение для order, если объект новый или явно указано order=null
            if ($entity->isVirtual() || (array_key_exists('order', $attr) && is_null($attr['order']))){
                // Порядковое значение вычисляется от максимального существующего
                $q = $this->db->prepare('SELECT MAX(`order`) m FROM `'.$this->table.'` WHERE `uri` like ? AND lang =? AND owner=? AND is_history=0 AND level=?');
                $q->execute(array($entity->getParentUri().'/%', $attr['lang'], $attr['owner'], $attr['level']));
                if ($row = $q->fetch(DB::FETCH_ASSOC)){
                    $attr['order'] = $row['m']+1;
                }
            }else{
                if (isset($current['order'])) $attr['order'] = $current['order'];
            }

            // Если редактирование записи, при этом старая запись имеет файл, а новая нет, то удаляем файл
            if (!$add && $attr['is_history'] == $current['is_history']){
                if ($attr['is_file']==0 && $current['is_file']==1){
                    // Удаление файла
                    if ($current['is_history']){
                        $path = $entity->getDir(true).'_history_/'.$current['date'].'_'.$current['value'];
                    }else{
                        $path = $entity->getDir(true).$current['value'];
                    }
                    @unlink($path);
                }
            }else
            // Если старое значение является файлом и выполняется редактирование со сменой is_history или
            // добавляется новая актуальная запись, то перемещаем старый файл либо в историю, либо из неё
            if ($current['is_file']==1 && (!$add || ($add && !$entity->isVirtual() && $attr['is_history']==0))){
                if ($current['is_history']==0){
                    $to = $entity->getDir(true).'_history_/'.$current['date'].'_'.$current['value'];
                    $from = $entity->getDir(true).$current['value'];
                }else{
                    $to = $entity->getDir(true).$current['value'];
                    $from = $entity->getDir(true).'_history_/'.$current['date'].'_'.$current['value'];
                }
                File::rename($from, $to);
            }

            // Связывание с новым файлом
            if (isset($attr['file'])){
                if ($attr['is_history']){
                    $path = $entity->getDir(true).'_history_/'.$attr['date'].'_'.$attr['value'];
                }else{
                    $path = $entity->getDir(true).$attr['value'];
                }
                if (isset($attr['file']['data'])){
                    if (!File::create($attr['file']['data'], $path)){
                        $attr['is_file'] = false;
                        $attr['value'] = null;
                    }
                }else{
                    if ($attr['file']['tmp_name']!=$path){
                        if (!File::upload($attr['file']['tmp_name'], $path)){
                            // @todo Проверить безопасность.
                            // Копирование, если объект-файл создаётся из уже имеющихся на сервере файлов, например при импорте каталога
                            if (!File::copy($attr['file']['tmp_name'], $path)){
                                $attr['is_file'] = false;
                                $attr['value'] = null;
                            }
                        }
                    }
                }
                unset($attr['file']);
            }

            // Текущую акуальную запись в историю
            // Если добавление новой актуальной записи или востановление из истории
            if ((!$entity->isVirtual() || $current['is_histroy']) && $attr['is_history']==0){
                // Смена истории, если есть уже записи.
                $q = $this->db->prepare('
                    UPDATE `'.$this->table.'` SET `is_history` = 1
                    WHERE `uri`=? AND lang =? AND owner=? AND is_history=0'
                );
                $q->execute(array($attr['uri'], $attr['lang'], $attr['owner']));
            }

            // Запись объекта (создание или обновление при наличии)
            // Объект идентифицируется по uri+vers+lang+owner
            $attr_names = array_keys($attr);
            $cnt = sizeof($attr);
            $q = $this->db->prepare('
                INSERT INTO `'.$this->table.'` (`'.implode('`, `', $attr_names).'`)
                VALUES ('.str_repeat('?,', $cnt-1).'?)
                ON DUPLICATE KEY UPDATE `'.implode('`=?, `', $attr_names).'`=?
            ');
            $i = 0;
            foreach ($attr as $value){
                $i++;
                $type = is_int($value)? DB::PARAM_INT : (is_bool($value) ? DB::PARAM_BOOL : (is_null($value)? DB::PARAM_NULL : DB::PARAM_STR));
                $q->bindValue($i, $value, $type);
                $q->bindValue($i+$cnt, $value, $type);
            }
            $q->execute();

            // Обновление экземпляра
            $entity->_attribs = $attr;
            $entity->_virtual = false;
            $entity->_changed = false;
        }
    }

    /**
     * Выбор объектов по условию
     * @param array $cond Услвоие поиска
     * $cond = array(
     *        'where' => '', // Условие как в SQL на колонки таблицы. Условие на подчиненные объекты
     *        'values' => array(), // Массив значений для вставки в условие вместо "?"
     *        'order' => '', // Способ сортировки. Задается как в SQL, например: `name` DESC, `value` ASC
     *        'count' => 0, // Количество выбираемых объектов (строк)
     *        'start' => 0 // Смещение от первого найденного объекта, с которого начинать выбор
     *    );
     * @return array
     */
    public function select($cond)
    {
        // Услвоие, сортировка и ограничение количества
        $cond = array_replace(array('where' => '', 'values' => array(), 'order' => '', 'count' => 0, 'start' => 0), $cond);
        $filter = '';
        if ($cond['where']) $filter.= ' WHERE '.$cond['where'];
        if ($cond['order']) $filter.= ' ORDER BY '.$cond['order'];
        if ($cond['count']||$cond['start']) $filter.= ' LIMIT '.intval($cond['start']).($cond['count']?','.intval($cond['count']):'');
        // Подготовка и исполнение запроса
        $q = $this->db->prepare("SELECT * FROM {$this->table} {$filter}");
        $cnt = sizeof($cond['values']);
        for ($i = 0; $i < $cnt; $i++){
            $q->bindValue($i+1, $cond['values'][$i]);
        }
        $q->execute();
        // Создание экземпляров
        $result = array();
        while ($attr = $q->fetch(DB::FETCH_ASSOC)){
            $obj = Data::makeObject($attr);
            $obj->_virtual = false;
            $result[] = $obj;
        }
        return $result;
    }

    /**
     * Выбор количества бъектов по условию
     * @param array $cond Услвоие поиска
     * @return int
     */
    public function select_count($cond){
        // Услвоие, сортировка и ограничение количества
        $cond = array_replace(array('where' => '', 'values' => array(), 'order' => '', 'count' => 0, 'start' => 0), $cond);
        $filter = '';
        if ($cond['where']) $filter.= ' WHERE '.$cond['where'];
        if ($cond['order']) $filter.= ' ORDER BY '.$cond['order'];
        // Подготовка и исполнение запроса
        $q = $this->db->prepare("SELECT count(*) as cnt FROM {$this->table} {$filter}");
        $cnt = sizeof($cond['values']);
        for ($i = 0; $i < $cnt; $i++){
            $q->bindValue($i+1, $cond['values'][$i]);
        }
        $q->execute();
        // Создание экземпляров
        $result = array();
        if ($row = $q->fetch(DB::FETCH_ASSOC)){
            return $row['cnt'];
        }
        return 0;
    }

    public function install()
    {
        $this->db->exec('
            CREATE TABLE `'.$this->table.'` (
                `uri` VARCHAR(255) NOT NULL DEFAULT "" COMMENT "Унифицированный идентификатор (путь на объект)",
                `lang` CHAR(3) NOT NULL DEFAULT "" COMMENT "Код языка по ISO 639-3",
                `owner` INT(11) NOT NULL DEFAULT "0" COMMENT "Владелец",
                `date` INT(11) NOT NULL COMMENT "Дата создания объекта",
                `level` INT(11) NOT NULL DEFAULT "1" COMMENT "Уровень вложенности относительно корня",
                `order` INT(11) NOT NULL DEFAULT "1" COMMENT "Порядковый номер",
                `proto` VARCHAR(255) DEFAULT NULL COMMENT "uri прототипа",
                `value` VARCHAR(255) DEFAULT NULL COMMENT "Значение",
                `is_logic` TINYINT(4) NOT NULL DEFAULT "0" COMMENT "Признак, есть ли класс у объекта",
                `is_file` TINYINT(4) NOT NULL DEFAULT "0" COMMENT "Является ли объект файлом",
                `is_history` TINYINT(4) NOT NULL DEFAULT "0" COMMENT "Признак, является ли запись историей",
                `is_delete` TINYINT(4) NOT NULL DEFAULT "0" COMMENT "Признак, удален объект или нет",
                `is_hidden` TINYINT(4) NOT NULL DEFAULT "0" COMMENT "Признак, скрытый объект или нет",
                PRIMARY KEY  (`uri`,`lang`,`owner`,`date`),
                KEY `orders` (`order`),
                KEY `sate` (`is_history`,`is_delete`,`is_hidden`)
            ) ENGINE=INNODB DEFAULT CHARSET=utf8'
        );
    }
}
