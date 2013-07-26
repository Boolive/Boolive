<?php
/**
 * Хранилище в MySQL
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\data\stores;

use Boolive\auth\Auth,
    Boolive\cache\Cache,
    Boolive\database\DB,
    Boolive\data\Entity,
    Boolive\data\Data,
    Boolive\functions\F,
    Boolive\file\File,
    Boolive\data\Buffer,
    Boolive\develop\Trace,
    Boolive\errors\Error,
    Boolive\events\Events;

class MySQLStore extends Entity
{
    /** @var \Boolive\database\DB */
    public $db;
    /** @var string Ключ хранилища, по которому хранилище выбирается для объектов и создаются короткие URI */
    private $key;
    /** @var array  */
    private $classes;

    private $_local_ids;
    private $_local_ids_change = false;

    /**
     * Конструктор экземпляра хранилища
     * @param array $key Ключ хранилища. Используется для формирования и распознования сокращенных URI
     * @param $config Параметры подключения к базе данных
     */
    public function __construct($key, $config)
    {
        $this->key = $key;
        $this->db = DB::connect($config);
        Events::on('Boolive::deactivate', $this, 'deactivate');
    }

    /**
     * Обработчик системного события deactivate (завершение работы системы)
     */
    public function deactivate()
    {
        if ($this->_local_ids_change) Cache::set('mysqlstore/localids', F::toJSON($this->_local_ids, false));
    }

    /**
     * Чтение объектов
     * @param $cond Условие на читаемые объекты.
     * @param bool $index Признак, выполнять индексацию данных перед чтением или нет?
     * @return array|\Boolive\data\Entity|null Массив объектов. Если глубина поиска ровна 0, то возвращается объект или null
     * @throws \Exception
     */
    public function read($cond, $index = false)
    {
        // SQL условия выбокри
        $sql = $this->getCondSQL($cond);
        // Сгруппированный результат
        if ($cond['select'][0] == 'count'){
            $fill = 0;
        }else
        if ($cond['select'][0] == 'exists'){
            $fill = false;
        }else
        if ($cond['select'][0] == 'self'){
            $fill = null;
        }else{
            $fill = array();
        }
        $what = ($cond['select'][0] == 'exists' || $cond['select'][0] == 'count')? $cond['select'][1] : $cond['select'][0];
        if (is_array($cond['from'])){
            if ($what != 'self'){
                // Если выбор не self, то сгруппировать результаты можно только по //id,
                // поэтому нормализуем from
                foreach ($cond['from'] as $key => $from){
                    if (!Data::isShortUri($from, true)){
                        $cond['from'][$key] = Data::read($from.'&comment=read multy "from"', !empty($cond['access']))->id();
                    }
                }
            }
            $multy_from = array_combine($cond['from'], $cond['from']);
            $group_result = array_fill_keys($cond['from'], $fill);
        }else{
            $multy_from = array($cond['from']);
            $group_result = array($fill);
        }
        // Обновление. Для новых объектов автоматом прототипируются подчиненные объекты
        if ($index && $cond['depth'][1] > 0 && ($what == 'children' || $what == 'tree')){
            foreach ($multy_from as $key => $from){
                $multy_from[$key] = Data::read($from.'&comment=read "from" for indexation', !empty($cond['access']));
                // Поиск обновлений, если ещё небыло обновлений, они не завершилась или были больше 5 минут назад
                if ($multy_from[$key]->_attribs['update_time'] == 0 || $multy_from[$key]->_attribs['update_step']!=0
                    /*|| (time()-$multy_from[$key]->_attribs['update_time']) > 300*/){
                    $this->refresh($multy_from[$key], 10, 2);
                }
            }
        }
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
        // Подготовка результата для дерева
        if ($cond['select'][0] == 'tree' && empty($cond['select'][1])){
            foreach ($multy_from as $key => $from){
                if (!$from instanceof Entity){
                    $multy_from[$key] = Data::read($from.'&comment=read "from" for tree', !empty($cond['access']));
                }
                $first_level[$key] = $multy_from[$key]->parentCount() + $cond['depth'][0];
                $tree_list[$key] = array();
                if (empty($row) && $cond['depth'][0] == 0){
                    return is_array($cond['from'])? $multy_from : reset($multy_from);
                }
            }
        }
        // Обработка выбранных строк
        while ($row){
            $key = isset($row['from'])?$row['from']:0;
            // Выбор значения функции
            if ($cond['select'][0] == 'count'){
                // Первая строка результата. Возможно, вычисляемое значение
                $group_result[$key] = intval($row['fun']);
            }else
            // Проверка существования
            if ($cond['select'][0] == 'exists'){
                $group_result[$key] = true;
            }else
            // Выбор одного объекта и не указан ключ для списка
            if (($cond['select'][0] == 'self' || $cond['select'][0] == 'link') && empty($cond['key'])){
                // Выбор атрибута
                if (isset($cond['select'][1])){
                    if (in_array($cond['select'][1], array_keys($this->_attribs)) && isset($row)){
                        $group_result[$key] = $row[$cond['select'][1]];
                    }
                }else
                // Выбор объекта
                if (isset($row['id'])){
                    unset($row['id2']);
                    $group_result[$key] = $this->makeObject($row);
                }else{
                    if (isset($row['id2'])){
                        $group_result[$key] = array(
                            'uri'=>$row['uri'],
                            'owner'=>$this->_attribs['owner'],
                            'lang'=>$this->_attribs['lang'],
                            'class' => '\\Boolive\\data\\Entity',
                        );
                    }
                }
            }
            // Список или ветка объектов
            else{
                // Объект или его атрибут
                if (isset($cond['select'][1]) && in_array($cond['select'][1], array_keys($this->_attribs))){
                    if (empty($cond['key'])){
                        $group_result[$key][] = $row[$cond['select'][1]];
                    }else{
                        $group_result[$key][$row[$cond['key']]] = $row[$cond['select'][1]];
                    }
                }else{
                    $obj = $this->makeObject($row);
                    // Если выборка дерева, то в результате будут объекты начальной глубины
                    if (!isset($tree_list[$key]) ||
                        (isset($tree_list[$key]) && $row['parent_cnt'] == $first_level[$key]))
                    {
                        $group_result[$key][] = &$obj;
                    }
                    // Подготовительные данные для формирования дерева
                    if (isset($tree_list[$key])){
                        $tree_list[$key][$obj['id']] = &$obj;
                    }
                    unset($obj);
                }
            }
            $row = $q->fetch(DB::FETCH_ASSOC);
        }
        // Формирование дерева результата (найденные объекты добавляются к найденным родителям)
        if (isset($tree_list)){
            foreach ($tree_list as $key => $tree){
                // Ручная сортиорвка по order
                if (!($cond['depth'][0] == 1 && $cond['depth'][1] == 1) && empty($cond['select'][1]) && empty($cond['limit']) &&
                                !empty($cond['order']) && count($cond['order'])== 1 && $cond['order'][0][0] == 'order'){
                    $sort_kind = mb_strtolower($cond['order'][0][1]) == 'asc'?1:-1;
                    $sort = function($a, $b) use ($sort_kind){
                        if ($a['order'] == $b['order']) return 0;
                        return $sort_kind * ($a['order'] > $b['order']?1:-1);
                    };
                    uasort($tree, $sort);
                    uasort($group_result[$key], $sort);
                }
                foreach ($tree as $tk => $obj){
                    $p = $obj['parent'];
                    if (isset($tree_list[$key][$p])){
                        $tree_list[$key][$p]['children'][$obj['name']] = &$tree_list[$key][$tk];
                    }
                }
                if ($cond['depth'][0] == 0 && isset($group_result[$key])){
                    $group_result[$key] = reset($group_result[$key]);
                }
            }
        }
        // Создание экземпляров не найденных объектов
        if ($cond['select'][0] == 'self'){
            foreach ($group_result as $key => $obj){
                if (!isset($obj)){
                    $obj = /*new Entity(*/array('owner'=>$this->_attribs['owner'], 'lang'=>$this->_attribs['lang'], 'class' => '\\Boolive\\data\\Entity'/*)*/);
//                    $obj->cond($cond);
                    $uri = ($key===0)? $cond['from'] : $key;
                    if (!Data::isShortUri($uri)){
                        $names = F::splitRight('/', $uri, true);
                        $obj/*->_attribs*/['name'] = $names[1];
                        $obj/*->_attribs*/['uri'] = $uri;
                    }
                    $group_result[$key] = $obj;
                }
            }
        }
        return is_array($cond['from'])? $group_result : reset($group_result);
    }

    /**
     * Сохранение объекта
     * @param \Boolive\data\Entity $entity Сохраняемый объект
     * @param bool $access Признак, проверять доступ или нет?
     * @param bool $force_add Принудительное добавление объекта. Используется для внутренних быстрых добалений
     * @throws \Boolive\errors\Error Ошибки в сохраняемом объекте
     * @throws
     * @throws \Exception Системные ошибки
     */
    public function write($entity, $access, $force_add = false)
    {
        if ($access && IS_INSTALL && !($entity->isAccessible() && Auth::getUser()->checkAccess('write', $entity))){
            $error = new Error('Запрещенное действие над объектом', $entity->uri());
            $error->access = new Error('Нет доступа на запись', 'write');
            throw $error;
        }
        if ($entity->check($error)){
            try{
                // Атрибуты отфильтрованы, так как нет ошибок
                $attr = $entity->_attribs;
                // Идентифкатор объекта
                // Родитель и урвень вложенности
                $attr['parent'] = isset($attr['parent']) ? $this->localId($attr['parent']) : 0;
                $attr['parent_cnt'] = $entity->parentCount();
                // Прототип и уровень наследования
                $attr['proto'] = isset($attr['proto']) ? $this->localId($attr['proto']) : 0;
                $attr['proto_cnt'] = $entity->protoCount();
                // Владелец
                $attr['owner'] = isset($attr['owner']) ? $this->localId($attr['owner']) : Entity::ENTITY_ID;
                // Язык
                $attr['lang'] = isset($attr['lang']) ? $this->localId($attr['lang']) : Entity::ENTITY_ID;
                // Числовое значение
                $attr['valuef'] = floatval($attr['value']);
                // Переопределено ли значение и кем
                $attr['is_default_value'] = (strval($attr['is_default_value']) !== '0' && $attr['is_default_value'] != Entity::ENTITY_ID)? $this->localId($attr['is_default_value']) : $attr['is_default_value'];
                // Чей класс
                $attr['is_default_class'] = (strval($attr['is_default_class']) !== '0' && $attr['is_default_class'] != Entity::ENTITY_ID)? $this->localId($attr['is_default_class']) : $attr['is_default_class'];
                // Ссылка
                $attr['is_link'] = (strval($attr['is_link']) !== '0' && $attr['is_link'] != Entity::ENTITY_ID)? $this->localId($attr['is_link']) : $attr['is_link'];
                // URI до сохранения объекта
                $curr_uri = $attr['uri'];
                // Подбор уникального имени, если указана необходимость в этом
                if ($entity->_autoname){
                    $q = $this->db->prepare('SELECT 1 FROM {objects} WHERE parent=? AND `name`=? LIMIT 0,1');
                    $q->execute(array($attr['parent'], $entity->_autoname));
                    if ($q->fetch()){
                        //Выбор записи по шаблону имени с самым большим префиксом
                        $q = $this->db->prepare('SELECT `name` FROM {objects} WHERE parent=? AND `name` REGEXP ? ORDER BY CAST((SUBSTRING_INDEX(`name`, "_", -1)+1) AS SIGNED) DESC LIMIT 0,1');
                        $q->execute(array($attr['parent'], '^'.$entity->_autoname.'(_[0-9]+)?$'));
                        if ($row = $q->fetch(DB::FETCH_ASSOC)){
                            preg_match('|^'.preg_quote($entity->_autoname).'(?:_([0-9]+))?$|u', $row['name'], $match);
                            $entity->_autoname.= '_'.(isset($match[1]) ? ($match[1]+1) : 1);
                        }
                    }
                    $temp_name = $attr['name'];
                    $attr['name'] = $entity->_attribs['name'] = $entity->_autoname;
                    $attr['uri'] = $entity->uri(true);
                }
                // Локальный идентификатор объекта
                if (empty($attr['id'])){
                    $attr['id'] = $this->localId($attr['uri'], true, $new_id);
                }else{
                    $attr['id'] = $this->localId($attr['id'], true, $new_id);
                }
                // Значения превращаем в файл, если больше 255
                if (isset($attr['value']) && mb_strlen($attr['value']) > 255){
                    $attr['file'] = array(
                        'data' => $attr['value'],
                        'name' => $entity->name().'.value'
                    );
                }
                // Если значение файл, то подготовливаем для него имя
                if (isset($attr['file'])){
                    $attr['is_file'] = 1;
                    // Если нет временного имени, значит создаётся из значения
                    if (empty($attr['file']['tmp_name'])){
                        $attr['value'] = $attr['file']['name'];
                    }else{
                        $f = File::fileInfo($attr['file']['tmp_name']);
                        $attr['value'] = ($f['back']?'../':'').$attr['name'];
                        // расширение
                        if (empty($attr['file']['name'])){
                            if ($f['ext']) $attr['value'].='.'.$f['ext'];
                        }else{
                            $f = File::fileInfo($attr['file']['name']);
                            if ($f['ext']) $attr['value'].='.'.$f['ext'];
                        }
                        unset($attr['file']['data']);
                    }
                    if ($attr['lang'] != Entity::ENTITY_ID || $attr['owner'] != Entity::ENTITY_ID){
                        $attr['value'] = $attr['owner'].'@'.$attr['lang'].'@'.$attr['value'];
                    }
                }
                // По умолчанию считаем, что запись добавляется (учёт истории)
                $add = true;
                // Проверяем, может запись с указанной датой существует и её тогда редактировать?
                if (!$force_add && isset($attr['date'])){
                    // Поиск записи по полному ключю id+lang+owner+date
                    $q = $this->db->prepare('SELECT {objects}.*, {ids}.uri FROM {objects}, {ids} WHERE {ids}.id=? AND owner=? AND lang=? AND {objects}.id={ids}.id AND date=? LIMIT 0,1');
                    $q->execute(array($attr['id'], $attr['owner'], $attr['lang'], $attr['date']));
                    // Если объект есть в БД
                    if ($current = $q->fetch(DB::FETCH_ASSOC)){
                        $add = false;
                    }
                    unset($q);
                }else{
                    $attr['date'] = time();
                }
                // Если добавляется новая версия объекта, то добавление возможно, если у объекта новое значение (или файл)
                if (!$force_add && $add){
                    // Поиск самой свежей записи с учётом is_histrory
                    $q = $this->db->prepare('SELECT {objects}.*, {ids}.uri FROM {objects}, {ids} WHERE {ids}.id=? AND owner=? AND lang=? AND {objects}.id={ids}.id AND is_history=? ORDER BY `date` DESC LIMIT 0,1');
                    $q->execute(array($attr['id'], $attr['owner'], $attr['lang'], $attr['is_history']));
                    if ($current = $q->fetch(DB::FETCH_ASSOC)){
                        if (empty($attr['file']) && $current['value']==$attr['value'] && (!isset($attr['is_file']) || $current['is_file']==$attr['is_file'])){
                            $add = false;
                            $attr['date'] = $current['date'];
                        }
                    }
                    unset($q);
                }
                $this->db->beginTransaction();
                // Если новое имя или родитель, то обновить свой URI и URI подчиненных
                if (!empty($current) && ($current['name']!=$attr['name'] || $current['parent']!=$attr['parent'])){
                    // Обновление URI в ids
                    $attr['uri'] = $entity->uri(true);
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
                        // Обновление отношений
                        $this->makeParents($attr['id'], $attr['parent'], $dl, true);
                    }
                    // Переименование/перемещение папки объекта
                    if (!empty($uri) && is_dir(DIR_SERVER_PROJECT.ltrim($uri, '/'))){
                        File::rename(DIR_SERVER_PROJECT.ltrim($uri, '/'), DIR_SERVER_PROJECT.ltrim($uri_new, '/'));
                    }
                    unset($q);
                }
                // Уникальность order, если задано значение и записываемый объект не в истории
                // Если запись в историю, то вычисляем только если не указан order
                if (!$attr['is_history'] && $attr['order']!= Entity::MAX_ORDER && (!isset($current) || $current['order']!=$attr['order'])){
                    // Проверка, занят или нет новый order
                    $q = $this->db->prepare('SELECT 1 FROM {objects} WHERE owner=? AND lang=? AND `parent`=? AND is_history=0 AND `order`=?');
                    $q->execute(array($attr['owner'], $attr['lang'], $attr['parent'], $attr['order']));
                    if ($q->fetch()){
                        // Сдвиг order существующих записей, чтоб освободить значение для новой
                        $q = $this->db->prepare('
                            UPDATE {objects} SET `order` = `order`+1
                            WHERE owner=? AND lang=? AND `parent`=? AND is_history=0 AND `order`>=?'
                        );
                        $q->execute(array($attr['owner'], $attr['lang'], $attr['parent'], $attr['order']));
                    }
                    unset($q);
                }else
                // Новое максимальное значение для order, если объект новый или явно указано order=null
                if (!$entity->isExist() || /*(array_key_exists('order', $attr) && is_null($attr['order']))*/ $attr['order']==self::MAX_ORDER){
                    // Порядковое значение вычисляется от максимального существующего
                    $q = $this->db->prepare('SELECT MAX(`order`) m FROM {objects} WHERE owner=? AND lang=? AND parent=?');
                    $q->execute(array($attr['owner'], $attr['lang'], $attr['parent']));
                    if ($row = $q->fetch(DB::FETCH_ASSOC)){
                        $attr['order'] = $row['m']+1;
                    }
                    unset($q);
                }else{
                    if (isset($current['order'])) $attr['order'] = $current['order'];
                }
                // Префикс к имени файла для учёта владельца и языка
                if ($attr['lang'] != Entity::ENTITY_ID || $attr['owner'] != Entity::ENTITY_ID){
                    $fname_pf = $attr['owner'].'@'.$attr['lang'].'@';
                }else{
                    $fname_pf = '';
                }
                // Если редактирование записи, при этом старая запись имеет файл, а новая нет или загружаетс новый файл, то удаляем файл
                if (!$add && $attr['is_history'] == $current['is_history']){
                    if (isset($attr['is_file']) && ($attr['is_file']==0 && $current['is_file']==1 || isset($attr['file']))){
                        // Удаление файла
                        if ($current['is_history']){
                            $path = $entity->dir(true).'_history_/'.$fname_pf.$current['date'].'_'.$current['value'];
                        }else{
                            $path = $entity->dir(true).$fname_pf.$current['value'];
                        }
                        File::delete($path);
                    }
                }else
                // Если старое значение является файлом и выполняется редактирование со сменой is_history или
                // добавляется новая актуальная запись, то перемещаем старый файл либо в историю, либо из неё
                if (!$force_add && (!$add || ($add && $attr['is_history']==0)) && $current['is_file']==1){
                    if ($current['is_history']==0){
                        $to = $entity->dir(true).'_history_/'.$fname_pf.$current['date'].'_'.$current['value'];
                        $from = $entity->dir(true).$fname_pf.$current['value'];
                    }else{
                        $to = $entity->dir(true).$fname_pf.$current['value'];
                        $from = $entity->dir(true).'_history_/'.$fname_pf.$current['date'].'_'.$current['value'];
                    }
                    File::rename($from, $to);
                }
                // Связывание с новым файлом
                if (isset($attr['file'])){
                    if ($attr['is_history']){
                        $path = $entity->dir(true).'_history_/'.$fname_pf.$attr['date'].'_'.$attr['value'];
                    }else{
                        $path = $entity->dir(true).$fname_pf.$attr['value'];
                    }
                    if (isset($attr['file']['data'])){
                        if (!File::create($attr['file']['data'], $path)){
                            $attr['is_file'] = 0;
                            $attr['value'] = '';
                        }
                    }else{
                        if ($attr['file']['tmp_name']!=$path){
                            if (!File::upload($attr['file']['tmp_name'], $path)){
                                // @todo Проверить безопасность.
                                // Копирование, если объект-файл создаётся из уже имеющихся на сервере файлов, например при импорте каталога
                                if (!File::copy($attr['file']['tmp_name'], $path)){
                                    $attr['is_file'] = 0;
                                    $attr['value'] = '';
                                }
                            }
                        }
                    }
                    unset($attr['file']);
                }
                // Текущую акуальную запись в историю
                // Если добавление новой актуальной записи или востановление из истории
                if (!$force_add && (($add && $entity->isExist()) || (!$add && $current['is_history'])) && (isset($attr['is_history']) && $attr['is_history']==0)){
                    // Смена истории, если есть уже записи.
                    $q = $this->db->prepare('UPDATE {objects} SET `is_history`=1 WHERE `id`=? AND owner=? AND lang=? AND is_history=0');
                    $q->execute(array($attr['owner'], $attr['lang'], $attr['id']));
                    unset($q);
                }
                $attr_names = array('id', 'name', 'owner', 'lang', 'order', 'date', 'parent', 'proto', 'value', 'is_file',
                        'is_history', 'is_delete', 'is_hidden', 'is_link', 'is_default_value', 'is_default_class',
                        'proto_cnt', 'parent_cnt', 'valuef', 'diff');
                $cnt = count($attr_names);
                // Запись объекта (создание или обновление при наличии)
                // Объект идентифицируется по id+owner+lang+date
                if ($add){
                    $q = $this->db->prepare('
                        INSERT INTO {objects} (`'.implode('`, `', $attr_names).'`)
                        VALUES ('.str_repeat('?,', $cnt-1).'?)
                        ON DUPLICATE KEY UPDATE `'.implode('`=?, `', $attr_names).'`=?
                    ');
                    $i = 0;
                    foreach ($attr_names as $name){
                        $value = $attr[$name];
                        $i++;
                        $type = is_int($value)? DB::PARAM_INT : (is_bool($value) ? DB::PARAM_BOOL : (is_null($value)? DB::PARAM_NULL : DB::PARAM_STR));
                        $q->bindValue($i, $value, $type);
                        $q->bindValue($i+$cnt, $value, $type);
                    }
                    $q->execute();
                }else{
                    $attr['date'] = time();
                    $q = $this->db->prepare('
                        UPDATE {objects} SET `'.implode('`=?, `', $attr_names).'`=? WHERE id = ? AND owner=? AND lang=? AND date=?
                    ');
                    $i = 0;
                    foreach ($attr_names as $name){
                        $value = $attr[$name];
                        $i++;
                        $type = is_int($value)? DB::PARAM_INT : (is_bool($value) ? DB::PARAM_BOOL : (is_null($value)? DB::PARAM_NULL : DB::PARAM_STR));
                        $q->bindValue($i, $value, $type);
                    }
                    $q->bindValue(++$i, $attr['id']);
                    $q->bindValue(++$i, $current['owner']);
                    $q->bindValue(++$i, $current['lang']);
                    $q->bindValue(++$i, $current['date']);
                    $q->execute();
                }
                if ($q->rowCount()>0){
                    // Обновить дату изменения у родителей
                    $this->updateDate($attr['id'], $attr['date']);
                }
                $this->db->commit();
                $this->db->beginTransaction();

                if (!$new_id && empty($current)){
                    // Если объект еще не был создан, но его уже прототипировали другие
                    // Для этого был создан только идентификатор объекта, а записи в objects, parents, protos нет
                    // Тогда имитируем обновление объекта, чтобы обновить его отношения со всеми наследниками
                    $current = array(
                        'id'		   => $attr['id'],
                        'proto'        => 0,
                        'proto_cnt'    => 0,
                        'value'	 	   => '',
                        'is_file'	   => 0,
                        'is_delete'	   => 0,
                        'is_hidden'	   => 0,
                        'is_link'      => 0,
                        'is_default_value' => 0,
                        'is_default_class' => Entity::ENTITY_ID,
                    );
                    $incomplete = true;
                }else{
                    $incomplete = false;
                }
                // Создание отношений если объект новый
                if (!$entity->isExist()){
                    $this->makeParents($attr['id'], $attr['parent'], 0, false);
                    $this->makeProtos($attr['id'], $attr['proto'], 0, false, $incomplete);
                }
                if (!empty($current)){
                    // Обновление признаков у подчиненных
                    $u = array(
                        'sql' => '',
                        'binds' => array(':obj'=>$attr['id'])
                    );
                    foreach (array('is_delete', 'is_hidden') as $key){
                        $d = $attr[$key] - $current[$key];
                        if ($d != 0){
                            $u['sql'].=' {objects}.'.$key.' = {objects}.'.$key.' + :d'.$key.',';
                            $u['binds'][':d'.$key] = $d;
                        }
                    }
                    if (!empty($u['sql'])){
                        $q = $this->db->prepare('UPDATE {objects}, {parents} SET '.trim($u['sql'],',').' WHERE {parents}.parent_id = :obj AND {parents}.object_id = {objects}.id AND {parents}.level > 0 AND {parents}.is_delete = 0');
                        $q->execute($u['binds']);
                    }
                    unset($u);
                    // Обновления наследников
                    $dp = ($attr['proto_cnt'] - $current['proto_cnt']);
                    if ($current['proto'] != $attr['proto']){
                        $this->makeProtos($attr['id'], $attr['proto'], $dp, true, $incomplete);
                    }
                    // Обновление значения, признака файла, признака наследования значения, класса и кол-во прототипов у наследников
                    // если что-то из этого изменилось у объекта
                    if ($incomplete || $current['value']!=$attr['value'] || $current['is_file']!=$attr['is_file'] ||
                        $current['is_default_class']!=$attr['is_default_class'] || ($current['proto']!=$attr['proto']) || $dp!=0){
                        // id прототипа, значание которого берется по умолчанию для объекта
                        $p = $attr['is_default_value'] ? $attr['is_default_value'] : $attr['id'];
                        $u = $this->db->prepare('
                            UPDATE {objects}, {protos} SET
                                `value` = IF((is_default_value=:vproto AND owner = :owner AND lang = :lang AND is_history = 0), :value, value),
                                `is_file` = IF((is_default_value=:vproto AND owner = :owner AND lang = :lang AND is_history = 0), :is_file, is_file),
                                `is_default_value` = IF((is_default_value=:vproto  || is_default_value=:max_id), :proto, is_default_value),
                                `is_default_class` = IF((is_default_class=:cclass AND ((is_link>0)=:is_link)), :cproto, is_default_class),
                                `proto_cnt` = `proto_cnt`+:dp
                            WHERE {protos}.proto_id = :obj AND {protos}.object_id = {objects}.id AND {protos}.is_delete=0
                              AND {protos}.proto_id != {protos}.object_id
                        ');
                        $u->execute(array(
                            ':value' => $attr['value'],
                            ':is_file' => $attr['is_file'],
                            ':vproto' => $current['is_default_value'] ? $current['is_default_value'] : $current['id'],
                            ':cclass' => $current['is_default_class'] ? $current['is_default_class'] : $current['id'],
                            ':cproto' => $attr['is_default_class'] ? $attr['is_default_class'] : $attr['id'],
                            ':proto' => $p,
                            ':is_link' => $attr['is_link'] > 0 ? 1: 0,
                            ':dp' => $dp,
                            ':owner' => $attr['owner'],
                            ':lang' => $attr['lang'],
                            ':obj' => $attr['id'],
                            ':max_id' => Entity::ENTITY_ID
                        ));
                    }
                    // Изменился признак ссылки
                    if ($incomplete || $current['is_link']!=$attr['is_link']){
                        // Смена класса по-умолчанию у всех наследников
                        // Если у наследников признак is_link такой же как у изменённого объекта и класс был Entity, то они получают класс изменного объекта
                        // Если у наследников признак is_link не такой же и класс был как у изменноо объекта, то они получают класс Entity
                        $u = $this->db->prepare('
                            UPDATE {objects}, {protos} SET
                                `is_default_class` = IF(({objects}.is_link > 0) = :is_link,
                                    IF(is_default_class=:max_id, :cproto, is_default_class),
                                    IF(is_default_class=:cproto, :max_id, is_default_class)
                                ),
                                `is_link` = IF((is_link=:clink || is_link=:max_id), :nlink, is_link)
                            WHERE {protos}.proto_id = :obj AND {protos}.object_id = {objects}.id AND {protos}.is_delete=0
                              AND {protos}.proto_id != {protos}.object_id
                        ');
                        $params = array(
                            ':obj' => $attr['id'],
                            ':is_link' => $attr['is_link'] > 0 ? 1: 0,
                            ':cproto' => $attr['is_default_class'] ? $attr['is_default_class'] : $attr['id'],
                            ':clink' => $current['is_link'] ? $current['is_link'] : $current['id'],
                            ':nlink' => $attr['is_link'] ? $attr['is_link'] : $attr['id'],
                            ':max_id' => Entity::ENTITY_ID
                        );
                        $u->execute($params);
                    }
                }
                // Обновление экземпляра
                $entity->_attribs['id'] = $this->key.'//'.$attr['id'];
                $entity->_attribs['date'] = $attr['date'];
                $entity->_attribs['name'] = $attr['name'];
                $entity->_attribs['value'] = $attr['value'];
                $entity->_attribs['is_file'] = $attr['is_file'];
                $entity->_attribs['is_exist'] = 1;
                $entity->_changed = false;
                $entity->_autoname = false;
                if ($entity->_attribs['uri'] != $curr_uri){
                    $entity->updateURI();
                }
                $this->db->commit();

                $this->afterWrite($attr, empty($current)?array():$current);

            }catch (\Exception $e){
                $this->db->rollBack();
                $q = $this->db->query('SHOW ENGINE INNODB STATUS');
                trace($q->fetchAll(DB::FETCH_ASSOC));
                throw $e;
            }
        }else{
            throw $error;
        }
    }

    /**
     * Удаление объекта и его подчиненных, если они никем не используются
     * @param Entity $entity Уничтожаемый объект
     * @param bool $access Признак, проверять или нет наличие доступа на уничтожение объекта?
     * @param bool $integrity Признак, проверять целостность данных?
     * @throws \Boolive\errors\Error Ошибки в сохраняемом объекте
     * @return bool
     */
    public function delete($entity, $access, $integrity)
    {
        // Проверка доступа на уничтожение объекта и его подчиненных
        if ($access && IS_INSTALL && ($acond = Auth::getUser()->getAccessCond('destroy', $entity->id(), null))){
            $not_access = $this->read(array(
                    'select' => array('exists', 'children'),
                    'from' => $entity->id(),
                    'depth' => array(0, 'max'),
                    'where' => array('not', $acond),
                    'access' => false
                ), false
            );
            if ($not_access){
                $error = new Error('Запрещенное действие над объектом', $entity->uri());
                $error->access = new Error('Нет доступа на уничтожение объекта или любого из его подчиненных', 'delete');
                throw $error;
            }
        }
        $id = $this->localId($entity->key(), false);
        // Проверка целосности - поиск наследников объекта и наследников его подчиненных
        if ($integrity){
            $q = $this->db->prepare('
                SELECT ids.uri FROM ids
                JOIN parents ON parents.parent_id = :id AND parents.is_delete = 0
                JOIN protos ON protos.object_id = ids.id AND protos.proto_id = parents.object_id AND protos.level > 0 AND protos.is_delete = 0
                LEFT JOIN parents sub ON sub.object_id=protos.object_id AND sub.parent_id = :id
                WHERE sub.object_id IS NULL
                LIMIT 0,10
            ');
            $q->execute(array(':id'=>$id));
            $rows = $q->fetchAll(DB::FETCH_COLUMN, 0);
            if ($rows){
                $uris = implode(', ', $rows);
                $error = new Error('Недопустимое действие над объектом', $entity->uri());
                $error->integrity = new Error(array('Уничтожение невозможно. Объект используется в качесвте прототипа для других объектов (%s)', $uris), 'heirs-exists');
                throw $error;
            }
        }
        // Обновить дату изменения у родителей
        $this->updateDate($id, time());
        // Удалить объект
        $q = $this->db->prepare('
            DELETE ids, objects, parents, protos FROM parents p, ids, objects, parents, protos
            WHERE p.parent_id = ?
            AND p.object_id = ids.id
            AND p.object_id = objects.id
            AND p.object_id = protos.object_id
            AND p.object_id = parents.object_id
            AND p.is_delete = 0
        ');
        $q->execute(array($id));
        // Удалении директории со всеми файлами
        File::clearDir($entity->dir(true), true);
        Cache::delete('mysqlstore/localids');
        return $q->rowCount() > 0;
    }

    /**
     * Поиск обновлений для объекта в прототипе или info файле.
     * Если для объекта есть обновления, то он помечается признаком dif = changed
     * Ищутся новые свойства (подчиненные объекты) от прототипа или info файлов. Найденные объекты сохраняются с признаком dif = new, если уже были проиндексированы после создания. Иначе без признака.
     * Проверяются существующие свойства объекта, если их прототип отсутсвует, но был свойством прототипа родителя, то такие свойства помечаются dif = delete
     * Для каждого свойства запускается findUpdate()
     *
     * @param Entity $entity Обновляеый объект
     * @param int $step_size Количество проверяемых подчиненных за раз
     * @param int $depth Глубина обновления
     * @throws \Exception
     */
    public function refresh($entity, $step_size = 50, $depth = 1)
    {
        // Выбрать прототип. Если прототип не индексирован, то индексировать его
        $proto = $entity->proto();
        // Если у объекта прототип указан, но он не выбран (не существует), то сохранить объект с diff = delete.
        if (!$proto && $entity->_attribs['proto']){
            $entity->_attribs['diff'] = Entity::DIFF_DELETE;
        }else{
            $entity->_attribs['diff'] = Entity::DIFF_NO;
        }
        // Подготовка запроса для сохранения времени индексации объекта
        $osave = $this->db->prepare('
            UPDATE {objects} SET `diff` = :diff, update_time = :itime, update_step = :istep
            WHERE id = :id AND owner = :owner AND lang = :lang AND is_history = 0
        ');
        $id = $this->localId($entity->id(), false);
        $update_time = $entity->_attribs['update_time'];
        // Проверка изменений в прототипе
        if ($proto){
            // Сравнить прототип с объектом. Если объект использует значение по умолчанию и оно отличается от прототипа, то сохранить объект с признаком dif = change.
            if ($entity->isDefaultValue() && $entity->value()!=$proto->value()){
                $entity->_attribs['diff'] = Entity::DIFF_CHANGE;
            }
            // Поиск обновлений для подчиненных
            if ($depth > 0){
                // Удалить объекты с diff=add.
                $q = $this->db->prepare('
                    DELETE ids, objects, parents, protos FROM parents p, ids, objects, parents, protos
                    WHERE objects.parent = ?
                    AND objects.diff = ?
                    AND p.object_id = ids.id
                    AND p.object_id = objects.id
                    AND p.object_id = protos.object_id
                    AND p.object_id = parents.object_id
                    AND p.is_delete = 0
                ');
                $q->execute(array($id, Entity::DIFF_ADD));
                Cache::delete('mysqlstore/localids');
                // У остальных обнулить diff
                $q = $this->db->prepare('UPDATE {objects} SET `diff` = 0 WHERE parent = ? AND diff > 0');
                $q->execute(array($id));
                // С учётом update_step выбрать $step_size подчиненных прототипа. Если выбрано меньше $step_size, то update_step = 0, иначе +50. Сохранить объект с новым update_step
                $pchildren = $proto->find(array(
                    'select' => array('children'),
                    'limit' => array($entity->_attribs['update_step'], $step_size),
                    'key' => 'uri'
                ), false, false, false);
                if (count($pchildren) < $step_size){
                    // В следующий раз обновление по новой
                    $entity->_attribs['update_step'] = 0;
                }else{
                    // В следующий раз обновление будет продолжено
                    $entity->_attribs['update_step'] = $entity->_attribs['update_step'] + $step_size;
                }
                // время обновляется если update_step =0 или объект ранее был полностью унаследован от прототипа после создания
                $entity->_attribs['update_time'] = ($update_time || !$entity->_attribs['update_step'])? time() : 0;
                // Сохранение объекта с новыми diff, update_time, update_step
                $osave->execute(array(
                    ':diff' => $entity->_attribs['diff'],
                    ':itime' => $entity->_attribs['update_time'],
                    ':istep' => $entity->_attribs['update_step'],
                    ':id' => $this->localId($entity->id()),
                    ':owner' => empty($entity->_attribs['owner']) ? Entity::ENTITY_ID : $this->localId($entity->_attribs['owner']),
                    ':lang' => empty($entity->_attribs['lang']) ? Entity::ENTITY_ID : $this->localId($entity->_attribs['lang'])
                ));
                $pids = array();
                foreach ($pchildren as $pchild){
                    $pids[] = $pchild->key();
                }
                // У объекта выбрать подчиненные, которые прототипируются от выбранных $step_size подчиненных прототипа.
                $ochildren = $entity->find(array(
                    'where' => array('attr', 'proto', 'in', $pids),
                ), false, false, false);
                // Для выбранных по прототипам подчиненных выполнить findUpdate с $depth-1
                foreach ($ochildren as $child){
                    /** @var $child Entity */
                    self::refresh($child, $step_size, $depth-1);
                    // Из $pchildren удаляем объект, используемый в качесвте прототпа
                    if (($p = $child->proto()) && isset($pchildren[$p->uri()])) unset($pchildren[$p->uri()]);
                }
                $diff = $update_time == 0 ? Entity::DIFF_NO : Entity::DIFF_ADD;
                // Прототипы, по которым не были найдены подчиненные использовать для создания новых подчиненных с diff = add
                foreach ($pchildren as $proto){
                    /** @var $proto Entity */
                    $child = $proto->birth($entity);
                    $child->_attribs['diff'] = $diff;
                    $this->write($child, false, true);
                }
                return;
            }
            // время обновляется если объект ранее был полностью унаследован от прототипа после создания
            $entity->_attribs['update_time'] = $update_time ? time() : 0;
        }else{
            $entity->_attribs['update_time'] = time();
        }
        // Проверка изменений в info файлах
        if ($entity->_attribs['diff'] == Entity::DIFF_NO){
            if ($update_time!=0 && is_file($entity->dir(true).$entity->name().'.info')){
                $info = json_decode(file_get_contents($entity->dir(true).$entity->name().'.info'), true);
                if (is_array($info)){
                    // @todo Сравнить атриубты объекта.
                    // @todo Проверить изменения для всех $info['children'] рекурсивно
                }
            }
            if ($depth > 0){
                // @todo Сканирование директорие на наличие поддиректорий с файлами .info
                // @todo По пути на файл выбирать объект из бд. Если объекта нет, то файлом опредлен новый объект.
            }
        }
        $osave->execute(array(
            ':diff' => $entity->_attribs['diff'],
            ':itime' => $entity->_attribs['update_time'],
            ':istep' => 0,
            ':id' => $this->localId($entity->id()),
            ':owner' => empty($entity->_attribs['owner']) ? Entity::ENTITY_ID : $this->localId($entity->_attribs['owner']),
            ':lang' => empty($entity->_attribs['lang']) ? Entity::ENTITY_ID : $this->localId($entity->_attribs['lang'])
        ));
    }

    /**
     * Обновление даты изменения объекта и всех его родителей
     * @param int $local_id Локальный идентификатор объекта
     * @param int $date Новая дата изменения объекта
     * @return bool Признак, было ли совершено обновление?
     */
    protected function updateDate($local_id, $date)
    {
        $q = $this->db->prepare('
            UPDATE {objects}, {parents} SET {objects}.date=? WHERE {parents}.object_id = ? AND {parents}.parent_id = {objects}.id AND {objects}.is_history=0
        ');
        $q->execute(array($date, $local_id));
        return $q->rowCount()>0;
    }

    /**
     * Конвертирование условия поиска в SQL запрос
     * @param array $cond Условие поиска
     * @param bool $only_where
     * @throws \Exception
     * @return array Ассоциативный массив SQL запроса и значений, вставляемых в него вместо "?"
     */
    protected function getCondSQL($cond, $only_where = false)
    {
        $result = array(
            'select' => '',
            'from' => '',
            'joins' => '',
            'where' => '',
            'group' => '',
            'order' => '',
            'limit' => '',
            'binds' => array()  // Значения в SQL услвоие
        );
        // Информация о слияниях
        $joins = array('obj' => null);
        $binds2 = array();
        // количество услой IS
        $t_cnt = 1;
        // Условия для локализации и персонализации
        $owner_lang = '1';
        if (isset($cond['owner'])){
            $owner_lang = ($cond['owner'] == Entity::ENTITY_ID)? '`owner` = ?' : '`owner` IN (?, 4294967295)';
        }
        if (isset($cond['lang'])){
            if ($owner_lang == '1'){
                $owner_lang = '';
            }else{
                $owner_lang.=' AND ';
            }
            $owner_lang.= ($cond['lang'] == Entity::ENTITY_ID)? '`lang` = ?' : '`lang` IN (?, 4294967295)';
        }

        if (!$only_where){
            // Что?
            if ($cond['select'][0] == 'count'){
                $result['select'] = 'SELECT count(*) fun';
                $calc = true;
            }else
            if (in_array($cond['select'][0], array('self', 'children', 'parents', 'tree', 'protos', 'heirs'))){
                // @todo учитывать указанные атрибуты в $cond['select'][1..n]
                $result['select'] = 'SELECT u.uri, obj.*';
                $calc = false;
            }else{
                $result['select'] = 'SELECT 1 fun';
                $calc = true;
            }

            if ($cond['select'][0] == 'count' || $cond['select'][0] == 'exists'){
                $what = $cond['select'][1];
            }else{
                $what = $cond['select'][0];
            }
            if ($multy = is_array($cond['from'])){
                $multy_cnt = count($cond['from']);
                if ($what == 'self' || $cond['select'][0] == 'exists'){
                    $cond['limit'] = array(0,$multy_cnt);
                }
            }else{
                if ($what == 'self' || $what == 'link'){
                    $cond['limit'] = array(0,1);
                }
            }
            // От куда?
            if ($what == 'self'){
                // Выбор from
                // Контроль доступа
                if (!empty($cond['access']) && IS_INSTALL && ($acond = \Boolive\auth\Auth::getUser()->getAccessCond('read'))){
                    $acond = $this->getCondSQL(array('where'=>$acond, 'owner'=>$cond['owner'], 'lang'=>$cond['lang']), true);
                    $result['select'].= ', IF('.$acond['where'].',1,0) is_accessible';
                    $result['joins'].=$acond['joins'];
                    $result['binds'] = array_merge($acond['binds'], $result['binds']);
                }
                $result['select'].= ', u.id `id2`';
                $result['from'] = 'FROM {ids} u LEFT JOIN {objects} obj ON obj.id = u.id AND ';
                // Условие на владельца и язык
                $result['from'].= ($cond['owner'] == Entity::ENTITY_ID)? 'obj.`owner` = ?' : 'obj.`owner` IN (?, 4294967295)';
                $result['from'].= ($cond['lang'] == Entity::ENTITY_ID)? ' AND obj.`lang` = ?' : ' AND obj.`lang` IN (?, 4294967295)';
                $result['binds'][] = array($cond['owner'], DB::PARAM_INT);
                $result['binds'][] = array($cond['lang'], DB::PARAM_INT);
                // Идентификация
                // Если from множественный, то формат запроса определяется по первому from!
                $from_info = Data::parseUri($cond['from']);
                if (!empty($from_info['id'])){
                    if (empty($from_info['path'])){
                        if ($multy){
                            // Множественный выбор по идентификатору
                            $result['select'].= ', CONCAT("//", u.id) as `from` ';
                            $result['where'].= 'u.id IN ('.rtrim(str_repeat('?,', $multy_cnt),',').')';
                            for ($i=0; $i<$multy_cnt; $i++){
                                $from_info = Data::parseUri($cond['from'][$i]);
                                $result['binds'][] = array($from_info['id'], DB::PARAM_STR);
                            }
                            if ($calc) $result['group'] = ' GROUP BY u.id';
                        }else{
                            // Одиночный выбор по идентификатору
                            $result['where'].= 'u.id = ?';
                            $result['binds'][] = array($from_info['id'], DB::PARAM_INT);
                        }
                    }else{
                        if ($multy){
                            // Множественный выбор по родителю и имени объекта
                            $result['select'].= ', CONCAT("//", {obj}.parent, "/", {obj}.name) as `from` ';
                            $w = '';
                            for ($i=0; $i<$multy_cnt; $i++){
                                $from_info = Data::parseUri($cond['from'][$i]);
                                if (!empty($w)) $w.=' OR ';
                                $w.= '({obj}.parent = ? AND {obj}.name = ?)';
                                $result['binds'][] = $from_info['id'];
                                $result['binds'][] = ltrim($from_info['path'],'/');
                            }
                            $result['where'].='('.$w.')';
                            if ($calc) $result['group'] = ' GROUP BY `from`';
                        }else{
                            // Одиночный выбор по родителю и имени объекта
                            $result['where'].= '{obj}.parent = ? AND {obj}.name = ?';
                            $result['binds'][] = $from_info['id'];
                            $result['binds'][] = ltrim($from_info['path'],'/');
                        }
                    }
                }else{
                    if ($multy){
                        // Множественный выбор по URI
                        $result['select'].= ', u.uri as `from` ';
                        $result['where'].= 'u.uri IN ('.rtrim(str_repeat('?,', $multy_cnt),',').')';
                        for ($i=0; $i<$multy_cnt; $i++){
                            $result['binds'][] = array($cond['from'][$i], DB::PARAM_STR);
                        }
                        if ($calc) $result['group'] = ' GROUP BY u.uri';
                    }else{
                        // Одиночный выбор по URI
                        $result['where'].= 'u.uri = ?';
                        $result['binds'][] = array($cond['from'], DB::PARAM_STR);
                    }
                }
            }else{
                // Дополняем условие контролем доступа
                if (!empty($cond['access']) && IS_INSTALL && ($acond = \Boolive\auth\Auth::getUser()->getAccessCond('read', $cond['from']))){
                    if (empty($cond['where'])){
                        $cond['where'] = array($acond);
                    }else{
                        if (is_string($cond['where'][0])){
                            if ($cond['where'][0] == 'all'){
                               $cond['where'][1][] = $acond;
                            }else{
                               $cond['where'] = array($cond['where'], $acond);
                            }
                        }else{
                            $cond['where'][] = $acond;
                        }
                    }
                }
                if ($what == 'children' || $what == 'tree'){
                    // Выбор подчиненных
                    // Выбор всех подчиненных
                    if ($cond['depth'][1] == Entity::MAX_DEPTH && $cond['depth'][0]<=1){
                        // Поиск по всей ветке
                        $result['from'] = ' FROM {objects} obj JOIN {ids} u ON (u.id = obj.id)';
                        if ($multy){
                            $result['select'].= ', CONCAT("//",t.parent_id) as `from`';
                            $result['from'].= "\n  JOIN {parents} t ON (t.object_id = obj.id AND t.parent_id IN (".rtrim(str_repeat('?,', $multy_cnt),',').')'.($cond['depth'][0]==1?' AND t.object_id!=t.parent_id':'').' AND t.is_delete=0)';
                            for ($i=0; $i<$multy_cnt; $i++){
                                $binds2[] = array($this->localId($cond['from'][$i]), DB::PARAM_INT);
                            }
                            if ($calc) $result['group'] = ' GROUP BY t.parent_id';
                        }else{
                            $result['from'].= "\n  JOIN {parents} t ON (t.object_id = obj.id AND t.parent_id = ?".($cond['depth'][0]==1?' AND t.object_id!=t.parent_id':'').' AND t.is_delete=0)';
                            $binds2[] = array($this->localId($cond['from']), DB::PARAM_INT);
                        }
                        // сортировка по порядковому номеру будет выполнена после выборки, чтобы при выборке не использовалась файловая сортировка
                        if ($what == 'tree' && empty($cond['select'][1]) && empty($cond['limit']) &&
                            !empty($cond['order']) && count($cond['order'])== 1 && $cond['order'][0][0] == 'order'){
                            $cond['order'] = false;
                        }
                    }else
                    if ($cond['depth'][0] == 1 && $cond['depth'][1] == 1){
                        // Подчиненные объекты
                        $result['from'] = ' FROM {objects} obj USE INDEX(property) JOIN {ids} u ON (u.id = obj.id)';
                        if ($multy){
                            $result['select'].= ', CONCAT("//",obj.parent) as `from`';
                            $result['where'].= 'obj.parent IN ('.rtrim(str_repeat('?,', $multy_cnt),',').') AND ';
                            for ($i=0; $i<$multy_cnt; $i++){
                                $result['binds'][] = array($this->localId($cond['from'][$i]), DB::PARAM_STR);
                            }
                            if ($calc) $result['group'] = ' GROUP BY obj.parent';
                        }else{
                            // Сверка parent
                            $result['where'].= 'obj.parent = ? AND ';
                            $result['binds'][] = array($this->localId($cond['from']), DB::PARAM_INT);
                        }
                        // Оптимизация сортировки по атрибуту order
                        if (!empty($cond['order']) && count($cond['order'])== 1 && $cond['order'][0][0] == 'order' && strtoupper($cond['order'][0][1])=='ASC'){
                            $cond['order'] = false;
                        }
                    }else{
                        // Поиск по ветке с ограниченной глубиной
                        $result['from'] = ' FROM {objects} obj JOIN {ids} u ON (u.id = obj.id)';
                        if ($multy){
                            $result['select'].= ', CONCAT("//",f.parent_id) as `from`';
                            $w = '';
                            for ($i=0; $i<$multy_cnt; $i++){
                                if (!empty($w)) $w.=' OR ';
                                $w.= "(f.object_id = obj.id AND f.parent_id = ? AND f.level>=? AND f.level<=?)";
                                $binds2[] = array($this->localId($cond['from'][$i]), DB::PARAM_INT);
                                $binds2[] = array($cond['depth'][0], DB::PARAM_INT);
                                $binds2[] = array($cond['depth'][1], DB::PARAM_INT);
                            }
                            $result['from'].= "\n  JOIN {parents} f ON ((".$w.') AND f.is_delete=0)';
                            if ($calc) $result['group'] = ' GROUP BY f.parent_id';
                        }else{
                            $result['from'].= "\n  JOIN {parents} f ON (f.object_id = obj.id AND f.parent_id = ? AND f.level>=? AND f.level<=? AND f.is_delete=0)";
                            $binds2[] = array($this->localId($cond['from']), DB::PARAM_INT);
                            $binds2[] = array($cond['depth'][0], DB::PARAM_INT);
                            $binds2[] = array($cond['depth'][1], DB::PARAM_INT);
                            // сортировка по порядковому номеру будет выполнена после выборки, чтобы при выборке не использовалась файловая сортировка
                            if ($what == 'tree' && empty($cond['select'][1]) && empty($cond['limit']) &&
                                !empty($cond['order']) && count($cond['order'])== 1 && $cond['order'][0][0] == 'order'){
                                $cond['order'] = false;
                            }
                        }
                    }
                }else
                if ($what == 'parents'){
                    // Выбор родителей
                    // Выбор всех родителей from
                    if ($cond['depth'][1] == Entity::MAX_DEPTH && $cond['depth'][0]<=1){
                        // Поиск по всей ветке
                        $result['from'] = ' FROM {objects} obj JOIN {ids} u ON (u.id = obj.id)';
                        if ($multy){
                            $result['select'].= ', CONCAT("//",t.object_id) as `from`';
                            $result['from'].= "\n  JOIN {parents} t ON (t.parent_id = obj.id AND t.object_id IN (".rtrim(str_repeat('?,', $multy_cnt),',').')'.($cond['depth'][0]==1?' AND t.object_id!=t.parent_id':'').' AND t.is_delete=0)';
                            for ($i=0; $i<$multy_cnt; $i++){
                                $result['binds'][] = array($this->localId($cond['from'][$i]), DB::PARAM_STR);
                            }
                            if ($calc) $result['group'] = ' GROUP BY t.object_id';
                        }else{
                            $result['from'].= "\n  JOIN {parents} t ON (t.parent_id = obj.id AND t.object_id = ?".($cond['depth'][0]==1?' AND t.object_id!=t.parent_id':'').' AND t.is_delete=0)';
                            $binds2[] = array($this->localId($cond['from']), DB::PARAM_INT);
                        }
                    }else{
                        // Поиск ограниченной глубиной (кол-ва родителей)
                        $result['from'] = ' FROM {objects} obj JOIN {ids} u ON (u.id = obj.id)';
                        if ($multy){
                            $result['select'].= ', CONCAT("//",f.object_id) as `from`';
                            $w = '';
                            for ($i=0; $i<$multy_cnt; $i++){
                                if (!empty($w)) $w.=' OR ';
                                $w.= "(f.parent_id = obj.id AND f.object_id = ? AND f.level>=? AND f.level<=?)";
                                $binds2[] = array($this->localId($cond['from'][$i]), DB::PARAM_INT);
                                $binds2[] = array($cond['depth'][0], DB::PARAM_INT);
                                $binds2[] = array($cond['depth'][1], DB::PARAM_INT);
                            }
                            $result['from'].= "\n  JOIN {parents} f ON ((".$w.') AND f.is_delete=0)';
                            if ($calc) $result['group'] = ' GROUP BY f.object_id';
                        }else{
                            $result['from'].= "\n  JOIN {parents} f ON (f.parent_id = obj.id AND f.object_id = ? AND f.level>=? AND f.level<=? AND f.is_delete=0)";
                            $binds2[] = array($this->localId($cond['from']), DB::PARAM_INT);
                            $binds2[] = array($cond['depth'][0], DB::PARAM_INT);
                            $binds2[] = array($cond['depth'][1], DB::PARAM_INT);
                        }
                    }
                }else
                if ($what == 'protos'){
                    // Выбор или подсчёт прототипов
                    // Выбор всех прототипов from
                    if ($cond['depth'][1] == Entity::MAX_DEPTH && $cond['depth'][0]<=1){
                        // Поиск по всей ветке
                        $result['from'] = ' FROM {objects} obj JOIN {ids} u ON (u.id = obj.id)';
                        if ($multy){
                            $result['select'].= ', CONCAT("//",t.object_id) as `from`';
                            $result['from'].= "\n  JOIN {protos} t ON (t.proto_id = obj.id AND t.object_id IN (".rtrim(str_repeat('?,', $multy_cnt),',').')'.($cond['depth'][0]==1?' AND t.object_id!=t.proto_id':'').' AND t.is_delete=0)';
                            for ($i=0; $i<$multy_cnt; $i++){
                                $result['binds'][] = array($this->localId($cond['from'][$i]), DB::PARAM_STR);
                            }
                            if ($calc) $result['group'] = ' GROUP BY t.object_id';
                        }else{
                            $result['from'].= "\n  JOIN {protos} t ON (t.proto_id = obj.id AND t.object_id = ?".($cond['depth'][0]==1?' AND t.object_id!=t.proto_id':'').' AND t.is_delete=0)';
                            $binds2[] = array($this->localId($cond['from']), DB::PARAM_INT);
                        }
                    }else{
                        // Поиск ограниченной глубиной (кол-ва прототипов)
                        $result['from'] = ' FROM {objects} obj JOIN {ids} u ON (u.id = obj.id)';
                        if ($multy){
                            $result['select'].= ', CONCAT("//",f.object_id) as `from`';
                            $w = '';
                            for ($i=0; $i<$multy_cnt; $i++){
                                if (!empty($w)) $w.=' OR ';
                                $w.= "(f.proto_id = obj.id AND f.object_id = ? AND f.level>=? AND f.level<=?)";
                                $binds2[] = array($this->localId($cond['from'][$i]), DB::PARAM_INT);
                                $binds2[] = array($cond['depth'][0], DB::PARAM_INT);
                                $binds2[] = array($cond['depth'][1], DB::PARAM_INT);
                            }
                            $result['from'].= "\n  JOIN {protos} f ON ((".$w.') AND f.is_delete=0)';
                            if ($calc) $result['group'] = ' GROUP BY f.object_id';
                        }else{
                            $result['from'].= "\n  JOIN {protos} f ON (f.proto_id = obj.id AND f.object_id = ? AND f.level>=? AND f.level<=? AND f.is_delete=0)";
                            $binds2[] = array($this->localId($cond['from']), DB::PARAM_INT);
                            $binds2[] = array($cond['depth'][0], DB::PARAM_INT);
                            $binds2[] = array($cond['depth'][1], DB::PARAM_INT);
                        }
                    }
                }else
                if ($what == 'heirs'){
                    // Выбор наследников
                    // Выбор списка записей из from
                    if ($cond['depth'][1] == Entity::MAX_DEPTH && $cond['depth'][0]<=1){
                        // Поиск по всей ветке
                        $result['from'] = ' FROM {objects} obj JOIN {ids} u ON (u.id = obj.id)';
                        if ($multy){
                            $result['select'].= ', CONCAT("//",t.proto_id) as `from`';
                            $result['from'].= "\n  JOIN {protos} t ON (t.object_id = obj.id AND t.proto_id IN (".rtrim(str_repeat('?,', $multy_cnt),',').')'.($cond['depth'][0]==1?' AND t.object_id!=t.proto_id':'').' AND t.is_delete=0)';
                            for ($i=0; $i<$multy_cnt; $i++){
                                $result['binds'][] = array($this->localId($cond['from'][$i]), DB::PARAM_STR);
                            }
                            if ($calc) $result['group'] = ' GROUP BY t.proto_id';
                        }else{
                            $result['from'].= "\n  JOIN {protos} t ON (t.object_id = obj.id AND t.proto_id = ?".($cond['depth'][0]==1?' AND t.object_id!=t.proto_id':'').' AND t.is_delete=0)';
                            $binds2[] = array($this->localId($cond['from']), DB::PARAM_INT);
                        }
                    }else
                    if ($cond['depth'][0] == 1 && $cond['depth'][1] == 1){
                        // Прямые наследники
                        $result['from'] = ' FROM {objects} obj USE INDEX(property) JOIN {ids} u ON (u.id = obj.id)';
                        if ($multy){
                            $result['select'].= ', CONCAT("//",obj.proto) as `from`';
                            $result['where'].= 'obj.proto IN ('.rtrim(str_repeat('?,', $multy_cnt),',').') AND ';
                            for ($i=0; $i<$multy_cnt; $i++){
                                $result['binds'][] = array($this->localId($cond['from'][$i]), DB::PARAM_STR);
                            }
                            if ($calc) $result['group'] = ' GROUP BY obj.proto';
                        }else{
                            // Сверка proto
                            $result['where'].= "obj.proto = ? AND ";
                            $result['binds'][] = array($this->localId($cond['from']), DB::PARAM_INT);
                        }
                    }else{
                        // Поиск по ветке наследования с ограниченной глубиной
                        $result['from'] = ' FROM {objects} obj JOIN {ids} u ON (u.id = obj.id)';
                        if ($multy){
                            $result['select'].= ', CONCAT("//",f.proto_id) as `from`';
                            $w = '';
                            for ($i=0; $i<$multy_cnt; $i++){
                                if (!empty($w)) $w.=' OR ';
                                $w.= "(f.object_id = obj.id AND f.proto_id = ? AND f.level>=? AND f.level<=?)";
                                $binds2[] = array($this->localId($cond['from'][$i]), DB::PARAM_INT);
                                $binds2[] = array($cond['depth'][0], DB::PARAM_INT);
                                $binds2[] = array($cond['depth'][1], DB::PARAM_INT);
                            }
                            $result['from'].= "\n  JOIN {protos} f ON ((".$w.') AND f.is_delete=0)';
                            if ($calc) $result['group'] = ' GROUP BY f.proto_id';
                        }else{
                            $result['from'].= "\n  JOIN {protos} f ON (f.object_id = obj.id AND f.proto_id = ? AND f.level>=? AND f.level<=? AND f.is_delete=0)";
                            $binds2[] = array($this->localId($cond['from']), DB::PARAM_INT);
                            $binds2[] = array($cond['depth'][0], DB::PARAM_INT);
                            $binds2[] = array($cond['depth'][1], DB::PARAM_INT);
                        }
                    }
                }else{
                    throw new \Exception('Incorrect selection in condition: ("'.$cond['select'][0].'","'.$cond['select'][1].'")');
                }
                // Условие на владельца и язык
                $result['where'].="(obj.id, obj.owner, obj.lang) IN (SELECT id, `owner`, lang FROM {objects} WHERE id=obj.id AND $owner_lang GROUP BY id)\n  ";
                $result['binds'][] = array($cond['owner'], DB::PARAM_INT);
                $result['binds'][] = array($cond['lang'], DB::PARAM_INT);
            }
            // Сортировка
            if (!$calc && !empty($cond['order'])){
                $cnt = count($cond['order']);
                for ($i=0; $i<$cnt; $i++){
                    if (($ocnt = count($cond['order'][$i])-2)>=0){
                        $jtable = $pretabel = 'obj';
                        if ($ocnt>0){
                            // Сортировка по подчиненным объектами. Требуется слияние таблиц
                            for ($o = 0; $o < $ocnt; $o++){
                                $joins[$jtable = $jtable.'.'.$cond['order'][$i][$o]] = array($pretabel, $cond['order'][$i][$o]);
                            }
                        }
                        if ($result['order']) $result['order'].=', ';
                        $result['order'].= '`'.$jtable.'`.`'.$cond['order'][$i][$ocnt].'` '.$cond['order'][$i][$ocnt+1];
                    }
                }
                if ($result['order']) $result['order'] = "\n  ORDER BY ".$result['order'];
            }
        }
        // Основное условие
        if (!empty($cond['where'])){
            $store = $this;
            /**
             * Рекурсивная функция форматирования условия в SQL
             * @param array $cond Условие
             * @param string $glue Логическая оперция объединения условий
             * @param string $table Алиас таблицы. Изменяется в соответсвии с вложенностью условий на подчиенных
             * @param int $level Уровень вложености вызова функции
             * @param array $attr_exists Есть ли условия на указанные атрибуты? Если нет, то добавляется услвоие по умолчанию
             * @return string SQL условия в WHERE
             */
            $convert = function($cond, $glue = ' AND ', $table = 'obj', $level = 0, &$attr_exists = array()) use (&$convert, &$result, &$joins, $t_cnt, $store){
                $level++;
                // Нормализация групп условий
                if ($cond[0] == 'any' || $cond[0] == 'all'){
                    $glue = $cond[0] == 'any'?' OR ':' AND ';
                    $cond = $cond[1];
                }else
                if (count($cond)>0 && !is_array($cond[0])){
                    $cond = array($cond);
                    $glue = ' AND ';
                }
                foreach ($cond as $i => $c){
                    if (is_array($c) && !empty($c)){
                        // AND
                        if ($c[0]=='all'){
                            $cond[$i] = '('.$convert($c[1], ' AND ', $table, $level, $attr_exists).')';
                        }else
                        // OR
                        if ($c[0]=='any'){
                            $cond[$i] = '('.$convert($c[1], ' OR ', $table, $level, $attr_exists).')';
                        }else
                        // NOT - отрицание условий
                        if ($c[0]=='not'){
                            $cond[$i] = 'NOT('.$convert($c[1], ' AND ', $table, $level, $attr_exists).')';
                        }else
                        // Атрибут
                        if ($c[0]=='attr'){
                            // Если атрибут value, то в зависимости от типа значения используется соответсвующая колонка
                            if ($c[1] == 'value'){
                                $c[1] = is_numeric($c[3]) ? 'valuef': 'value';
                            }
                            if ($c[1] == 'parent' || $c[1] == 'proto' || $c[1] == 'owner' || $c[1] == 'lang'){
                                if (is_array($c[3])){
                                    foreach ($c[3] as $ci => $cv){
                                        $c[3][$ci] = $this->localId($cv, false);
                                    }
                                }else{
                                    $c[3] = $this->localId($c[3], false);
                                }
                            }
                            // sql услвоие
                            $cond[$i] = '`'.$table.'`.`'.$c[1].'` '.$c[2];
                            // Учитываем особенность синтаксиса условия IN
                            if (mb_strtolower($c[2]) == 'in'){
                                if (!is_array($c[3])) $c[3] = array($c[3]);
                                if (empty($c[3])){
                                    $cond[$i].='(NULL)';
                                }else{
                                    $cond[$i].='('.str_repeat('?,', count($c[3])-1).'?)';
                                    $result['binds'] = array_merge($result['binds'], $c[3]);
                                }
                            }else{
                                $cond[$i].= '?';
                                $result['binds'][] = $c[3];
                            }
                            if ($c[1] == 'is_history' || $c[1] == 'is_delete' || $c[1] == 'diff'){
                                $attr_exists[$c[1]] = true;
                            }
                        }else
                        // Условия на подчиенного
                        if ($c[0]=='child'){
                            $joins[$table.'.'.$c[1]] = array($table, $c[1]);
                            // Если условий на подчиненного нет, то проверяется его наличие
                            if (empty($c[2])){
                                $cond[$i] = '(`'.$table.'.'.$c[1].'`.id IS NOT NULL)';
                            }else{
                                $cond[$i] = '('.$convert($c[2], ' AND ', $table.'.'.$c[1], $level).')';
                            }
                        }else
                        // Условие на наличие родителя
                        if ($c[0]=='in'){
                            if (is_array($c[1])){
                                $c = $c[1];
                            }else{
                                unset($c[0]);
                            }
                            if (count($c)>0){
                                $alias = 'is'.$t_cnt;
                                $cond[$i] = 'EXISTS (SELECT 1 FROM {parents} `'.$alias.'` WHERE `'.$alias.'`.`object_id`=`'.$table.'`.id AND `'.$alias.'`.parent_id IN ('.rtrim(str_repeat('?,', count($c)), ',').') AND is_delete = 0)';
                                foreach ($c as $j => $key) $c[$j] = $store->localId($key);
                                $result['binds'] = array_merge($result['binds'], $c);
                            }else{
                                $cond[$i] = '1';
                            }
                        }else
                        // Условие на наличие прототипа.
                        if ($c[0]=='is'){
                            if (is_array($c[1])){
                                $c = $c[1];
                            }else{
                                unset($c[0]);
                            }
                            if (count($c)>0){
                                $alias = 'is'.$t_cnt;
                                $cond[$i] = 'EXISTS (SELECT 1 FROM {protos} `'.$alias.'` WHERE `'.$alias.'`.`object_id`=`'.$table.'`.id AND `'.$alias.'`.proto_id IN ('.rtrim(str_repeat('?,', count($c)), ',').') AND is_delete = 0)';
                                foreach ($c as $j => $key) $c[$j] = $store->localId($key);
                                $result['binds'] = array_merge($result['binds'], $c);
                            }else{
                                $cond[$i] = '1';
                            }
                        }else
                        // Является частью чего-то с учётом наследования
                        // Например заголовок статьи story1 является его частью и частью эталона статьи
                        // Пример, утверждение "Ветка является частью дерева" верно и для любого конкретного дерева
                        if ($c[0]=='of'){
                            if (is_array($c[1])){
                                $c = $c[1];
                            }else{
                                unset($c[0]);
                            }
                            if (count($c)>0){
                                $of = rtrim(str_repeat('?,', count($c)), ',');
                                $cond[$i] = 'EXISTS (SELECT 1 FROM {parents}, {protos} WHERE {parents}.object_id = {protos}.object_id AND {parents}.object_id=`'.$table.'`.id AND ({parents}.parent_id IN ('.$of.') OR {protos}.proto_id IN ('.$of.')) AND {parents}.is_delete = 0 AND {protos}.is_delete = 0)';
                                foreach ($c as $j => $key) $c[$j] = $store->localId($key);
                                $result['binds'] = array_merge($result['binds'], $c, $c);
                            }else{
                                $cond[$i] = '1';
                            }
                        }
                        // Не поддерживаемые услвоия игнорируем
                        else{
                            unset($cond[$i]);
                        }
                    }else{
                        unset($cond[$i]);
                    }
                }
                // Дополнительные услвоия по умолчанию
                if ($level == 1){
                    $more_cond = array();
                    if (empty($attr_exists['is_history'])) $more_cond[] = '`'.$table.'`.is_history = 0';
                    if (empty($attr_exists['is_delete'])) $more_cond[]  = '`'.$table.'`.is_delete = 0';
                    if (empty($attr_exists['diff'])) $more_cond[]  = '`'.$table.'`.diff != '.Entity::DIFF_ADD;
                    $attr_exists = array('is_history' => true, 'is_delete' => true, 'diff' => true);
                    if ($glue == ' AND '){
                        $cond = array_merge($cond, $more_cond);
                    }else
                    if (!empty($more_cond)){
                        array_unshift($more_cond, '('.implode($glue, $cond).')');
                        return implode(' AND ', $more_cond);
                    }
                }
                return implode($glue, $cond);
            };
            $attr_exists = $only_where ? array('is_history' => true, 'is_delete' => true, 'diff' => true) : array();
            // Если услвоия есть, то добавляем их в SQL
            if ($w = $convert($cond['where'], ' AND ', 'obj', 0, $attr_exists)){
                if (empty($result['where'])){
                    $result['where'] = $w;
                }else{
                    $result['where'].= " AND (".$w.')';
                }
            }
        }else{
            if ($cond['select'][0] == 'self'){
                $result['from'].= ' AND obj.is_history = 0';
            }else{
                if (!empty($result['where'])) $result['where'].=' AND ';
                $result['where'].= 'obj.is_history = 0 AND obj.is_delete = 0 AND obj.diff != '.Entity::DIFF_ADD;
            }
        }

        // Слияния для условий по подчиненным и сортировке по ним
        unset($joins['obj']);

        foreach ($joins as $alias => $info){
            $result['joins'].= "\n  LEFT JOIN {objects} `".$alias.'` ON (`'.$alias.'`.parent = `'.$info[0].'`.id AND `'.$alias.'`.name = ? AND (`'.$alias.'`.id, `'.$alias.'`.owner, `'.$alias.'`.lang) IN (SELECT id, `owner`, lang FROM {objects} WHERE id=`'.$alias.'`.id AND '.$owner_lang.' GROUP BY id))';
            $binds2[] = $info[1];
            $binds2[] = $cond['owner'];
            $binds2[] = $cond['lang'];
        }
        if ($binds2)  $result['binds'] = array_merge($binds2, $result['binds']);
        // Полноценный SQL
        if (!$only_where){
            // Ограничение по количеству и смещение
            if (!empty($cond['limit'])){
                $result['limit'] = "\n  LIMIT ?,?";
                $result['binds'][] = array((int)$cond['limit'][0], DB::PARAM_INT);
                $result['binds'][] = array((int)$cond['limit'][1], DB::PARAM_INT);
            }
        }
        // Полноценный SQL
        $result['sql'] = $result['select'].$result['from'].$result['joins']."\n  WHERE ".$result['where'].$result['group'].$result['order'].$result['limit'];
        return $result;
    }

    /**
     * Создание или обновление отношений между родителями объекта
     * @param $object Объект, для которого обновляются отношения с родителем
     * @param $parent Новый родитель объекта
     * @param int $dl Разница между новым и старым уровнем вложенности объекта
     * @param bool $remake Признак, отношения обновлять (при смене родителя) или создавать новые (новый объект)
     */
    private function makeParents($object, $parent, $dl, $remake = false)
    {
        if ($remake){
            // У подчинённых удалить отношения с родителями, которые есть у $object
            $q = $this->db->prepare('
                UPDATE {parents} p, (
                    SELECT c.object_id, c.parent_id FROM {parents} p
                    JOIN {parents} c ON c.object_id = p.object_id AND c.object_id!=c.parent_id AND c.parent_id IN (SELECT parent_id FROM {parents} WHERE object_id = :obj)
                    WHERE p.parent_id = :obj)p2
                SET p.is_delete = 1
                WHERE p.object_id = p2.object_id AND p.parent_id = p2.parent_id
            ');
            $q->execute(array(':obj'=>$object));
            // Обновить level оставшихся отношений в соответсвии с изменением level с новым родителем
            if ($dl != 0){
                $q = $this->db->prepare('
                    UPDATE {parents} c, (SELECT object_id FROM {parents} WHERE parent_id = :obj)p
                    SET c.level = c.level+:dl
                    WHERE c.object_id!=c.parent_id AND c.is_delete=0 AND p.object_id = c.object_id
                ');
                $q->execute(array(':obj'=>$object, ':dl'=>$dl));
            }
            if ($parent > 0){
                // Для объекта и всех его подчиненных создать отношения с новыми родителями
                $q = $this->db->prepare('SELECT object_id, `level` FROM {parents} WHERE parent_id = :obj ORDER BY level');
                $make = $this->db->prepare('
                    INSERT {parents} (object_id, parent_id, `level`)
                    SELECT :obj, parent_id, `level`+1+:l FROM {parents}
                    WHERE object_id = :parent
                    UNION SELECT :obj,:obj,0
                    ON DUPLICATE KEY UPDATE `level` = VALUES(level), is_delete = 0
                ');
                $q->execute(array(':obj'=>$object));
                while ($row = $q->fetch(DB::FETCH_ASSOC)){
                    $make->execute(array(':obj'=>$row['object_id'], ':parent'=>$parent, ':l'=>$row['level']));
                }
            }
        }else
        if ($parent >= 0){
            $make = $this->db->prepare('
                INSERT {parents} (object_id, parent_id, `level`)
                SELECT :obj, parent_id, `level`+1 FROM {parents}
                WHERE object_id = :parent
                UNION SELECT :obj,:obj,0
                ON DUPLICATE KEY UPDATE `level` = VALUES(level), is_delete = 0
            ');
            $make->execute(array(':obj' => $object, ':parent'=>$parent));
        }
    }

    /**
     * Создание или обновление отношений с прототипами объекта
     * @param $object Объект, для которого обновляются отношения с прототипами
     * @param $proto Новый прототип объекта
     * @param int $dl Разница между новым и старым уровнем вложенности объекта среди прототипов
     * @param bool $remake Признак, отношения обновлять (при смене прототипа) или создавать новые (новый объект)
     * @param bool $incomplete Признак, объект небыл сохранен, но его уже прототипировали?
     */
    private function makeProtos($object, $proto, $dl, $remake = false, $incomplete = false)
    {
        if ($remake){
            // У наследников удалить отношения с прототипами, которые есть у $object
            $q = $this->db->prepare('
                UPDATE {protos} p, (
                    SELECT c.object_id, c.proto_id FROM {protos} p
                    JOIN {protos} c ON c.object_id = p.object_id AND c.proto_id!=:obj AND c.object_id!=c.proto_id AND c.proto_id IN (SELECT proto_id FROM {protos} WHERE object_id = :obj)
                    LEFT JOIN objects ON (c.proto_id = objects.id)
                    WHERE p.proto_id = :obj AND objects.id IS NOT NULL)p2
                SET p.is_delete = 1
                WHERE p.object_id = p2.object_id AND p.proto_id = p2.proto_id
            ');
            $q->execute(array(':obj'=>$object));
            // Обновить level оставшихся отношений в соответсвии с изменением level с новым прототипом
            // если объект полностью создан и различаются уровни
            if (!$incomplete && $dl != 0){
                $q = $this->db->prepare('
                    UPDATE {protos} c, (SELECT object_id FROM {protos} WHERE proto_id = :obj)p
                    SET c.level = c.level+:dl
                    WHERE c.object_id!=c.proto_id AND c.is_delete=0 AND p.object_id = c.object_id
                ');
                $q->execute(array(':obj'=>$object, ':dl'=>$dl));
            }
            // Для объекта и всех его наследников создать отношения с новыми прототипом
            if ($proto >= 0){
                $q = $this->db->prepare('SELECT object_id, `level` FROM {protos} WHERE proto_id = :obj AND is_delete = 0 ORDER BY `level`');
                $make = $this->db->prepare('
                    INSERT {protos} (object_id, proto_id, `level`)
                    SELECT :obj, proto_id, `level`+1+:l FROM {protos}
                    WHERE object_id = :parent
                    UNION SELECT :obj,:obj,0
                    ON DUPLICATE KEY UPDATE `level` = VALUES(level), is_delete = 0
                ');
                $q->execute(array(':obj'=>$object));
                while ($row = $q->fetch(DB::FETCH_ASSOC)){
                    $make->execute(array(':obj'=>$row['object_id'], ':parent'=>$proto, ':l'=>$row['level']));
                }
            }
        }else
        if ($proto >= 0){
            if ($proto == 0){
                $make = $this->db->prepare('
                    INSERT {protos} (object_id, proto_id, `level`)
                    VALUES  (:obj,:obj,0)
                    ON DUPLICATE KEY UPDATE `level` = VALUES(level), is_delete = 0
                ');
                $make->execute(array(':obj' => $object));
            }else{
                $cheack = $this->db->prepare('SELECT 1 FROM {protos} WHERE object_id=? and is_delete=0 LIMIT 0,1');
                $cheack->execute(array($proto));
                if ($cheack->fetch()){
                    // Если прототип в таблице protos
                    $sql = '
                        INSERT {protos} (object_id, proto_id, `level`)
                        SELECT :obj, proto_id, `level`+1 FROM {protos}
                        WHERE object_id = :parent
                        UNION SELECT :obj,:obj,0
                        ON DUPLICATE KEY UPDATE `level` = VALUES(level), is_delete = 0
                    ';
                }else{
                    // Если прототипа нет в таблице protos
                    $sql = '
                        INSERT {protos} (object_id, proto_id, `level`)
                        VALUES (:obj,:parent,1), (:obj,:obj,0)
                        ON DUPLICATE KEY UPDATE `level` = VALUES(level), is_delete = 0
                    ';
                    if ($incomplete){
                        // Объект уже кем-то прототипирован, поэтому для них тоже добавляется отношения с proto
                        $q = $this->db->prepare('
                            INSERT {protos} (object_id, proto_id, `level`)
                            SELECT protos.object_id, :parent, objects.proto_cnt+1 FROM protos, objects
                            WHERE proto_id = :obj AND object_id = objects.id
                            ON DUPLICATE KEY UPDATE `level` = VALUES(level), is_delete = 0
                        ');
                        $q->execute(array(':obj' => $object, ':parent'=>$proto));
                    }
                }
                $make = $this->db->prepare($sql);
                $make->execute(array(':obj' => $object, ':parent'=>$proto));
            }
        }
    }

    /**
     * Полное пересодание дерева родителей
     * @throws \Exception
     */
    public function rebuildParents()
    {
        try{
            $this->db->beginTransaction();
            // Очитска таблицы
            $this->db->query('TRUNCATE {parents}');
            // Отношения родитель - подчиненный
            $q = $this->db->prepare('
                SELECT {ids}.uri, {objects}.id, {objects}.parent, {objects}.parent_cnt
                FROM {objects}, {ids}
                WHERE {ids}.id = {objects}.id
                ORDER BY {ids}.uri
            ');
            $q->execute();

            $make_ref = $this->db->prepare('
                INSERT IGNORE {parents} (object_id, parent_id, `level`)
                SELECT :obj, parent_id, `level`+1 FROM {parents}
                WHERE object_id = :parent
                UNION SELECT :obj,:obj,0
            ');
            while ($row = $q->fetch(\Boolive\database\DB::FETCH_ASSOC)){
                $make_ref->execute(array(':obj'=>$row['id'], ':parent'=>$row['parent']));
            }
            $this->db->commit();
        }catch (\Exception $e){
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Полное пересоздание дерева прототипов
     * @throws \Exception
     */
    public function rebuildProtos()
    {
        try{
            $this->db->beginTransaction();
            // Очитска таблицы
            $this->db->query('TRUNCATE {protos}');
            // Отношения родитель - подчиненный
            $q = $this->db->prepare('SELECT {objects}.id, {objects}.proto FROM {objects} ORDER BY {objects}.proto_cnt');
            $q->execute();
            $make_ref = $this->db->prepare('
                INSERT IGNORE {protos} (object_id, proto_id, `level`)
                SELECT :obj, proto_id, `level`+1 FROM {protos}
                WHERE object_id = :proto
                UNION SELECT :obj,:obj,0
            ');
            while ($row = $q->fetch(\Boolive\database\DB::FETCH_ASSOC)){
                $make_ref->execute(array(':obj'=>$row['id'], ':proto'=>$row['proto']));
            }
            $this->db->commit();
        }catch (\Exception $e){
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Создание идентификатора для указанного URI.
     * Если объект с указанным URI существует, то будет возвращен его идентификатор
     * @param string $uri URI для которого нужно получить идентификатор
     * @param bool $create Создать идентификатор, если отсутствует?
     * @param bool $is_new
     * @return int|null
     */
    private function localId($uri, $create = true, &$is_new = false)
    {
        $is_new = false;
        if ($uri instanceof Entity){
            $uri = $uri->key();
        }
        if (!is_string($uri)){
            return null;
        }
        if ($uri == Entity::ENTITY_ID) return $uri;
        if ($info = Data::isShortUri($uri)){
            if (mb_substr($uri,0,mb_strlen($this->key)) == $this->key){
                if (empty($info['path'])){
                    // Сокращенный URI приндалежит данной секции, поэтому возвращаем вторую часть
                    return intval($info['id']);
                }else{
                    return $this->localId(Data::read($uri.'&cache='.(IS_INSTALL?1:0), false));
                }
            }else{
                // Получаем полный URI по сокращенному
                $uri = Data::read($uri.'&cache='.(IS_INSTALL?1:0), false);
                if ($uri->isExist()){
                    $uri = $uri->uri();
                }else{
                    return null;
                }
            }
        }
        // Из кэша
        if (!isset($this->_local_ids) && IS_INSTALL){
            if ($local_ids = Cache::get('mysqlstore/localids')){
                $this->_local_ids = json_decode($local_ids, true);
            }else{
                $this->_local_ids = array();
            }
        }
        if (isset($this->_local_ids[$uri])) return $this->_local_ids[$uri];
        // Поиск идентифкатора URI
        $q = $this->db->prepare('SELECT id FROM {ids} WHERE `uri`=? LIMIT 0,1 FOR UPDATE');
        $q->execute(array($uri));
        if ($row = $q->fetch(DB::FETCH_ASSOC)){
            $id = $row['id'];
            $is_new = false;
            $this->_local_ids[$uri] = $id;
            $this->_local_ids_change = true && IS_INSTALL;
        }else
        if ($create){
            // Создание идентифbкатора для URI
            $q = $this->db->prepare('INSERT INTO {ids} (`id`, `uri`) VALUES (null, ?)');
            $q->execute(array($uri));
            $id = $this->db->lastInsertId('id');
            $is_new = true;
        }else{
            return null;
        }
        unset($q);
        return intval($id);
    }

    /**
     * Создание объекта из атрибутов
     * @param array $attribs Атриубты объекта, выбранные из базы данных
     * @throws \Exception
     * @return Entity
     */
    private function makeObject($attribs)
    {
        $attribs['id'] = $this->key.'//'.$attribs['id'];
        $attribs['owner'] = $attribs['owner'] == Entity::ENTITY_ID ? null : $this->key.'//'.$attribs['owner'];
        $attribs['lang'] = $attribs['lang'] == Entity::ENTITY_ID ? null : $this->key.'//'.$attribs['lang'];
        $attribs['parent'] = $attribs['parent'] == 0 ? null : $this->key.'//'.$attribs['parent'];
        $attribs['proto'] = $attribs['proto'] == 0 ? null : $this->key.'//'.$attribs['proto'];
        if ($attribs['is_default_value'] == 0) unset($attribs['is_default_value']); else $attribs['is_default_value'] = $this->key.'//'.$attribs['is_default_value'];
        $attribs['is_default_class'] = ($attribs['is_default_class'] !== '0' && $attribs['is_default_class'] != Entity::ENTITY_ID)? $this->key.'//'.$attribs['is_default_class'] : $attribs['is_default_class'];
        $attribs['is_link'] = ($attribs['is_link'] !== '1' && $attribs['is_link'] !== '0' && $attribs['is_link'] != Entity::ENTITY_ID)? $this->key.'//'.$attribs['is_link'] : $attribs['is_link'];
        $attribs['is_accessible'] = isset($attribs['is_accessible'])? $attribs['is_accessible'] : 1;
        $attribs['is_exist'] = 1;
        $attribs['order'] = intval($attribs['order']);
        $attribs['date'] = intval($attribs['date']);
        $attribs['parent_cnt'] = intval($attribs['parent_cnt']);
        $attribs['proto_cnt'] = intval($attribs['proto_cnt']);
        if (empty($attribs['is_file'])) unset($attribs['is_file']); else $attribs['is_file'] = intval($attribs['is_file']);
        if (empty($attribs['is_history'])) unset($attribs['is_history']); else $attribs['is_history'] = intval($attribs['is_history']);
        if (empty($attribs['is_delete'])) unset($attribs['is_delete']); else $attribs['is_delete'] = intval($attribs['is_delete']);
        if (empty($attribs['is_hidden'])) unset($attribs['is_hidden']); else $attribs['is_hidden'] = intval($attribs['is_hidden']);
        $attribs['update_step'] = intval($attribs['update_step']);
        $attribs['update_time'] = intval($attribs['update_time']);
        $attribs['diff'] = intval($attribs['diff']);
        unset($attribs['valuef']);
        // Свой класс
        if (empty($attribs['is_default_class'])){
            $attribs['class'] = $this->getClassById($attribs['id']);
        }else
        if ($attribs['is_default_class'] != Entity::ENTITY_ID){
            $attribs['class'] = $this->getClassById($attribs['is_default_class']);
        }else{
            $attribs['class'] = '\\Boolive\\data\\Entity';
        }
        return $attribs;
    }

    /**
     * Название класса по идентификатору объекта для которого он определен
     * @param $id Идентификатор объекта со своим классом
     * @return string Название класса с пространством имен
     */
    private function getClassById($id)
    {
        if (!isset($this->classes)){
            if ($classes = Cache::get('mysqlstore/classes')){
                // Из кэша
                $this->classes = json_decode($classes, true);
            }else{
                // Из бд и создаём кэш
                $q = $this->db->query('SELECT ids.* FROM ids JOIN objects ON objects.id = ids.id AND objects.is_default_class = 0');
                $this->classes = array();
                while ($row = $q->fetch(DB::FETCH_ASSOC)){
                    if ($row['uri']!==''){
                        $names = F::splitRight('/', $row['uri'], true);
                        $this->classes['//'.$row['id']] = str_replace('/', '\\', trim($row['uri'],'/')) . '\\' . $names[1];
                    }else{
                        $this->classes['//'.$row['id']] = 'Site';
                    }
                }
                Cache::set('mysqlstore/classes', F::toJSON($this->classes, false));
            }
        }
        if (isset($this->classes[$id])){
            return $this->classes[$id];
        }
        return '\\Boolive\\data\\Entity';
    }

    /**
     * Валидация кэша соответствия локальных идентификаторов uri
     * @param $new_attr Новые атрибуты объекта
     * @param $last_attr Старые атриубты объекта
     */
    private function localIdCacheValidate($new_attr, $last_attr)
    {
        if (isset($last_attr['name']) && isset($last_attr['parent']) &&
            ($last_attr['name'] != $new_attr['name'] || $last_attr['parent'] != $new_attr['parent']))
        {
            Cache::delete('mysqlstore/localids');
            $this->_local_ids = null;
        }
    }

    /**
     * Валидация кэша соответствия названий классов идентифкаторам
     * @param $new_attr Новые атрибуты объекта
     * @param $last_attr Старые атриубты объекта
     */
    private function classesCacheValidate($new_attr, $last_attr)
    {
        $last_class = isset($last_attr['is_default_class'])? $last_attr['is_default_class'] : Entity::ENTITY_ID;
        if ($new_attr['is_default_class']!=$last_class && ($new_attr['is_default_class'] == 0 || $last_class == 0)){
            Cache::delete('mysqlstore/classes');
            $this->classes = null;
        }
    }

    /**
     * Действия после сохранения объекта
     * @param $new_attr Новые атрибуты объекта
     * @param $last_attr Старые атриубты объекта
     */
    private function afterWrite($new_attr, $last_attr)
    {
        $this->classesCacheValidate($new_attr, $last_attr);
        $this->localIdCacheValidate($new_attr, $last_attr);
    }

    /**
	 * Проверка системных требований для установки класса
	 * @return array
	 */
	static function systemRequirements()
    {
		$requirements = array();
		if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')){
			$requirements[] = 'Требуется расширение <code>pdo_mysql</code> для PHP';
		}
		return $requirements;
	}

    /**
     * Создание хранилища
     * @param $connect
     * @param null $errors
     * @throws \Boolive\errors\Error|null
     */
    static function createStore($connect, &$errors = null)
    {
        try{
            if (!$errors) $errors = new \Boolive\errors\Error('Некоректные параметры доступа к СУБД', 'db');
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
                        $errors->dbname = 'no_access';
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
                $errors->common = 'no_innodb';
                throw $errors;
            }
            // Есть ли таблицы в БД?
            $pfx = $connect['prefix'];
            $tables = array($pfx.'ids', $pfx.'objects', $pfx.'protos', $pfx.'parents');
            $q = $db->query('SHOW TABLES');
            while ($row = $q->fetch(DB::FETCH_NUM)/* && empty($config['prefix'])*/){
                if (in_array($row[0], $tables)){
                    // Иначе ошибка
                    $errors->dbname = 'db_not_empty';
                    throw $errors;
                }
            }
            // Создание таблиц
            $db->exec("
                CREATE TABLE {ids} (
                  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `uri` VARCHAR(1000) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `uri` (`uri`(255))
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='Идентификация путей (URI)'
            ");
            $db->exec("
                CREATE TABLE {objects} (
                  `id` INT(10) UNSIGNED NOT NULL COMMENT 'Идентификатор по таблице ids',
                  `owner` INT(10) UNSIGNED NOT NULL DEFAULT '4294967295' COMMENT 'Идентификатор объекта-владельца',
                  `lang` INT(10) UNSIGNED NOT NULL DEFAULT '4294967295' COMMENT 'Идентификатор объекта-языка',
                  `date` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Дата создания и версия',
                  `name` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Имя',
                  `order` INT(11) NOT NULL DEFAULT '0' COMMENT 'Порядковый номер. Уникален в рамках родителя',
                  `parent` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Идентификатор родителя',
                  `parent_cnt` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Уровень вложенности (кол-во родителей)',
                  `proto` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Идентификатор прототипа',
                  `proto_cnt` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Уровень наследования (кол-во прототипов)',
                  `value` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Строковое значение',
                  `valuef` DOUBLE NOT NULL DEFAULT '0' COMMENT 'Числовое значение для правильной сортировки и поиска',
                  `is_file` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Значение - файл или нет?',
                  `is_history` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'В истории или нет?',
                  `is_delete` INT(10) NOT NULL DEFAULT '0' COMMENT 'Удален или нет? Значение зависит от родителя',
                  `is_hidden` INT(10) NOT NULL DEFAULT '0' COMMENT 'Скрыт или нет? Значение зависит от родителя',
                  `is_link` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Используетя как ссылка или нет? Для оптимизации указывается идентификатор объекта, на которого ссылается ',
                  `is_default_value` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Используется значение прототипа или оно переопределено? Если больше 0, то определяет идентификатор прототипа, чьё значение наследуется',
                  `is_default_class` INT(10) UNSIGNED NOT NULL DEFAULT '4294967295' COMMENT 'Используется класс прототипа или свой?',
                  `update_step` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Шаг обновления. Если не 0, то обновление не закончено',
                  `update_time` INT(11) NOT NULL DEFAULT '0' COMMENT 'Время последней проверки изменений',
                  `diff` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Код обнаруженных изменений. Коды Entity::DIFF_*',
                  PRIMARY KEY (`id`,`owner`,`lang`,`date`),
                  KEY `property` (`parent`,`order`,`name`,`value`,`valuef`),
                  KEY `indexation` (`parent`,`is_history`,`id`)
                ) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='Объекты'
            ");
            $db->exec("
                CREATE TABLE {parents} (
                  `object_id` INT(10) UNSIGNED NOT NULL COMMENT 'Идентификатор объекта',
                  `parent_id` INT(10) UNSIGNED NOT NULL COMMENT 'Идентификатор родителя',
                  `level` INT(10) UNSIGNED NOT NULL COMMENT 'Уровень родителя от корня',
                  `is_delete` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Удалено отношение или нет',
                  PRIMARY KEY (`object_id`,`parent_id`),
                  UNIQUE KEY `children` (`parent_id`,`object_id`)
                ) ENGINE=INNODB DEFAULT CHARSET=utf8
            ");
            $db->exec("
                CREATE TABLE {protos} (
                  `object_id` INT(10) UNSIGNED NOT NULL COMMENT 'Идентификатор объекта',
                  `proto_id` INT(10) UNSIGNED NOT NULL COMMENT 'Идентификатор прототипа',
                  `level` INT(10) UNSIGNED NOT NULL COMMENT 'Уровень прототипа от базового',
                  `is_delete` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Признак, удалено отношение или нет',
                  PRIMARY KEY (`object_id`,`proto_id`),
                  UNIQUE KEY `heirs` (`proto_id`,`object_id`)
                ) ENGINE=INNODB DEFAULT CHARSET=utf8
            ");
        }catch (\PDOException $e){
			// Ошибки подключения к СУБД
			if ($e->getCode() == '1045'){
				$errors->user = 'no_acces';
				$errors->password = 'no_access';
			}else
			if ($e->getCode() == '2002'){
				$errors->host = 'not_found';
                if ($connect['port']!=3306){
                    $errors->port = 'not_found';
                }
			}else{
				$errors->common = $e->getMessage();
			}
			if ($errors->isExist()) throw $errors;
		}
    }
}