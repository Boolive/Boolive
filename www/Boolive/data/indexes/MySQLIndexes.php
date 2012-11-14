<?php
/**
 * Поисковый индекс в MySQL
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\data\indexes;

use Boolive\data\Index,
    Boolive\errors\Error,
    Boolive\database\DB,
    Boolive\functions\F,
    Boolive\data\Data,
    Boolive\data\Entity,
    Boolive\data\Buffer;

class MySQLIndexes extends Index
{
    /**
     * @const int Минимальная глубина индексов.
     * Чем меньше, тем больше индексов создаётся.
     * Чем меньше, тем быстрее выполняется поиск по индексу
     * Глубина создаваемых индексов зависит от условий поиска
     */
    const MIN_DEPTH = 2;
    /** @var \Boolive\database\DB */
    private $db;

    /**
     * @param $config Параметры подключения к MySQL
     * @throws \Boolive\errors\Error
     */
    public function __construct($config)
    {
        if (isset($config['connect'])){
            $this->db = DB::connect($config['connect']);
        }
        if (empty($this->db)){
            throw new Error('MySQLIndexes: Incorrect configuration');
        }
    }

    /**
     * Выбор объекта по его uri из индекса
     * Если подходящего индекса нет, то автоматически создаётся новый по родителю искомого объекта
     * @param string $uri URI объекта
     * @param string $lang Код языка из 3 символов. Если не указан, то выбирается общий
     * @param int $owner Код владельца. Если не указан, то выбирается общий
     * @param null|int $date Дата создания (версия). Если не указана, то выбирается актуальная
     * @param null|bool $is_history Объект в истории (true) или нет (false) или без разницы (null). Если указана дата, то всегда null
     * @return \Boolive\data\Entity|null
     */
    public function read($uri, $lang = '', $owner = 0, $date = null, $is_history = false)
    {
        $key = $uri;//.' '.$lang.' '.$owner.' '.$date.' '.$is_history;
        if (Buffer::isExist($key)) return Buffer::get($key);

        $uris = F::splitRight('/', $uri);
        $info = $this->getIndex($uris[0], 1);
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
        $q = $this->db->prepare('SELECT * FROM {index_'.$info['name'].'} WHERE '.$where.' LIMIT 0,1');
        $q->execute($values);
        if ($info = $q->fetch(DB::FETCH_ASSOC)){
            $object = new $info['class']($info, !empty($info['_virtual']), true);
        }else{
            $object = new Entity(array('uri' => $uri, 'lang' => $lang, 'owner' => $owner));
            // Объект ссылка?
            if (!empty($parent['is_link']) || !empty($proto['is_link'])){
                $object['is_link'] = 1;
            }
        }
        return Buffer::set($key, $object);
    }

    /**
     * Поиск объектов по условию
     * Поиск выполняется по индексу. Если его нет, то индекс автоматически создаётся
     * Создание индекса может быть затратным по времени
     * @param array $cond Условие поиска
     * @param string $keys Название атрибута, который использовать для ключей массива результата
     * @return array|int Массив объектов или их количество, в зависимости от условия поиска
     * @throws \PDOException
     */
    public function select($cond, $keys = 'name')
    {
        // Глубина условия
        $depth = $this->getCondDepth($cond);
        // Инофрмация об индексе
        $info = $this->getIndex($cond['from'][0], $depth);
        // SQL условия по выбранному индексу
        $sql = $this->getCondSQL($cond, $info['name']);
        // Выбор из индекса
        $q = $this->db->prepare($sql['sql']);
        foreach ($sql['binds'] as $i => $v){
            if (is_array($v)){
                $q->bindValue($i+1, $v[0], $v[1]);
            }else{
                $q->bindValue($i+1, $v);
            }
        }
        $q->execute();
        // Если не значение, то список строк
        $row = $q->fetch(DB::FETCH_ASSOC);
        if (isset($row['fun'])){
            // Первая строка результата. Возможно, вычисляемое значение
            return $row['fun'];
        }
        if ($keys == 'value') $keys = '_svalue';
        $result = array();
        while ($row){
            $key = $row['uri'];//.' '.$row['lang'].' '.$row['owner'].' '.$row['date'].' '.(bool)$row['is_history'];
            if (Buffer::isExist($key)){
                $obj = Buffer::get($key);
            }else{
                $obj = Buffer::set($key, new $row['class']($row, !empty($row['_virtual']), true));
            }
            if (empty($keys)){
                $result[] = $obj;
            }else{
                $result[$row[$keys]] = $obj;
            }

            $row = $q->fetch(DB::FETCH_ASSOC);
        }
        return $result;
    }

    /**
     * Выбор индекса по области поиска
     * @param $from URI объекта, подчиенные которого ищутся
     * @param $depth Глубина поиска
     * @return array Информация об индексе
     * @throws \PDOException
     */
    private function getIndex($from, $depth)
    {
        if ($from == "/Interfaces/html/body/boolive/head/logo"){
            $x =0;
        }
        // Параметры выбора индекса
        $names = explode('/', $from);
        $min_level = sizeof($names);
        $max_level = $min_level + $depth;
//        if ($depth < self::MIN_DEPTH){
//            $from2 = implode('/', array_slice($names, 0, sizeof($names) + $depth-self::MIN_DEPTH)).'/%';
//        }else{
//            $from2 = $from.'/%';
//        }
        // Есть ли индекс?
        $info = null;
        $q = $this->db->prepare('SELECT * FROM {index} WHERE LOCATE(`from`, ?)=1 AND `min_level`<=? AND `max_level` >= ? ORDER BY `depth` DESC limit 0,1');
        try{
            $q->execute(array($from, $min_level, $max_level));
            $info = $q->fetch(DB::FETCH_ASSOC);
        }catch(\PDOException $e){
            if ($e->errorInfo[1] == 1146){
                $this->install();
            }else{
                throw $e;
            }
        }
        // Создание индекса
        if (!$info){
            $info = $this->createIndex($from, $depth);
        }
        return $info;
    }

    /**
     * Создание индекса
     * @param string $from Для какого объекта строить индекс
     * @param int $depth Глубина индекса (глубина индексации подчиенных объектов)
     * @return array Информация об индексе
     */
    private function createIndex($from, $depth)
    {
        $depth = max($depth, self::MIN_DEPTH);
        $min_level = mb_substr_count($from, '/') + 1;
        $max_level = $min_level + $depth;
        $index_name = md5($from.'-'.$min_level.'-'.$max_level);
        // Запись информации об индексе
        $q = $this->db->prepare('INSERT IGNORE INTO {index} (`name`, `from`, `depth`, `min_level`, `max_level`, `update`) VALUES (?, ?, ?, ?, ?, ?)');
        $q->execute(array($index_name, $from, $depth, $min_level, $max_level, $update = time()));

        // Создание таблиц индекса
        $this->db->exec('DROP TABLE IF EXISTS {index_'.$index_name.'};
            CREATE TABLE IF NOT EXISTS {index_'.$index_name.'} (
                `uuid` CHAR(32) NOT NULL COLLATE utf8_bin,
                `uri` VARCHAR(500) NOT NULL DEFAULT "" COLLATE utf8_bin,
                `lang` CHAR(3) NOT NULL DEFAULT "",
                `owner` INT(11) NOT NULL DEFAULT "0",
                `date` INT(11) NOT NULL,
                `level` INT(11) NOT NULL DEFAULT "1",
                `order` INT(11) NOT NULL DEFAULT "1",
                `proto` VARCHAR(500) DEFAULT NULL COLLATE utf8_bin,
                `value` VARCHAR(255) DEFAULT NULL,
                `is_logic` TINYINT(4) NOT NULL DEFAULT "0",
                `is_file` TINYINT(4) NOT NULL DEFAULT "0",
                `is_history` TINYINT(4) NOT NULL DEFAULT "0",
                `is_delete` TINYINT(4) NOT NULL DEFAULT "0",
                `is_hidden` TINYINT(4) NOT NULL DEFAULT "0",
                `is_link` TINYINT(4) NOT NULL DEFAULT "0",
                `override` TINYINT(4) NOT NULL DEFAULT "0",
                `_virtual` TINYINT(4) NOT NULL DEFAULT "0",
                `_svalue` VARCHAR(500) NOT NULL DEFAULT "",
                `_fvalue` DOUBLE NOT NULL DEFAULT 0,
                `_is_file` TINYINT(4) NOT NULL DEFAULT 0,
                `parent` CHAR(32) NOT NULL COLLATE utf8_bin,
                `name` CHAR(32) NOT NULL COLLATE utf8_bin,
                `class` VARCHAR(255) NOT NULL DEFAULT "" COLLATE utf8_bin,
                PRIMARY KEY (`uuid`, `lang`, `owner`),
                KEY (`uri`(255),`lang`,`owner`),
                KEY (`parent`, `name`)
        ) ENGINE=INNODB DEFAULT CHARSET=utf8;

        DROP TABLE IF EXISTS {index_'.$index_name.'_proto};
            CREATE TABLE IF NOT EXISTS {index_'.$index_name.'_proto} (
                `o_uuid` CHAR(32) NOT NULL COLLATE utf8_bin,
                `p_uuid` CHAR(32) NOT NULL COLLATE utf8_bin,
                `level` INT(11) NOT NULL DEFAULT 0,
                `o` VARCHAR(500) NOT NULL COLLATE utf8_bin,
                `p` VARCHAR(500) NOT NULL COLLATE utf8_bin,
                PRIMARY KEY (`o_uuid`, `p_uuid`)
            ) ENGINE=INNODB DEFAULT CHARSET=utf8'
        );

        //trace($this->db->errorInfo());

        // Заполнение индекса
        $this->fillIndex($index_name, Data::read($from), $depth);

        // Возврат информации об индексе
        return array(
            'name' => $index_name,
            'from' => $from,
            'depth' => $depth,
            'min_level' => $min_level,
            'max_level' => $max_level,
            'update' => $update
        );
    }

    /**
     * Заполнение индекса
     * @param string $index_name Название индекса. По названию формируется имя таблиц
     * @param \Boolive\data\Entity $base Для какого объекта индекс?
     * @param int $depth Глубина индекса
     * @throws \Exception
     */
    private function fillIndex($index_name, $base, $depth = 1)
    {
        try{
            $this->db->beginTransaction();
            // Заполнение индекса
            $insert = $this->db->prepare('
                    INSERT IGNORE INTO `~index_'.$index_name.'` (`uri`,`lang`,`owner`,`date`,`level`,`order`,
                        `proto`,`value`,`is_logic`,`is_file`,`is_history`,`is_delete`,`is_hidden`,`is_link`,
                        `override`,`_virtual`,`_svalue`,`_fvalue`, `_is_file`, `uuid`, `parent`, `name`, `class`)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ');
            $insert_proto_sql = '';

            $size = 5;
            $proto_level = 0;
            $obj = $base;
            $base_hash = md5($base['uri']);
            $bases = array();
            do{
                array_unshift($bases, $obj);
                $s = Data::getSection($obj['uri'], false);
                $start = 0;
                do{
                    $list = $s->select2($obj['uri'], $start, $size);
                    $cnt = sizeof($list);
                    for ($i=0; $i<$cnt; $i++){
                        // Если подчиенный от прототипа, то создаём виртуального своего
                        if ($proto_level > 0){
                            foreach ($bases as $b) $list[$i] = $list[$i]->birth($b);
                        }
                        $list[$i]['_virtual'] = $list[$i]->isVirtual()?1:0;
                        // Значение с учётом наследования
                        $list[$i]['_svalue'] = mb_substr($list[$i]->getValue(false),0,255);
                        // Числовое значение
                        $list[$i]['_fvalue'] = floatval($list[$i]['_svalue']);
                        // Является ли файлом?
                        $list[$i]['_is_file'] = $list[$i]->isFile(false)?1:0;
                        // HASH
                        $list[$i]['uuid'] = md5($list[$i]['uri']);
                        // HASH родителя
                        $list[$i]['parent'] = $base_hash;
                        // Имя объекта
                        $list[$i]['name'] = $list[$i]->getName();
                        // Путь на класс
                        $list[$i]['class'] = get_class($list[$i]);
                        // Запись в индекс
                        $insert->execute(array_values($list[$i]->getAllAttribs()));
                        // Список прототипов
                        // Добавление прототипов в таблицу отношений
                        $protos = $list[$i]->getAllProto(false);
                        array_unshift($protos, $list[$i]['uri']);
                        $tpl = '(\''.md5($list[$i]['uri']).'\','.$this->db->quote($list[$i]['uri']).', \'';
                        $level = sizeof($protos);
                        foreach ($protos as $p){
                            $insert_proto_sql.= $tpl.md5($p).'\', '.$this->db->quote($p).','.$level--.'),';
                        }
                        if ($depth>1 && $insert->rowCount()){
                            $this->fillIndex($index_name, $list[$i], $depth-1);
                        }
                    }
                    $start+= $size;
                }while(sizeof($list) == $size);
                $proto_level++;
            }while(empty($obj['override']) && $obj = $obj->proto(false));

            if ($insert_proto_sql){
                $this->db->exec('INSERT IGNORE INTO `~index_'.$index_name.'_proto` (`o_uuid`, `o`, `p_uuid`, `p`, `level`) VALUES '.
                                rtrim($insert_proto_sql,',')
                );
            }
            $this->db->commit();
        }catch (\Exception $e){
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Определение глубины условия
     * Глубина зависит от охвата поиска в from и от условий или сортировке по подчиненным
     * @param array $cond Условие поиска
     * @return int
     */
    public function getCondDepth($cond)
    {
        $depth = 1;
        if (isset($cond['where'])){
            $find_depth = function($cond, $depth = 1) use (&$find_depth){
                $d = $depth;
                if (isset($cond[0], $cond[1]) && ($cond[0] == 'any' || $cond[0] == 'all')){
                    $cond = $cond[1];
                }
                $cnt = sizeof($cond);
                for ($i=0; $i<$cnt; $i++){
                    if (is_array($cond[$i]) && !empty($cond[$i])){
                        if ($cond[$i][0]=='child'){
                            $d = max($find_depth($cond[$i][2], $depth+1), $d);
                        }else
                        if (in_array($cond[$i][0], array('any', 'not', 'all'))){
                            $d = max($find_depth($cond[$i][1], $depth), $d);
                        }
                    }
                }
                return $d;
            };
            $depth = $find_depth($cond['where']);
        }
        if ($depth == 1 && isset($cond['order'])){
            $cnt = sizeof($cond['order']);
            while ($depth==1 && --$cnt>=0){
                if (sizeof($cond['order'][$cnt])==3) $depth = 2;
            }
        }
        if (isset($cond['from'][1]) && $cond['from'][1]>1){
            $depth+= $cond['from'][1]-1;
        }
        return $depth;
    }

    /**
     * Конвертирование условия в SQL запрос
     * @param $cond Условие поиска
     * @param $index_name Название индекса, в котором выполнять поиск.
     * @return array Ассоциативный массив SQL запроса и значений, вставляемых в него вместо "?"
     */
    public function getCondSQL($cond, $index_name)
    {
        // Имя таблицы индекса
        $index_table = 'index_'.$index_name;
        // Информация о слияниях
        $joins = array('obj' => null);
        // Значения в SQL услвоие
        $binds = array();
        // количество услой IS
        $is_cnt = 1;

        // Что?
        if (isset($cond['select']) && in_array($cond['select'], array('count'))){
            // Подсчёт количества объектов
            $sql = 'SELECT count(*) as fun FROM {'.$index_table."} as `obj`";
            $calc = true;
        }else{
            // Выбор объектов
            $sql = 'SELECT `obj`.* FROM {'.$index_table."} as `obj`";
            $calc = false;
        }

        // От куда?
        $min_level = mb_substr_count($cond['from'][0], '/') + 1;
        $max_level = $min_level + $cond['from'][1] - 1;
        if (empty($cond['from'][0])){
            $where = "\n  WHERE ";
        }else{
            $where = "\n  WHERE `obj`.uri LIKE ? AND ";
            $binds[] = $cond['from'][0].'/%';
        }

        if ($min_level == $max_level){
            $where.=' `obj`.level=? ';
            $binds[] = array($min_level, DB::PARAM_INT);
        }else{
            $where.=' `obj`.level>=? AND `obj`.level<=? ';
            $binds[] = array($min_level, DB::PARAM_INT);
            $binds[] = array($max_level, DB::PARAM_INT);
        }

        // Условие
        if (isset($cond['where'])){
            /**
             * Рекурсивная функция форматирования условия в SQL
             * @param array $cond Условие
             * @param string $glue Логическая оперция объединения условий
             * @param string $table Алиас таблицы. Изменяется в соответсвии с вложенностью условий на подчиенных
             * @return string SQL условия в WHERE
             */
            $convert = function($cond, $glue = ' AND ', $table = 'obj') use (&$convert, &$binds, &$joins, &$index_table, $is_cnt){
                // Нормализация групп условий
                if ($cond[0] == 'any' || $cond[0] == 'all'){
                    $glue = $cond[0] == 'any'?' OR ':' AND ';
                    $cond = $cond[1];
                }
                    foreach ($cond as $i => $c){
                        if (is_array($c) && !empty($c)){
                            if ($c[0]=='attr'){
                                // Если атрибут value, то в зависимости от типа значения используется соответсвующая колонка
                                if ($c[1] == 'value'){
                                    $c[1] = is_numeric($c[3]) ? '_fvalue': '_svalue';
                                }else
                                // Если is_file, то используем колонку с унаследованным значением
                                if ($c[1] == 'is_file'){
                                    $c[1] = '_is_file';
                                }
                                // sql услвоие
                                $cond[$i] = '`'.$table.'`.`'.$c[1].'` '.$c[2];
                                // Учитываем особенность синтаксиса условия IN
                                if (mb_strtolower($c[2]) == 'in'){
                                    if (!is_array($c[3])) $c[3] = array($c[3]);
                                    $cond[$i].='('.str_repeat('?,', sizeof($c[3])-1).'?)';
                                    $binds = array_merge($binds, $c[3]);
                                }else{
                                    $cond[$i].= '?';
                                    $binds[] = $c[3];
                                }
                            }else
                            // Условие на наличие родителя. Сверяется URI объекта по маске
                            if ($c[0]=='parent'){
                                $cond[$i] = '`'.$table.'`.`uri` LIKE ?';
                                $binds[] = $c[1].'/%';
                            }else
                            // OR
                            if ($c[0]=='any'){
                                $cond[$i] = '('.$convert($c[1], ' OR ').')';
                            }else
                            // AND
                            if ($c[0]=='all'){
                                $cond[$i] = '('.$convert($c[1], ' AND ').')';
                            }else
                            // NOT - отрицание условий
                            if ($c[0]=='not'){
                                $cond[$i] = 'NOT('.$convert($c[1], ' AND ').')';
                            }else
                            // Условия на подчиенного
                            if ($c[0]=='child'){
                                $joins[$table.'.'.$c[1]] = array($table, $c[1]);
                                // Если условий на подчиненного нет, то проверяется его наличие
                                if (empty($c[2])){
                                    $cond[$i] = '(`'.$table.'.'.$c[1].'`.uuid IS NOT NULL)';
                                }else{
                                    $cond[$i] = '('.$convert($c[2], ' AND ', $table.'.'.$c[1]).')';
                                }
                            }else
                            // Условие на наличие прототипа.
                            if ($c[0]=='is'){
                                if (is_array($c[1])){
                                    $c = $c[1];
                                }else{
                                    unset($c[0]);
                                }
                                $alias = 'is'.$is_cnt;
                                $cond[$i] = 'EXISTS (SELECT 1 FROM {'.$index_table.'_proto} as `'.$alias.'` WHERE '.$alias.'.`o_uuid`=`'.$table.'`.uuid AND '.$alias.'.`p_uuid` IN ('.rtrim(str_repeat('md5(?),', sizeof($c)), ',').') LIMIT 0,1)';
                                $binds = array_merge($binds, $c);
                            }
                            // Не поддерживаемые услвоия игнорируем
                            else{
                                unset($cond[$i]);
                            }
                        }else{
                            unset($cond[$i]);
                        }
                    }
                    return implode($glue, $cond);
            };
            // Еслия услвоия есть, то добавляем их в SQL
            if ($w = $convert($cond['where'])){
                $where.= "AND \n\t (".$w.')';
            }
        }

        // Сортировка
        $order = '';
        if (!$calc && isset($cond['order'])){
            $cnt = sizeof($cond['order']);
            for ($i=0; $i<$cnt; $i++){
                if (($ocnt = sizeof($cond['order'][$i])-2)>=0){
                    $jtable = $pretabel = 'obj';
                    if ($ocnt>0){
                        // Сортировка по подчиненным объектами. Требуется слияние таблиц
                        for ($o = 0; $o < $ocnt; $o++){
                            $joins[$jtable = $jtable.'.'.$cond['order'][$i][$o]] = array($pretabel, $cond['order'][$i][$o]);
                        }
                    }
                    if ($order) $order.=', ';
                    $order.= '`'.$jtable.'`.`'.$cond['order'][$i][$ocnt].'` '.$cond['order'][$i][$ocnt+1];
                }
            }
            if ($order) $order = "\n  ORDER BY ".$order;
        }

        // Ограничение по количеству и смещение
        if (!$calc && isset($cond['limit'])){
            $limit="\n  LIMIT ?,?";
            $binds[] = array((int)$cond['limit'][0], DB::PARAM_INT);
            $binds[] = array((int)$cond['limit'][1], DB::PARAM_INT);
        }else{
            $limit = '';
        }

        // Слияния для услвоий по подчиненным и сортировке по ним
        unset($joins['obj']);
        $binds2 = array();
        foreach ($joins as $alias => $info){
            $sql.="\n  LEFT JOIN {".$index_table.'} as `'.$alias.'` ON (`'.$alias.'`.parent = `'.$info[0].'`.uuid AND `'.$alias.'`.name = ?)';
            $binds2[] = $info[1];
        }

        // Полноценный SQL и значения в него
        return array(
            'sql' => $sql.$where.$order.$limit,
            'binds' => array_merge($binds2, $binds)
        );
    }

    private function install()
    {
        // Создание таблиц индекса
        $this->db->exec("
            CREATE TABLE {index} (
                `name` CHAR(32) COLLATE utf8_bin NOT NULL COMMENT 'Идентификатор индекса',
                `from` VARCHAR(500) COLLATE utf8_bin NOT NULL COMMENT 'Для какого объекта индекс',
                `depth` INT(11) NOT NULL COMMENT 'Глубина индекса',
                `min_level` INT(11) NOT NULL DEFAULT '0' COMMENT 'Уровень from',
                `max_level` INT(11) DEFAULT '0' COMMENT 'Уровень from + depth',
                `update` INT(11) NOT NULL DEFAULT '0' COMMENT 'Дата обновления',
                PRIMARY KEY  (`name`),
                KEY `from_depth` (`from`(255),`depth`)
            ) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_bin"
        );
    }
}