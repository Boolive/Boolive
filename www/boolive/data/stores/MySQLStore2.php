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
    /** @var array  */
    private $classes;
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
     * @return array|\boolive\data\Entity|null Массив объектов. Если глубина поиска ровна 0, то возвращается объект или null
     * @throws \Exception
     */
    function read($cond)
    {
        $sql = $this->condToSQL($cond);
        $q = $this->db->prepare($sql['sql']);
        foreach ($sql['binds'] as $i => $v){
            if (is_array($v)){
                $q->bindValue($i+1, $v[0], $v[1]);
            }else{
                $q->bindValue($i+1, $v);
            }
        }
        try{
            $q->execute();
        }catch (\Exception $e){
            trace($q);
            throw $e;
        }
        $row = $q->fetch(DB::FETCH_ASSOC);
        $result = array();
        while ($row){
            if ($cond['calc']){
                $result[] = $row['calc'];
            }else{
                $result[] = $this->makeObject($row);
            }
            $row = $q->fetch(DB::FETCH_ASSOC);
        }
        // Создание экземпляров не найденных объектов
        if ($cond['struct'] == 'object' && empty($result)){
            $attr = array('class_name' => '\\boolive\\data\\Entity');
            if (Data2::isUri($cond['from'])){
                $attr['uri'] = $cond['from'];
            }else{
                $attr['id'] = $cond['from'];
            }
            $result[] = $attr;
        }
        // Выбор текстовых значений
        // Структуирование дерева

        return $result;
    }



    function condToSQL($cond, $only_where = false)
    {
        $result = array(
            'sql' => '',
            'binds' => array()
        );
        $select = '';
        $from = '';
        $joins = '';
        $where = '';
        $group = '';
        $order = '';
        $limit = '';
        // Значения в условиях JOIN ON
        $binds2 = array();

        if (empty($cond['calc'])){
            $select = 'SELECT obj.* ';
        }else
        if (!is_array($cond['calc'])){
            if ($cond['calc'] == 'exists'){
                $select = 'SELECT 1 calc ';
            }else
            if ($cond['calc'] == 'count'){
                $select = 'SELECT COUNT(*) calc ';
            }else
            if (in_array($cond['calc'], array('max', 'min', 'avg', 'sum'))){
                $select = "SELECT {$cond['calc']}(obj.value) calc ";
            }
        }else
        if (in_array($cond['calc'][0], array('max', 'min', 'avg', 'sum')) && isset($this->_attribs[$cond['calc'][1]])){
            $select = "SELECT {$cond['calc'][0]}(obj.{$cond['calc'][1]}) calc ";
        }else{
            throw new \Exception('Incorrect calc parameter in the reading condition');
        }

        $sec = null;
        // Выбор "себя"
        if ($cond['select'] == 'self'){
            $from .= 'FROM {objects} obj';
            if (empty($cond['multiple'])){
                // Выбор одного объекта по id или uri
                if (is_int($cond['from'])){
                    $where .= 'obj.id=?';
                    $result['binds'][] = array($cond['from'], DB::PARAM_INT);
                }else{
                    $where = 'obj.sec=? AND obj.uri=? ';
                    $sec = $this->getSection($cond['from']);
                    $result['binds'][] = array($sec, DB::PARAM_INT);
                    $result['binds'][] = array($cond['from'], DB::PARAM_STR);
                }
            }else{
                // Множественный выбор объектов по id или uri
                $secs = array();
                $ids = array();
                $uris = array();
                foreach ($cond['from'] as $f){
                    if (is_int($f)){
                        $ids[] = $f;
                    }else{
                        $secs[$this->getSection($f)] = true;
                        $uris[] = $f;
                    }
                }
                $w = '';
                if (count($ids)) $w.= 'obj.id IN ('.rtrim(str_repeat('?,',count($ids)),',').')';
                if (count($uris)){
                    $secs = array_keys($secs);
                    if (!empty($w)) $w = '('.$w.' OR ';
                    $w .= '(obj.sec IN ('.rtrim(str_repeat('?,',count($secs)),',').') AND obj.uri IN ('.rtrim(str_repeat('?,',count($uris)),',').'))';
                }
                $where.=$w;
                $result['binds'] = array_merge($result['binds'], $ids, $secs, $uris);
            }
        }else
        // Выбор подчиненного по имени
        if ($cond['select'] == 'child'){
            $from = 'FROM {objects} obj';
            if (empty($cond['multiple'])){
                if (is_int($cond['from'][0])){
                    $where = 'obj.parent=? AND obj.name=?';
                    $result['binds'][] = array($cond['from'][0], DB::PARAM_INT);
                    $result['binds'][] = array($cond['from'][1], DB::PARAM_STR);
                }else{
                    $id = $this->getId($cond['from'][0], false);
                    $sec = $this->getSection($cond['from'][0]);
                    $where = 'obj.sec=? AND obj.parent=? AND obj.name=?';
                    $result['binds'][] = array($sec, DB::PARAM_INT);
                    $result['binds'][] = array($id, DB::PARAM_INT);
                    $result['binds'][] = array($cond['from'][1], DB::PARAM_STR);
                }
            }else{
                // @todo Выбор свойства по имени у множества объектов
                $where = 'obj.sec IN (?) AND obj.parent IN (?) AND obj.name = ?';
            }
        }else{
            // Подчиенные до указанной глубины. По умолчанию глубина 1
            if ($cond['select'] == 'children'){
                // @todo Учитывать секции и uri||id
                // Выбор всех подчиенных
                if ($cond['depth'][1] == Entity::MAX_DEPTH && $cond['depth'][0]<=1){
                    $from = 'FROM {objects} obj';
                    $from.= "\n  JOIN {parents} t ON (t.object_id = obj.id AND t.parent_id = ?".($cond['depth'][0]==1?' AND t.object_id!=t.parent_id':'').' AND t.is_delete=0)';
                    $binds2[] = array($cond['from'], DB::PARAM_INT);

                }else
                // Выбор непосредственных подчиненных
                if ($cond['depth'][0] == 1 && $cond['depth'][1] == 1){
                    $from = 'FROM {objects} obj USE INDEX(child)';
                    $where = 'obj.parent = ? ';
                    $result['binds'][] = array($cond['from'], DB::PARAM_INT);
                }else{
                    // Выбор с ограничением в глубину
                    $from = 'FROM {objects} obj';
                    $from.= "\n  JOIN {parents} f ON (f.object_id = obj.id AND f.parent_id = ? AND f.level>=? AND f.level<=? AND f.is_delete=0)";
                    $binds2[] = array($cond['from'], DB::PARAM_INT);
                    $binds2[] = array($cond['depth'][0], DB::PARAM_INT);
                    $binds2[] = array($cond['depth'][1], DB::PARAM_INT);
                }
            }else
            // Наследники до указанной глубины. По умодчанию глубина 1
            if ($cond['select'] == 'heirs'){
                $from = 'FROM {objects} obj';
            }else
            // Родители до указанной глубины относительно объекта. По умолчанию выбор до корня
            if ($cond['select'] == 'parents'){
                $from = 'FROM {objects} obj';
            }else
            // Прототипы до указанной глубины. По умолчанию выбор до первичного прототипа
            if ($cond['select'] == 'protos'){
                // @todo секции и учитывать uri||id
                // Поиск по всей ветке
                if ($cond['depth'][1] == Entity::MAX_DEPTH && $cond['depth'][0]<=1){
                    $from = 'FROM {objects} obj';
                    $from.= "\n  JOIN {protos} t ON (t.proto_id = obj.id AND t.object_id = ?".($cond['depth'][0]==1?' AND t.object_id!=t.proto_id':'').' AND t.is_delete=0)';
                    $binds2[] = array($cond['from'], DB::PARAM_INT);
                }else{
                    $from = ' FROM {objects} obj';
                    $from.= "\n  JOIN {protos} f ON (f.proto_id = obj.id AND f.object_id = ? AND f.level>=? AND f.level<=? AND f.is_delete=0)";
                    $binds2[] = array($cond['from'], DB::PARAM_INT);
                    $binds2[] = array($cond['depth'][0], DB::PARAM_INT);
                    $binds2[] = array($cond['depth'][1], DB::PARAM_INT);
                }
            }
        }
        // условие where

        // сортировка

        // limit
        if (!empty($cond['limit'])){
            $limit = "\n  LIMIT ?,?";
            $result['binds'][] = array((int)$cond['limit'][0], DB::PARAM_INT);
            $result['binds'][] = array((int)$cond['limit'][1], DB::PARAM_INT);
        }

        $result['sql'] = $select.$from.$joins."\n  WHERE ".$where.$group.$order.$limit;


        return $result;
    }

    function parseFrom($from, $uri_to_id = false)
    {
        $result = array(
            'secs' => array(),
            'ids' => array(),
            'uris' => array()
        );
        foreach ($from as $f){
            if (is_int($f)){
                $result['ids'][] = $f;
            }else
            if ($uri_to_id){
                $result['ids'][] =$this->getId($f,false);
            }else{
                $secs[$this->getSection($f)] = true;
                $result['uris'][] = $f;
            }
        }
        if ($result['secs']) $result['secs'] = array_keys($result['secs']);
        return $result;
    }


    /**
     * Создание объекта из атрибутов
     * @param array $attribs Атриубты объекта, выбранные из базы данных
     * @throws \Exception
     * @return Entity
     */
    private function makeObject($attribs)
    {
//        $attribs['id'] = intval($attribs['id']);
        if ($attribs['parent'] == 0) $attribs['parent'] = null;
//        $attribs['parent'] = $attribs['parent'] == 0 ? null : $attribs['parent'];
//        $attribs['proto'] = $attribs['proto'] == 0 ? null : $attribs['proto'];
        if ($attribs['proto'] == 0) $attribs['proto'] = null;
//        $attribs['author'] = $attribs['author'] == 0 ? null : $attribs['author'];
        if ($attribs['author'] == 0) $attribs['author'] = null;
//        $attribs['is_default_value'] = $attribs['is_default_value'] == Entity::ENTITY_ID ? Entity::ENTITY_ID : $attribs['is_default_value'];
//        $attribs['is_default_class'] = $attribs['is_default_class'] == Entity::ENTITY_ID ? Entity::ENTITY_ID : $attribs['is_default_class'];
//        $attribs['is_link'] = $attribs['is_link'] == Entity::ENTITY_ID ? Entity::ENTITY_ID : $attribs['is_link'];
//        $attribs['order'] = intval($attribs['order']);
//        $attribs['date_update'] = intval($attribs['date_update']);
//        $attribs['date_create'] = intval($attribs['date_create']);
//        $attribs['parent_cnt'] = intval($attribs['parent_cnt']);
//        $attribs['proto_cnt'] = intval($attribs['proto_cnt']);
//        $attribs['value_type'] = intval($attribs['value_type']);
//        if (empty($attribs['is_draft'])) unset($attribs['is_draft']); else $attribs['is_draft'] = true;
//        if (empty($attribs['is_hidden'])) unset($attribs['is_hidden']); else $attribs['is_hidden'] = true;
//        if (empty($attribs['is_mandatory'])) unset($attribs['is_mandatory']); else $attribs['is_mandatory'] = true;
//        if (empty($attribs['is_completed'])) unset($attribs['is_completed']); else $attribs['is_completed'] = true;
//        if (empty($attribs['is_property'])) unset($attribs['is_property']); else $attribs['is_property'] = true;
//        if (empty($attribs['is_relative'])) unset($attribs['is_relative']); else $attribs['is_relative'] = true;
        if (isset($attribs['is_accessible'])){
            if (!empty($attribs['is_accessible'])) unset($attribs['is_accessible']); else $attribs['is_accessible'] = false;
        }
        $attribs['is_exist'] = true;
        unset($attribs['valuef'], $attribs['sec']);
        // Свой класс
        $attribs['class_name'] = '\\boolive\\data\\Entity';
        if (empty($attribs['is_default_class'])){
            $attribs['class_name'] = $this->getClassById($attribs['id']);
        }else
        if ($attribs['is_default_class'] != Entity::ENTITY_ID){
            $attribs['class_name'] = $this->getClassById($attribs['is_default_class']);
        }
        return $attribs;
    }

    /**
     * Название класса по идентификатору объекта для которого он определен
     * @param string $id Идентификатор объекта со своим классом
     * @return string Название класса с пространством имен
     */
    private function getClassById($id)
    {
        if (!isset($this->classes)){
            if ($classes = Cache::get('mysqlstore2/classes')){
                // Из кэша
                $this->classes = json_decode($classes, true);
            }else{
                // Из бд и создаём кэш
                $q = $this->db->query('SELECT id, uri FROM {objects} WHERE is_default_class = id');
                $this->classes = array();
                while ($row = $q->fetch(DB::FETCH_ASSOC)){
                    if ($row['uri'] !== ''){
                        $names = F::splitRight('/', $row['uri'], true);
                        $this->classes[$row['id']] = '\\site\\'.str_replace('/', '\\', trim($row['uri'],'/')).'\\'.$names[1];
                    }else{
                        $this->classes[$row['id']] = '\\site\\site';
                    }
                }
                Cache::set('mysqlstore2/classes', F::toJSON($this->classes, false));
            }
        }
        if ($id != Entity::ENTITY_ID){
            if (isset($this->classes[$id])){
                return $this->classes[$id];
            }else{
                // По id выбираем запись из таблицы ids. Скорее всего объект внешний, поэтому его нет в таблице objects
                $q = $this->db->prepare('SELECT id, uri FROM {objects} WHERE id = ?');
                $q->execute(array($id));
                if ($row = $q->fetch(DB::FETCH_ASSOC)){
                    $names = F::splitRight('/', $row['uri'], true);
                    $this->classes[$id] = '\\site\\'.str_replace('/', '\\', trim($row['uri'],'/')) . '\\' . $names[1];
                }else{
                    $this->classes[$id] = '\\boolive\\data\\Entity';
                }
                //Cache::set('mysqlstore2/classes', F::toJSON($this->classes, false));
                return $this->classes[$id];
            }
        }
        return '\\boolive\\data\\Entity';
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
            $attr['parent_cnt'] = $entity->parentCount();
            // Прототип и уровень наследования
            $attr['proto'] = isset($attr['proto']) ? $this->getId($attr['proto'], true) : 0;
            $attr['proto_cnt'] = $entity->protoCount();
            // Автор
            $attr['author'] = 0;//isset($attr['author']) ? $this->getId($attr['author']) : (IS_INSTALL ? $this->getId(Auth::getUser()->key()): 0);
            // Числовое значение
            $attr['valuef'] = floatval($attr['value']);
            // Переопределено ли значение и кем
            $attr['is_default_value'] = (!is_null($attr['is_default_value']) && $attr['is_default_value'] != Entity::ENTITY_ID)? $this->getId($attr['is_default_value']) : $attr['is_default_value'];
            // Чей класс
            $attr['is_default_class'] = (strval($attr['is_default_class']) !== '0' && $attr['is_default_class'] != Entity::ENTITY_ID)? $this->getId($attr['is_default_class']) : $attr['is_default_class'];
            // Ссылка
            $attr['is_link'] = (strval($attr['is_link']) !== '0' && $attr['is_link'] != Entity::ENTITY_ID)? $this->getId($attr['is_link']) : $attr['is_link'];
            // Дата обновления
            $attr['date_update'] = time();

            // URI до сохранения объекта
            $curr_uri = $attr['uri'];

            $attr['sec'] = $this->getSection($entity->uri2());
            unset($attr['date'], $attr['is_exist'], $attr['is_accessible']);
            $is_new = empty($attr['id']) || $attr['id'] == Entity::ENTITY_ID;

            // Выбор текущего состояния объекта
            if (!$is_new){
                $q = $this->db->prepare('SELECT * FROM {objects} WHERE id=? LIMIT 0,1');
                $q->execute(array($attr['id']));
                $current = $q->fetch(DB::FETCH_ASSOC);
                if (empty($attr['date_create'])) $attr['date_create'] = time();
            }
            if (empty($current)){
                $is_new = true;
                $attr['id'] = $this->reserveId();
            }
            // Тип по умолчанию
            if ($attr['value_type'] == Entity::VALUE_AUTO){
                $attr['value_type'] = ($is_new ? Entity::VALUE_SIMPLE : $current['value_type']);
            }

            // Если больше 255, то тип текстовый
            $value_src = $attr['value'];// для сохранения в текстовой таблице
            if (mb_strlen($attr['value']) > 255){
                $attr['value'] = mb_substr($attr['value'],0,255);
                $attr['value_type'] = Entity::VALUE_TEXT;
            }
            // Своё значение. Вместо 0 используется свой идентификатор - так проще обновлять наследников
            if (empty($attr['is_default_value'])){
                $attr['is_default_value'] = $attr['id'];
            }
            if (empty($attr['is_default_class'])){
                $attr['is_default_class'] = $attr['id'];
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

            // @todo Контроль доступа

            $temp_name = $attr['name'];
            // Уникальность имени объекта
            if ($entity->_autoname){
                // Подбор уникального имени
                $attr['name'] = $entity->_attribs['name'] = $this->nameMakeUnique($attr['sec'], $attr['parent'], $entity->_autoname);
            }else
            if ($is_new || $attr['name']!=$current['name'] || $attr['parent'] != $current['name']){
                // Проверка уникальности для новых объектов или при измененении имени или родителя
                if ($this->nameIsExists($attr['sec'], $attr['parent'], $attr['name'])){
                    $entity->errors()->_attribs->name->unique = array('Уже имеется объект с именем %s', $attr['name']);
                }
            }
            $attr['uri'] = $entity->uri2(true);

            // Если новое имя или родитель, то обновить свой URI и URI подчиненных, перенести папки, переименовать файлы
            if (!empty($current) && ($current['name']!==$attr['name'] || $current['parent']!=$attr['parent'])){
                // Текущий URI
                $names = F::splitRight('/', empty($current)? $attr['uri'] : $current['uri'], true);
                $uri = (isset($names[0])?$names[0].'/':'').(empty($current)? $temp_name : $current['name']);
                // Новый URI
                $names = F::splitRight('/', $attr['uri'], true);
                $uri_new = (isset($names[0])?$names[0].'/':'').$attr['name'];
                $entity->_attribs['uri'] = $uri_new;
                // Новые уровни вложенности
                $dl = $attr['parent_cnt'] - $current['parent_cnt'];
                // @todo Устновка sec через условия, чтобы с учётом конфига обновлился код секции подчиеннных, а он может отличаться от родительского. Sec нужно обновить и в parents
                $q = $this->db->prepare('
                    UPDATE {objects}, {parents}
                    SET {objects}.uri = CONCAT(?, SUBSTRING({objects}.uri, ?)),
                        {objects}.parent_cnt = {objects}.parent_cnt + ?,
                        {objects}.sec = ?
                    WHERE {parents}.parent_id = ? AND {parents}.object_id = {objects}.id AND {parents}.is_delete=0
                ');
                $v = array($uri_new, mb_strlen($uri)+1, $dl, $attr['sec'], $attr['id']);
                $q->execute($v);

                if (!empty($uri) && is_dir(DIR_SERVER.'site'.$uri)){
                    // Переименование/перемещение папки объекта
                    $dir = DIR_SERVER.'site'.$uri_new;
                    File::rename(DIR_SERVER.'site'.$uri, $dir);
                    if ($current['name'] !== $attr['name']){
                        // Переименование файла, если он есть
                        if ($current['value_type'] == Entity::VALUE_FILE){
                            $attr['value'] = File::changeName($current['value'], $attr['name']);
                        }
                        // Ассоциированный с объектом файл. Имя файла определено в value
                        File::rename($dir.'/'.$current['value'], $dir.'/'.$attr['name']);
                        // Переименование файла класса
                        File::rename($dir.'/'.$current['name'].'.php', $dir.'/'.$attr['name'].'.php');
                        // Переименование .info файла
                        File::rename($dir.'/'.$current['name'].'.info', $dir.'/'.$attr['name'].'.info');
                    }
                }
                unset($q);
            }

            // Загрузка файла
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
                            // @todo Проверить безопасность?
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
            // Загрузка/обновление класса
            if (isset($attr['class'])){
                $path = $entity->dir(true).($attr['name']===''?'site':$attr['name']).'.php';
                if (isset($attr['class']['content'])){
                    File::create($attr['class']['content'], $path);
                }else{
                    if ($attr['class']['tmp_name']!=$path){
                        if (!File::upload($attr['class']['tmp_name'], $path)){
                            // @todo Проверить безопасность?
                            // Копирование, если объект-файл создаётся из уже имеющихся на сервере файлов, например при импорте каталога
                            File::copy($attr['class']['tmp_name'], $path);
                        }
                    }
                }
                unset($attr['class']);
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

            // Вставка или обновление записи объекта
            if ($is_new){
                $names = array_keys($attr);
                $sql = 'INSERT INTO {objects} (`'.implode('`, `',$names).'`) VALUES (:'.implode(', :',$names).')';
                $q = $this->db->prepare($sql);
                $q->execute($attr);

            }else{
                $sets = '';
                $binds = array();
                foreach ($attr as $n => $v){
                    if ($v != $current[$n]){
                        $sets .= '`'.$n.'` = :'.$n.', ';
                        $binds[$n] = $v;
                    }
                }
                $binds['id'] = $attr['id'];
                $binds['cursec'] = $current['sec'];
                $sql = 'UPDATE {objects} SET '.rtrim($sets,', ').' WHERE id = :id AND sec = :cursec';
                $q = $this->db->prepare($sql);
                $q->execute($binds);
            }

            // Вставка или обновления текста
            if ($attr['value_type'] == Entity::VALUE_TEXT && $attr['is_default_value'] == $attr['id']){
                $q = $this->db->prepare('REPLACE {text} (`id`, `value`) VALUES (?, ?)');
                $q->execute(array($attr['id'], $value_src));
            }

            // Создание или обновление отношений в protos & parents
            if ($is_new || $attr['parent']!=$current['parent']){
                $this->makeParents($attr['sec'], $attr['id'], $attr['parent'], $is_new);
            }
            if ($is_new || $attr['proto']!=$current['proto']){
                $this->makeProtos($attr['sec'], $attr['id'], $attr['proto'], $is_new);
            }
            // Обновление наследников
            if (!$is_new){
                $dp = ($attr['proto_cnt'] - $current['proto_cnt']);
                // Обновление значения, типа значения, признака наследования значения, класса и кол-во прототипов у наследников
                // если что-то из этого изменилось у объекта
                if ($current['value']!=$attr['value'] || $current['value_type']!=$attr['value_type'] ||
                    $current['is_default_class']!=$attr['is_default_class'] || ($current['proto']!=$attr['proto']) || $dp!=0)
                {
                    $u = $this->db->prepare('
                        UPDATE {objects}, {protos} SET
                            value = IF(is_default_value=:val_proto, :value, value),
                            valuef = IF(is_default_value=:val_proto, :valuef, valuef),
                            value_type = IF(is_default_value=:val_proto, :value_type, value_type),
                            is_default_value = IF((is_default_value=:val_proto || is_default_value=:max_id), :new_val_proto, is_default_value),
                            is_default_class = IF((is_default_class=:class_proto AND ((is_link>0)=:is_link)), :new_class_proto, is_default_class),
                            proto_cnt = proto_cnt+:dp
                        WHERE {protos}.proto_id = :obj AND {protos}.object_id = {objects}.id
                          AND {protos}.proto_id != {protos}.object_id
                    ');
                    $u->execute(array(
                        ':value' => $attr['value'],
                        ':valuef' => $attr['valuef'],
                        ':value_type' => $attr['value_type'],
                        ':val_proto' => $current['is_default_value'],
                        ':class_proto' => $current['is_default_class'],
                        ':new_class_proto' => $attr['is_default_class'],
                        ':new_val_proto' => $attr['is_default_value'],
                        ':is_link' => $attr['is_link'] > 0 ? 1: 0,
                        ':dp' => $dp,
                        ':obj' => $attr['id'],
                        ':max_id' => Entity::ENTITY_ID
                    ));
                }
                // Изменился признак ссылки
                if ($current['is_link'] != $attr['is_link']){
                    // Смена класса по-умолчанию у всех наследников
                    // Если у наследников признак is_link такой же как у изменённого объекта и класс был Entity, то они получают класс изменного объекта
                    // Если у наследников признак is_link не такой же и класс был как у изменноо объекта, то они получают класс Entity
                    $u = $this->db->prepare('
                        UPDATE {objects}, {protos} SET
                            is_default_class = IF((is_link > 0) = :is_link,
                                IF(is_default_class=:max_id, :class_proto, is_default_class),
                                IF(is_default_class=:class_proto, :max_id, is_default_class)
                            ),
                            is_link = IF((is_link=:cur_link || is_link=:max_id), :new_link, is_link)
                        WHERE {protos}.proto_id = :obj AND {protos}.object_id = {objects}.id
                          AND {protos}.proto_id != {protos}.object_id
                    ');
                    $params = array(
                        ':is_link' => $attr['is_link'] > 0 ? 1: 0,
                        ':class_proto' => $attr['is_default_class'],
                        ':max_id' => Entity::ENTITY_ID,
                        ':cur_link' => $current['is_link'] ? $current['is_link'] : $current['id'],
                        ':new_link' => $attr['is_link'] ? $attr['is_link'] : $attr['id'],
                        ':obj' => $attr['id']
                    );
                    $u->execute($params);
                }
            }

            // @todo Запись в лог об изменениях в объекте

            foreach ($attr as $n => $v){
                $entity->_attribs[$n] = $v;
            }
            $entity->_attribs['is_exist'] = true;
            $entity->_changed = false;
            $entity->_autoname = false;
            if ($entity->_attribs['uri'] !== $curr_uri){
                $entity->updateURI();
            }

            $this->db->commit();
            return true;
        }catch (\Exception $e){
            $this->db->rollBack();
            // @todo Учитывать исклюечения уникального ключа (именования объекта)
            if (!$e instanceof Error) throw $e;
        }
        return false;
    }

    /**
     * Удаление объекта и его подчиненных, если они никем не используются
     * @param Entity $entity Уничтожаемый объект
     * @param bool $access Признак, проверять или нет наличие доступа на уничтожение объекта?
     * @param bool $integrity Признак, проверять целостность данных?
     * @throws \boolive\errors\Error Ошибки в сохраняемом объекте
     * @return bool
     */
    function delete($entity, $access, $integrity)
    {

    }

    /**
     * Дополнение объекта обязательными свойствами
     * @param \boolive\data\Entity $entity Сохраняемый объект
     * @param bool $access Признак, проверять доступ или нет?
     * @return bool
     * @throws \boolive\errors\Error Ошибки в сохраняемом объекте
     * @throws \Exception Системные ошибки
     */
    function complete($entity, $access)
    {

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
     * Создание или обновление отношений с родителями
     * "Материализованный путь", когда для каждого объекта имеются отношения со всеми его родителями
     * @param int $sec Код секции объекта
     * @param int $entity_id Идентификатор объекта для которого создать или обновить отношения. Отношения обновляются и у его подчиненных
     * @param int $parent_id Идентифкатор нового родителя
     * @param bool $is_new Признак, объект новый или нет. Если нет, то отношения обновляются
     */
    function makeParents($sec, $entity_id, $parent_id, $is_new = true)
    {
        // Запрос на добавление отношений копированием их у родительского объекта
        $add = $q = $this->db->prepare('
            INSERT INTO {parents} (object_id, parent_id, `level`, `sec`)
            SELECT :obj, parent_id, `level`+:l, :sec FROM {parents}
            WHERE object_id = :parent
            UNION SELECT :obj,:obj,0,:sec
            ON DUPLICATE KEY UPDATE `level` = VALUES(level), `sec` = VALUES(sec)
        ');
        if ($is_new){
            $add->execute(array('obj' => $entity_id, 'parent'=>$parent_id, 'l'=>1, 'sec'=>$sec));
        }else{
            // Родители, от которых перемещается объект
            $q = $this->db->prepare('SELECT parent_id FROM parents WHERE object_id = ? AND object_id != parent_id');
            $q->execute(array($entity_id));
            $parents = $q->fetchAll(DB::FETCH_COLUMN);
            // Удаление ненужных родителей у объекта и всех его подчиненных
            if ($parents){
                $q = $this->db->prepare('
                    DELETE b FROM parents b, parents c
                    WHERE b.object_id = c.object_id AND b.object_id != b.parent_id AND
                          b.parent_id IN ('.implode(',',$parents).') AND c.parent_id = ?
                ');
                $q->execute(array($entity_id));
            }
            // Для каждого подчиненного добавить отношения от нового родителя
            $q = $this->db->prepare('SELECT object_id, level, sec FROM {parents} WHERE parent_id = :obj ORDER BY level');
            $q->execute(array(':obj'=>$entity_id));
            while ($row = $q->fetch(DB::FETCH_ASSOC)){
                $add->execute(array('obj'=>$row['object_id'], 'parent'=>$parent_id, 'l'=>1+$row['level'], 'sec'=>$row['sec']));
            }
        }
    }

    function makeProtos($sec, $entity_id, $proto_id, $is_new = true)
    {
        // Запрос на добавление отношений копированием их у родительского объекта
        $add = $q = $this->db->prepare('
            INSERT INTO {protos} (object_id, proto_id, `level`, `sec`)
            SELECT :obj, proto_id, `level`+:l, :sec FROM {protos}
            WHERE object_id = :proto
            UNION SELECT :obj,:obj,0,:sec
            ON DUPLICATE KEY UPDATE `level` = VALUES(level), `sec` = VALUES(sec)
        ');
        if ($is_new){
            $add->execute(array('obj' => $entity_id, 'proto'=>$proto_id, 'l'=>1, 'sec'=>$sec));
        }else{
            // Родители, от которых перемещается объект
            $q = $this->db->prepare('SELECT proto_id FROM protos WHERE object_id = ? AND object_id!=proto_id');
            $q->execute(array($entity_id));
            $protos = $q->fetchAll(DB::FETCH_COLUMN);
            // Удаление ненужных родителей у объекта и всех его подчиненных
            if ($protos){
                $q = $this->db->prepare('
                    DELETE b FROM protos b, protos c
                    WHERE b.object_id = c.object_id AND b.object_id != b.proto_id AND
                          b.proto_id IN ('.implode(',',$protos).') AND c.proto_id = ?
                ');
                $q->execute(array($entity_id));
            }
            // Для каждого подчиненного добавить отношения от нового родителя
            $q = $this->db->prepare('SELECT object_id, level, sec FROM {protos} WHERE proto_id = :obj ORDER BY level');
            $q->execute(array(':obj'=>$entity_id));
            while ($row = $q->fetch(DB::FETCH_ASSOC)){
                $add->execute(array('obj'=>$row['object_id'], 'proto'=>$proto_id, 'l'=>1+$row['level'], 'sec'=>$row['sec']));
            }
        }
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
                $sec = $this->getSection($uri);
                $q = $this->db->prepare('
                    INSERT INTO {objects} (`id`, `sec`, `parent`, `parent_cnt`, `order`, `name`, `uri`)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ');
                $q->execute(array($this->uri_id[$uri], $sec, $parant, $parant_cnt, $this->uri_id[$uri], $names[1], $uri));
                // Иерархические отношения, чтобы не нарушать целостность
                $this->makeParents($sec, $this->uri_id[$uri], $parant, true);
                $this->makeProtos($sec, $this->uri_id[$uri], 0, true);
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
        $row = $q->fetch(DB::FETCH_ASSOC);
        return isset($row['m'])? $row['m'] : 0;
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