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
    boolive\data\Data,
    boolive\functions\F,
    boolive\file\File,
    boolive\errors\Error,
    boolive\events\Events,
    boolive\develop\Trace;

class MySQLStore extends Entity
{
    /** @var \boolive\database\DB */
    public $db;
    /** @var string Ключ хранилища, по которому хранилище выбирается для объектов и создаются короткие URI */
    private $key;
    /** @var array  */
    private $classes;

    private $_local_ids;
    private $_local_ids_change = false;

    /**
     * Конструктор экземпляра хранилища
     * @param string $key Ключ хранилища. Используется для формирования и распознования сокращенных URI
     * @param array $config Параметры подключения к базе данных
     */
    function __construct($key, $config)
    {
        $this->key = $key;
        $this->db = DB::connect($config);
        Events::on('Boolive::deactivate', $this, 'deactivate');
    }

    /**
     * Обработчик системного события deactivate (завершение работы системы)
     */
    function deactivate()
    {
        if ($this->_local_ids_change) Cache::set('mysqlstore/localids', F::toJSON($this->_local_ids, false));
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
        $need_text = array();
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
                    if (isset($row['value_type']) && $row['value_type'] == Entity::VALUE_TEXT){
                        $need_text[$row['is_default_value']][] = &$group_result[$key];
                    }
                }else{
                    if (isset($row['id2'])){
                        if ($this != ($store = Data::getStore($row['uri']))){
                            $group_result[$key] = $store->read(array(
                                'from' => $row['uri'],
                                'select' => array('self'),
                                'depth'=> array(0,0)
                                )
                            );
                        }else{
                            $group_result[$key] = array(
                                'uri'=>$row['uri'],
                                'class_name' => '\\boolive\\data\\Entity',
                            );
                        }
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
                    if (isset($row['value_type']) && $row['value_type'] == Entity::VALUE_TEXT){
                        $need_text[$row['is_default_value']][] = &$obj;
                    }
                    unset($obj);
                }
            }
            $row = $q->fetch(DB::FETCH_ASSOC);
        }
        // Выборка текстов
        if (!empty($need_text)){
            $q = $this->db->query('SELECT id, value FROM {text} WHERE id IN ('.implode(',', array_keys($need_text)).')');
            while ($row = $q->fetch(DB::FETCH_ASSOC)){
                $cnt = sizeof($need_text[$row['id']]);
                for ($i=0; $i<$cnt; $i++) $need_text[$row['id']][$i]['value'] = $row['value'];
            }
        }

        // Формирование дерева результата (найденные объекты добавляются к найденным родителям)
        if (isset($tree_list)){
            foreach ($tree_list as $key => $tree){
                // Ручная сортиорвка по order
                if (!($cond['depth'][0] == 1 && $cond['depth'][1] == 1) && empty($cond['select'][1]) && empty($cond['limit']) &&
                                !empty($cond['order']) && sizeof($cond['order'])== 1 && $cond['order'][0][0] == 'order'){
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
                    $obj = array('class_name' => '\\boolive\\data\\Entity');
                    $uri = ($key===0)? $cond['from'] : $key;
                    if (!Data::isShortUri($uri)){
                        $names = F::splitRight('/', $uri, true);
                        $obj['name'] = $names[1];
                        $obj['uri'] = $uri;
                    }
                    $group_result[$key] = $obj;
                }
            }
        }
        return is_array($cond['from'])? $group_result : reset($group_result);
    }

    /**
     * Сохранение объекта
     * @param \boolive\data\Entity $entity Сохраняемый объект
     * @param bool $access Признак, проверять доступ или нет?
     * @throws \boolive\errors\Error Ошибки в сохраняемом объекте
     * @throws
     * @throws \Exception Системные ошибки
     */
    function write($entity, $access)
    {
        if ($access && IS_INSTALL && !$entity->isAccessible('write')){
            ///$error = new Error('Запрещенное действие над объектом', $entity->uri());
            $entity->errors()->access->write = 'Нет доступа на запись';
            //throw $this->errors();
        }else
        if ($entity->check(/*$error*/)){
            try{
                // Атрибуты отфильтрованы, так как нет ошибок
                $attr = $entity->_attribs;
                // Идентификатор объекта
                // Родитель и урвень вложенности
                $attr['parent'] = isset($attr['parent']) ? $this->localId($attr['parent']) : 0;
                $attr['parent_cnt'] = $entity->parentCount();
                // Прототип и уровень наследования
                $attr['proto'] = isset($attr['proto']) ? $this->localId($attr['proto']) : 0;
                $attr['proto_cnt'] = $entity->protoCount();
                // Автор
                $attr['author'] = isset($attr['author']) ? $this->localId($attr['author']) : (IS_INSTALL ? $this->localId(Auth::getUser()->key()): 0);
                // Числовое значение
                $attr['valuef'] = floatval($attr['value']);
                // Переопределено ли значение и кем
                $attr['is_default_value'] = (!is_null($attr['is_default_value']) && $attr['is_default_value'] != Entity::ENTITY_ID)? $this->localId($attr['is_default_value']) : $attr['is_default_value'];
                // Чей класс
                $attr['is_default_class'] = (strval($attr['is_default_class']) !== '0' && $attr['is_default_class'] != Entity::ENTITY_ID)? $this->localId($attr['is_default_class']) : $attr['is_default_class'];
                // Ссылка
                $attr['is_link'] = (strval($attr['is_link']) !== '0' && $attr['is_link'] != Entity::ENTITY_ID)? $this->localId($attr['is_link']) : $attr['is_link'];
                // Если движок не установлен, то определение времени обновления
                if (!IS_INSTALL) $attr['update_time'] = time();
                // Тип по умолчанию
                if ($attr['value_type'] == Entity::VALUE_AUTO) $attr['value_type'] = Entity::VALUE_SIMPLE;
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
                }else{
                    $attr['uri'] = $entity->uri(true);
                }

                // Локальный идентификатор объекта
                if (empty($attr['id']) || $attr['id'] == Entity::ENTITY_ID){
                    $attr['id'] = $this->localId($attr['uri'], true, $new_id);
                }else{
                    $attr['id'] = $this->localId($attr['id'], true, $new_id);
                }

                // Своё значение
                if (is_null($attr['is_default_value'])){
                    $attr['is_default_value'] = $attr['id'];
                }

                // Если больше 255, то тип текстовый
                $value_src = $attr['value'];
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
                // Текущая запись
                if (!$new_id){
                    $q = $this->db->prepare('SELECT {objects}.*, {ids}.uri FROM {objects}, {ids} WHERE {ids}.id=? AND {objects}.id={ids}.id LIMIT 0,1');
                    $q->execute(array($attr['id']));
                    $current = $q->fetch(DB::FETCH_ASSOC);
                    unset($q);
                }else{
                    $attr['date'] = time();
                }
                // Проверка доступов
                if ($access && IS_INSTALL){
                    //$error = new Error('Запрещенное действие над объектом', $entity->uri());
                    if ((empty($current) || $current['parent']!=$attr['parent']) && !$entity->isAccessible('write/change/add_child')){
                        $entity->errors()->access->{'write/change/add_child'} = 'Нет доступа на добавление подчиненных';
                    }else
                    if ((empty($current) || $current['proto']!=$attr['proto']) && !$entity->isAccessible('write/create')){
                        $entity->errors()->access->{'write/create'} = 'Нет доступа на использование выбранного прототипа (создания объекта)';
                    }else
                    if (!empty($current)){
                        if ($current['is_hidden'] != $attr['is_hidden'] && !$entity->isAccessible('write/change/is_hidden')){
                            $entity->errors()->access->{'write/change/is_hidden'} = 'Нет доступа на смену признака "скрытый"';
                        }else
                        if ($current['is_draft'] != $attr['is_draft'] && !$entity->isAccessible('write/change/is_draft')){
                            $entity->errors()->access->{'write/change/is_draft'} = 'Нет доступа на смену признака "черновик"';
                        }else
                        if ($current['is_link'] != $attr['is_link'] && !$entity->isAccessible('write/change/is_link')){
                            $entity->errors()->access->{'write/change/is_link'} = 'Нет доступа на смену признака "ссылка"';
                        }else
                        if ($current['is_relative'] != $attr['is_relative'] && !$entity->isAccessible('write/change/proto')){
                            $entity->errors()->access->{'write/change/proto'} = 'Нет доступа на смену признака "относительный прототип"';
                        }else
                        if (($current['value'] != $attr['value'] ||
                             $current['value_type'] != $attr['value_type'] ||
                             $current['is_default_value'] != $attr['is_default_value'] ||
                             !empty($attr['file'])) &&
                             !$entity->isAccessible('write/change/value')){
                            $entity->errors()->access->{'write/change/value'} = 'Нет доступа на изменение значения';
                        }else
                        if ($current['name'] != $attr['name'] && !$entity->isAccessible('write/change/name')){
                            $entity->errors()->access->{'write/change/name'} = 'Нет доступа на смену имени';
                        }else
                        if ($current['parent'] != $attr['parent'] && !$entity->isAccessible('write/change/parent')){
                            $entity->errors()->access->{'write/change/pare'} = 'Нет доступа на смену родителя (перемещения)';
                        }else
                        if ($current['proto'] != $attr['proto'] && !$entity->isAccessible('write/change/proto')){
                            $entity->errors()->access->{'write/change/proto'} = 'Нет доступа на смену прототипа';
                        }else
                        if ($current['author'] != $attr['author'] && !$entity->isAccessible('write/change/author')){
                            $entity->errors()->access->{'write/change/author'} = 'Нет доступа на смену авторства';
                        }else
                        if ($current['is_default_class'] != $attr['is_default_class'] && !$entity->isAccessible('write/change/is_default_class')){
                            $entity->errors()->access->{'write/change/is_default_class'} = 'Нет доступа на смену признака "своя логика"';
                        }else
                        if ($current['is_mandatory'] != $attr['is_mandatory'] && !$entity->isAccessible('write/change/is_mandatory')){
                            $entity->errors()->access->{'write/change/is_mandatory'} = 'Нет доступа на смену признака "обязательный"';
                        }else
                        if ($current['is_property'] != $attr['is_property'] && !$entity->isAccessible('write/change/is_property')){
                            $entity->errors()->access->{'write/change/is_property'} = 'Нет доступа на смену признака "свойство"';
                        }
    //                    else
    //                    if ($current['order'] != $attr['order'] && ($p = $entity->parent()) && !$p->isAccessible('order')){
    //                        $entity->errors()->access->order = 'Нет доступа на упорядочивание подчиненных';
    //                    }
                    }
                    if ($entity->errors()->isExist()) return false;
                }

                $this->db->beginTransaction();
                // Если новое имя или родитель, то обновить свой URI и URI подчиненных
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
                        // Обновление отношений
                        $this->makeParents($attr['id'], $attr['parent'], $dl, true);
                    }
                    if (!empty($uri) && is_dir(DIR_SERVER.'site'.$uri)){
                        // Переименование/перемещение папки объекта
                        $dir = DIR_SERVER.'site'.$uri_new;
                        File::rename(DIR_SERVER.'site'.$uri, $dir);
                        if ($current['name'] !== $attr['name']){
                            // Переименование файла, если он есть
                            if ($current['value_type'] == Entity::VALUE_FILE && $current['is_default_value'] == $current['id']){
                                $attr['value'] = File::changeName($current['value'], $attr['name']);
                                File::rename($dir.'/'.$current['value'], $dir.'/'.$current['value']);
                            }
                            // Переименование файла класса
                            File::rename($dir.'/'.$current['name'].'.php', $dir.'/'.$attr['name'].'.php');
                            // Переименование .info файла
                            File::rename($dir.'/'.$current['name'].'.info', $dir.'/'.$attr['name'].'.info');
                        }
                    }
                    unset($q);
                }
                // Уникальность order, если задано значение и записываемый объект не в истории
                // Если запись в историю, то вычисляем только если не указан order
                if ($attr['order']!= Entity::MAX_ORDER && (!isset($current) || $current['order']!=$attr['order'])){
                    // Проверка, занят или нет новый order
                    $q = $this->db->prepare('SELECT 1 FROM {objects} WHERE `parent`=? AND `order`=?');
                    $q->execute(array($attr['parent'], $attr['order']));
                    if ($q->fetch()){
                        // Сдвиг order существующих записей, чтоб освободить значение для новой
                        $q = $this->db->prepare('
                            UPDATE {objects} SET `order` = `order`+1
                            WHERE `parent`=? AND `order`>=?'
                        );
                        $q->execute(array($attr['parent'], $attr['order']));
                    }
                    unset($q);
                }else
                // Новое максимальное значение для order, если объект новый или явно указано order=null
                if (!$entity->isExist() || /*(array_key_exists('order', $attr) && is_null($attr['order']))*/ $attr['order']==self::MAX_ORDER){
                    // Порядковое значение вычисляется от максимального существующего
                    $q = $this->db->prepare('SELECT MAX(`order`) m FROM {objects} WHERE parent=?');
                    $q->execute(array($attr['parent']));
                    if ($row = $q->fetch(DB::FETCH_ASSOC)){
                        $attr['order'] = $row['m']+1;
                    }
                    unset($q);
                }else{
                    if (isset($current['order'])) $attr['order'] = $current['order'];
                }
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
                // Загрузка/обновление класса
                if (isset($attr['class'])){
                    $path = $entity->dir(true).($attr['name']===''?'site':$attr['name']).'.php';
                    if (isset($attr['class']['content'])){
                        File::create($attr['class']['content'], $path);
                    }else{
                        if ($attr['class']['tmp_name']!=$path){
                            if (!File::upload($attr['class']['tmp_name'], $path)){
                                // @todo Проверить безопасность.
                                // Копирование, если объект-файл создаётся из уже имеющихся на сервере файлов, например при импорте каталога
                                File::copy($attr['class']['tmp_name'], $path);
                            }
                        }
                    }
                    unset($attr['class']);
                }
                $attr_names = array('id', 'name', 'order', 'date', 'parent', 'proto', 'value', 'valuef', 'value_type', 'author',
                        'is_draft', 'is_hidden', 'is_link', 'is_mandatory', 'is_property', 'is_relative', 'is_default_value', 'is_default_class',
                        'is_completed', 'proto_cnt', 'parent_cnt');
                $cnt = sizeof($attr_names);
                // Запись объекта (создание или обновление при наличии)
                // Объект идентифицируется по id
                if (empty($current)){
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
                        UPDATE {objects} SET `'.implode('`=?, `', $attr_names).'`=? WHERE id = ?
                    ');
                    $i = 0;
                    foreach ($attr_names as $name){
                        $value = $attr[$name];
                        $i++;
                        $type = is_int($value)? DB::PARAM_INT : (is_bool($value) ? DB::PARAM_BOOL : (is_null($value)? DB::PARAM_NULL : DB::PARAM_STR));
                        $q->bindValue($i, $value, $type);
                    }
                    $q->bindValue(++$i, $attr['id']);
                    $q->execute();
                }
                $is_write = $q->rowCount()>0;
                $this->db->commit();
                $this->db->beginTransaction();

                // Текстовые значения
                if ($attr['value_type'] == Entity::VALUE_TEXT && $attr['is_default_value'] == $attr['id']){
                    $q = $this->db->prepare('
                        INSERT INTO {text} (`id`, `value`)
                        VALUES (:id, :value)
                        ON DUPLICATE KEY UPDATE `value` = :value
                    ');
                    $q->execute(array(':id'=>$attr['id'], ':value'=>$value_src));
                }

                if (!$new_id && empty($current)){
                    // Если объект еще не был создан, но его уже прототипировали другие
                    // Для этого был создан только идентификатор объекта, а записи в objects, parents, protos нет
                    // Тогда имитируем обновление объекта, чтобы обновить его отношения со всеми наследниками
                    $current = array(
                        'id'		   => $attr['id'],
                        'proto'        => 0,
                        'proto_cnt'    => 0,
                        'value'	 	   => '',
                        'value_type'   => Entity::VALUE_SIMPLE,
                        'is_draft'	   => 0,
                        'is_hidden'	   => 0,
                        'is_link'      => 0,
                        'is_default_value' => $attr['id'],
                        'is_default_class' => Entity::ENTITY_ID,
                    );
                    $incomplete = true;
                }else{
                    $incomplete = false;
                }
                // Создание отношений если объект новый
                if (!$entity->isExist()){
                    $this->makeParents($attr['id'], $attr['parent'], 0, false);
                    $this->makeProtos($attr['id'], $attr['proto'], 0, false, $incomplete, $attr['proto']?$entity->proto()->uri():null);
                }
                // Обновить дату изменения у родителей
                if ($is_write){
                    $this->updateDate($attr['id'], $attr['date']);
                }

                if (!empty($current)){
                    // Обновление наследников
                    $dp = ($attr['proto_cnt'] - $current['proto_cnt']);
                    if (!$incomplete && $current['proto'] != $attr['proto']){
                        $this->makeProtos($attr['id'], $attr['proto'], $dp, true, $incomplete, $attr['proto']?$entity->proto()->uri():null);
                    }
                    // Обновление значения, типа значения, признака наследования значения, класса и кол-во прототипов у наследников
                    // если что-то из этого изменилось у объекта
                    if ($incomplete || $current['value']!=$attr['value'] || $current['value_type']!=$attr['value_type'] ||
                        $current['is_default_class']!=$attr['is_default_class'] || ($current['proto']!=$attr['proto']) || $dp!=0){
                        // id прототипа, значание которого берётся по умолчанию для объекта
                        $p = $attr['is_default_value']/* ? $attr['is_default_value'] : $attr['id']*/;
                        $u = $this->db->prepare('
                            UPDATE {objects}, {protos} SET
                                `value` = IF(is_default_value=:vproto, :value, value),
                                `valuef` = IF(is_default_value=:vproto, :valuef, valuef),
                                `value_type` = IF(is_default_value=:vproto, :value_type, value_type),
                                `is_default_value` = IF((is_default_value=:vproto  || is_default_value=:max_id), :proto, is_default_value),
                                `is_default_class` = IF((is_default_class=:cclass AND ((is_link>0)=:is_link)), :cproto, is_default_class),
                                `proto_cnt` = `proto_cnt`+:dp
                            WHERE {protos}.proto_id = :obj AND {protos}.object_id = {objects}.id AND {protos}.is_delete=0
                              AND {protos}.proto_id != {protos}.object_id
                        ');
                        $u->execute(array(
                            ':value' => $attr['value'],
                            ':valuef' => $attr['valuef'],
                            ':value_type' => $attr['value_type'],
                            ':vproto' => $current['is_default_value']/* ? $current['is_default_value'] : $current['id']*/,
                            ':cclass' => $current['is_default_class'] ? $current['is_default_class'] : $current['id'],
                            ':cproto' => $attr['is_default_class'] ? $attr['is_default_class'] : $attr['id'],
                            ':proto' => $p,
                            ':is_link' => $attr['is_link'] > 0 ? 1: 0,
                            ':dp' => $dp,
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
                $entity->_attribs['value'] = $value_src;
                $entity->_attribs['value_type'] = $attr['value_type'];
                $entity->_attribs['is_exist'] = true;
                $entity->_attribs['order'] = $attr['order'];
                $entity->_changed = false;
                $entity->_autoname = false;
                if ($entity->_attribs['uri'] != $curr_uri){
                    $entity->updateURI();
                }
                $this->db->commit();

                $this->afterWrite($attr, empty($current)?array():$current);
                return true;
            }catch (\Exception $e){
                $this->db->rollBack();
//                $q = $this->db->query('SHOW ENGINE INNODB STATUS');
//                trace($q->fetchAll(DB::FETCH_ASSOC));
                trace($e);
            }
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
        // Проверка доступа на уничтожение объекта и его подчиненных
        if ($access && IS_INSTALL && ($acond = Auth::getUser()->getAccessCond('destroy', $entity))){
            $not_access = $this->read(Data::normalizeCond(array(
                    'select' => array('exists', 'children'),
                    'from' => $entity->id(),
                    'depth' => array(0, 'max'),
                    'where' => array('not', $acond),
                    'access' => false
                ),array(), true), false
            );
            if ($not_access){
                //$error = new Error('Запрещенное действие над объектом', $entity->uri());
                $entity->errors()->access->delete = 'Нет доступа на уничтожение объекта или его подчиненных';
                return false;
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
                //$error = new Error('Недопустимое действие над объектом', $entity->uri());
                $entity->errors()->integrity->add(new Error(array('Уничтожение невозможно. Объект используется в качесвте прототипа для других объектов (%s)', $uris),'heirs-exists'));
                return false;
            }
        }
        // Обновить дату изменения у родителей
        $this->updateDate($id, time());
        // Удалить объект и его подчиненных
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
        // Удаление неиспользуемых текстов
        $this->db->exec('
            DELETE `text` FROM `text`
            LEFT JOIN objects ON text.id = objects.is_default_value
            WHERE objects.id IS NULL
        ');
        // Удалении директории со всеми файлами
        File::clearDir($entity->dir(true), true);
        Cache::delete('mysqlstore/localids');
        return $q->rowCount() > 0;
    }

    /**
     * Дополнение объекта
     * @param \boolive\data\Entity $entity Сохраняемый объект
     * @param bool $access Признак, проверять доступ или нет?
     * @return bool
     * @throws \boolive\errors\Error Ошибки в сохраняемом объекте
     * @throws \Exception Системные ошибки
     */
    function complete($entity, $access)
    {
        if ($access && IS_INSTALL && !$entity->isAccessible('write')){
            $error = new Error('Запрещенное действие над объектом', $entity->uri());
            $error->access = new Error('Нет доступа на запись', 'write');
            throw $error;
        }
        $entity->isCompleted(true);
        $proto = $entity->proto();
        if ($proto && $proto->isCompleted() && $entity->isLink() == $proto->isLink()){
            // С учётом update_step выбрать $step_size подчиненных прототипа.
            $complete_size = 50;
            $complete_step = 0;
            do{
                $proto_children = $proto->find(array(
                    'select' => array('children'),
                    'where' => array('attr', 'is_mandatory','=',1),
                    'limit' => array($complete_step * $complete_size, $complete_size),
                    'key' => 'uri',
                    'file_content' => 1,
                    'cache' => 2
                ), false, false, false);

                foreach ($proto_children as $proto){
                    /** @var $proto Entity */
                    $c = $entity->{$proto->name()};
                    if (!$c->isExist()){
                        // Если прототип относительный, то проверить наличие свойства в объекте с именем этого прототипа
                        // если есть, то создавать его не надо
                        if (!$proto->isRelative() || !$c->isRelative()){
                            $child = $proto->birth($entity, false);
                            $child->isMandatory($proto->isMandatory());
                            $child->order($proto->order());
                            $child->author($entity->author());
                            $this->write($child, false);
                            // После сохранения, когда получает уникальное имя, меняем прототип, если он должен быть относительным
                            if ($proto->isRelative() && ($p = $proto->proto())){
                                $new_proto = Data::getRelativeProto($child->uri(), $proto->uri(), $p->uri());
                                if ($new_proto!==false){
                                    $new_proto = Data::read(array(
                                        'from' => $new_proto,
                                        'select' => 'self',
                                        'cache' => 0
                                    ));
                                    $child->proto($new_proto);
                                    $child->isRelative(true);
                                    $this->write($child, false);
                                }
                            }
                        }
                    }
                }
                $complete_step++;
            }while($complete_size == count($proto_children));
        }
        $q = $this->db->prepare('UPDATE {objects} SET is_completed = 1 WHERE id = ?');
        $q->execute(array($this->localId($entity->id(), false)));
        return true;
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
            UPDATE {objects}, {parents} SET {objects}.date=? WHERE {parents}.object_id = ? AND {parents}.parent_id = {objects}.id
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
        $joins_link = array();
        $joins_plain = array();
        $joins_text = array();
        $binds2 = array();

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
                $multy_cnt = sizeof($cond['from']);
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
                if (!empty($cond['access']) && IS_INSTALL && ($acond = \boolive\auth\Auth::getUser()->getAccessCond('read'))){
                    $acond = $this->getCondSQL(array('where'=>$acond), true);
                    $result['select'].= ', IF('.$acond['where'].',1,0) is_accessible';
                    $result['joins'].=$acond['joins'];
                    $result['binds'] = array_merge($acond['binds'], $result['binds']);
                }
                $result['select'].= ', u.id `id2`';
                $result['from'] = 'FROM {ids} u LEFT JOIN {objects} obj ON obj.id = u.id ';
                // Идентификация
                // Если from множественный, то формат запроса определяется по первому from!
                $from_info = Data::parseUri($cond['from']);
                if (isset($from_info['id'])){
                    if (empty($from_info['path'])){
                        if ($multy){
                            // Множественный выбор по идентификатору
                            $result['select'].= ', CONCAT("//", u.id) as `from` ';
                            $result['where'].= 'u.id IN ('.rtrim(str_repeat('?,', $multy_cnt),',').')';
                            for ($i=0; $i<$multy_cnt; ++$i){
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
                            $w = '{obj}.parent IN ('.rtrim(str_repeat('?,',$multy_cnt),',').') AND {obj}.name = ?';
                            $name = '';
                            for ($i=0; $i<$multy_cnt; ++$i){
                                $from_info = Data::parseUri($cond['from'][$i]);
                                $result['binds'][] = $from_info['id'];
                                if (!$name) $name = ltrim($from_info['path'],'/');
                            }
                            $result['binds'][] = $name;
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
                        for ($i=0; $i<$multy_cnt; ++$i){
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
                if ($multy) trace_log('MULTY SELECT!! on '.$what.' '.implode(', ',$cond['from']));
                // Дополняем условие контролем доступа
                if (!empty($cond['access']) && IS_INSTALL && ($acond = \boolive\auth\Auth::getUser()->getAccessCond('read', $cond['from']))){
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
//                        if ($multy){
//                            $result['select'].= ', CONCAT("//",t.parent_id) as `from`';
//                            $result['from'].= "\n  JOIN {parents} t ON (t.object_id = obj.id AND t.parent_id IN (".rtrim(str_repeat('?,', $multy_cnt),',').')'.($cond['depth'][0]==1?' AND t.object_id!=t.parent_id':'').' AND t.is_delete=0)';
//                            for ($i=0; $i<$multy_cnt; ++$i){
//                                $binds2[] = array($this->localId($cond['from'][$i], false), DB::PARAM_INT);
//                            }
//                            if ($calc) $result['group'] = ' GROUP BY t.parent_id';
//                        }else{
                            $result['from'].= "\n  JOIN {parents} t ON (t.object_id = obj.id AND t.parent_id = ?".($cond['depth'][0]==1?' AND t.object_id!=t.parent_id':'').' AND t.is_delete=0)';
                            $binds2[] = array($this->localId($cond['from'], false), DB::PARAM_INT);
//                        }
                        // сортировка по порядковому номеру будет выполнена после выборки, чтобы при выборке не использовалась файловая сортировка
                        if ($what == 'tree' && empty($cond['select'][1]) && empty($cond['limit']) &&
                            !empty($cond['order']) && sizeof($cond['order'])== 1 && $cond['order'][0][0] == 'order'){
                            $cond['order'] = false;
                        }
                    }else
                    if ($cond['depth'][0] == 1 && $cond['depth'][1] == 1){
                        // Подчиненные объекты
                        $result['from'] = ' FROM {objects} obj USE INDEX(child) JOIN {ids} u ON (u.id = obj.id)';
//                        if ($multy){
//                            $result['select'].= ', CONCAT("//",obj.parent) as `from`';
//                            $result['where'].= 'obj.parent IN ('.rtrim(str_repeat('?,', $multy_cnt),',').') ';
//                            for ($i=0; $i<$multy_cnt; ++$i){
//                                $result['binds'][] = array($this->localId($cond['from'][$i], false), DB::PARAM_INT);
//                            }
//                            if ($calc) $result['group'] = ' GROUP BY obj.parent';
//                        }else{
                            // Сверка parent
                            $result['where'].= 'obj.parent = ? ';
                            $result['binds'][] = array($this->localId($cond['from'], false), DB::PARAM_INT);
//                        }
                        // Оптимизация сортировки по атрибуту order
                        if (!empty($cond['order']) && sizeof($cond['order'])== 1 && $cond['order'][0][0] == 'order' && strtoupper($cond['order'][0][1])=='ASC'){
                            $cond['order'] = false;
                        }
                    }else{
                        // Поиск по ветке с ограниченной глубиной
                        $result['from'] = ' FROM {objects} obj JOIN {ids} u ON (u.id = obj.id)';
//                        if ($multy){
//                            $result['select'].= ', CONCAT("//",f.parent_id) as `from`';
//                            $w = '';
//                            for ($i=0; $i<$multy_cnt; ++$i){
//                                if (!empty($w)) $w.=' OR ';
//                                $w.= "(f.object_id = obj.id AND f.parent_id = ? AND f.level>=? AND f.level<=?)";
//                                $binds2[] = array($this->localId($cond['from'][$i], false), DB::PARAM_INT);
//                                $binds2[] = array($cond['depth'][0], DB::PARAM_INT);
//                                $binds2[] = array($cond['depth'][1], DB::PARAM_INT);
//                            }
//                            $result['from'].= "\n  JOIN {parents} f ON ((".$w.') AND f.is_delete=0)';
//                            if ($calc) $result['group'] = ' GROUP BY f.parent_id';
//                        }else{
                            $result['from'].= "\n  JOIN {parents} f ON (f.object_id = obj.id AND f.parent_id = ? AND f.level>=? AND f.level<=? AND f.is_delete=0)";
                            $binds2[] = array($this->localId($cond['from'], false), DB::PARAM_INT);
                            $binds2[] = array($cond['depth'][0], DB::PARAM_INT);
                            $binds2[] = array($cond['depth'][1], DB::PARAM_INT);
                            // сортировка по порядковому номеру будет выполнена после выборки, чтобы при выборке не использовалась файловая сортировка
                            if ($what == 'tree' && empty($cond['select'][1]) && empty($cond['limit']) &&
                                !empty($cond['order']) && sizeof($cond['order'])== 1 && $cond['order'][0][0] == 'order'){
                                $cond['order'] = false;
                            }
//                        }
                    }
                }else
                if ($what == 'parents'){
                    // Выбор родителей
                    // Выбор всех родителей from
                    if ($cond['depth'][1] == Entity::MAX_DEPTH && $cond['depth'][0]<=1){
                        // Поиск по всей ветке
                        $result['from'] = ' FROM {objects} obj JOIN {ids} u ON (u.id = obj.id)';
//                        if ($multy){
//                            $result['select'].= ', CONCAT("//",t.object_id) as `from`';
//                            $result['from'].= "\n  JOIN {parents} t ON (t.parent_id = obj.id AND t.object_id IN (".rtrim(str_repeat('?,', $multy_cnt),',').')'.($cond['depth'][0]==1?' AND t.object_id!=t.parent_id':'').' AND t.is_delete=0)';
//                            for ($i=0; $i<$multy_cnt; ++$i){
//                                $result['binds'][] = array($this->localId($cond['from'][$i], false), DB::PARAM_STR);
//                            }
//                            if ($calc) $result['group'] = ' GROUP BY t.object_id';
//                        }else{
                            $result['from'].= "\n  JOIN {parents} t ON (t.parent_id = obj.id AND t.object_id = ?".($cond['depth'][0]==1?' AND t.object_id!=t.parent_id':'').' AND t.is_delete=0)';
                            $binds2[] = array($this->localId($cond['from'], false), DB::PARAM_INT);
//                        }
                    }else{
                        // Поиск ограниченной глубиной (кол-ва родителей)
                        $result['from'] = ' FROM {objects} obj JOIN {ids} u ON (u.id = obj.id)';
//                        if ($multy){
//                            $result['select'].= ', CONCAT("//",f.object_id) as `from`';
//                            $w = '';
//                            for ($i=0; $i<$multy_cnt; ++$i){
//                                if (!empty($w)) $w.=' OR ';
//                                $w.= "(f.parent_id = obj.id AND f.object_id = ? AND f.level>=? AND f.level<=?)";
//                                $binds2[] = array($this->localId($cond['from'][$i], false), DB::PARAM_INT);
//                                $binds2[] = array($cond['depth'][0], DB::PARAM_INT);
//                                $binds2[] = array($cond['depth'][1], DB::PARAM_INT);
//                            }
//                            $result['from'].= "\n  JOIN {parents} f ON ((".$w.') AND f.is_delete=0)';
//                            if ($calc) $result['group'] = ' GROUP BY f.object_id';
//                        }else{
                            $result['from'].= "\n  JOIN {parents} f ON (f.parent_id = obj.id AND f.object_id = ? AND f.level>=? AND f.level<=? AND f.is_delete=0)";
                            $binds2[] = array($this->localId($cond['from'], false), DB::PARAM_INT);
                            $binds2[] = array($cond['depth'][0], DB::PARAM_INT);
                            $binds2[] = array($cond['depth'][1], DB::PARAM_INT);
//                        }
                    }
                }else
                if ($what == 'protos'){
                    // Выбор или подсчёт прототипов
                    // Выбор всех прототипов from
                    if ($cond['depth'][1] == Entity::MAX_DEPTH && $cond['depth'][0]<=1){
                        // Поиск по всей ветке
                        $result['from'] = ' FROM {objects} obj JOIN {ids} u ON (u.id = obj.id)';
//                        if ($multy){
//                            $result['select'].= ', CONCAT("//",t.object_id) as `from`';
//                            $result['from'].= "\n  JOIN {protos} t ON (t.proto_id = obj.id AND t.object_id IN (".rtrim(str_repeat('?,', $multy_cnt),',').')'.($cond['depth'][0]==1?' AND t.object_id!=t.proto_id':'').' AND t.is_delete=0)';
//                            for ($i=0; $i<$multy_cnt; ++$i){
//                                $result['binds'][] = array($this->localId($cond['from'][$i], false), DB::PARAM_STR);
//                            }
//                            if ($calc) $result['group'] = ' GROUP BY t.object_id';
//                        }else{
                            $result['from'].= "\n  JOIN {protos} t ON (t.proto_id = obj.id AND t.object_id = ?".($cond['depth'][0]==1?' AND t.object_id!=t.proto_id':'').' AND t.is_delete=0)';
                            $binds2[] = array($this->localId($cond['from'], false), DB::PARAM_INT);
//                        }
                    }else{
                        // Поиск ограниченной глубиной (кол-ва прототипов)
                        $result['from'] = ' FROM {objects} obj JOIN {ids} u ON (u.id = obj.id)';
//                        if ($multy){
//                            $result['select'].= ', CONCAT("//",f.object_id) as `from`';
//                            $w = '';
//                            for ($i=0; $i<$multy_cnt; ++$i){
//                                if (!empty($w)) $w.=' OR ';
//                                $w.= "(f.proto_id = obj.id AND f.object_id = ? AND f.level>=? AND f.level<=?)";
//                                $binds2[] = array($this->localId($cond['from'][$i], false), DB::PARAM_INT);
//                                $binds2[] = array($cond['depth'][0], DB::PARAM_INT);
//                                $binds2[] = array($cond['depth'][1], DB::PARAM_INT);
//                            }
//                            $result['from'].= "\n  JOIN {protos} f ON ((".$w.') AND f.is_delete=0)';
//                            if ($calc) $result['group'] = ' GROUP BY f.object_id';
//                        }else{
                            $result['from'].= "\n  JOIN {protos} f ON (f.proto_id = obj.id AND f.object_id = ? AND f.level>=? AND f.level<=? AND f.is_delete=0)";
                            $binds2[] = array($this->localId($cond['from'], false), DB::PARAM_INT);
                            $binds2[] = array($cond['depth'][0], DB::PARAM_INT);
                            $binds2[] = array($cond['depth'][1], DB::PARAM_INT);
//                        }
                    }
                }else
                if ($what == 'heirs'){
                    // Выбор наследников
                    // Выбор списка записей из from
                    if ($cond['depth'][1] == Entity::MAX_DEPTH && $cond['depth'][0]<=1){
                        // Поиск по всей ветке
                        $result['from'] = ' FROM {objects} obj JOIN {ids} u ON (u.id = obj.id)';
//                        if ($multy){
//                            $result['select'].= ', CONCAT("//",t.proto_id) as `from`';
//                            $result['from'].= "\n  JOIN {protos} t ON (t.object_id = obj.id AND t.proto_id IN (".rtrim(str_repeat('?,', $multy_cnt),',').')'.($cond['depth'][0]==1?' AND t.object_id!=t.proto_id':'').' AND t.is_delete=0)';
//                            for ($i=0; $i<$multy_cnt; $i++){
//                                $result['binds'][] = array($this->localId($cond['from'][$i], false), DB::PARAM_STR);
//                            }
//                            if ($calc) $result['group'] = ' GROUP BY t.proto_id';
//                        }else{
                            $result['from'].= "\n  JOIN {protos} t ON (t.object_id = obj.id AND t.proto_id = ?".($cond['depth'][0]==1?' AND t.object_id!=t.proto_id':'').' AND t.is_delete=0)';
                            $binds2[] = array($this->localId($cond['from'], false), DB::PARAM_INT);
//                        }
                    }else
                    if ($cond['depth'][0] == 1 && $cond['depth'][1] == 1){
                        // Прямые наследники
                        $result['from'] = ' FROM {objects} obj JOIN {ids} u ON (u.id = obj.id)';
//                        if ($multy){
//                            $result['select'].= ', CONCAT("//",obj.proto) as `from`';
//                            $result['where'].= 'obj.proto IN ('.rtrim(str_repeat('?,', $multy_cnt),',').') ';
//                            for ($i=0; $i<$multy_cnt; ++$i){
//                                $result['binds'][] = array($this->localId($cond['from'][$i], false), DB::PARAM_STR);
//                            }
//                            if ($calc) $result['group'] = ' GROUP BY obj.proto';
//                        }else{
                            // Сверка proto
                            $result['where'].= "obj.proto = ? ";
                            $result['binds'][] = array($this->localId($cond['from'], false), DB::PARAM_INT);
//                        }
                    }else{
                        // Поиск по ветке наследования с ограниченной глубиной
                        $result['from'] = ' FROM {objects} obj JOIN {ids} u ON (u.id = obj.id)';
//                        if ($multy){
//                            $result['select'].= ', CONCAT("//",f.proto_id) as `from`';
//                            $w = '';
//                            for ($i=0; $i<$multy_cnt; ++$i){
//                                if (!empty($w)) $w.=' OR ';
//                                $w.= "(f.object_id = obj.id AND f.proto_id = ? AND f.level>=? AND f.level<=?)";
//                                $binds2[] = array($this->localId($cond['from'][$i], false), DB::PARAM_INT);
//                                $binds2[] = array($cond['depth'][0], DB::PARAM_INT);
//                                $binds2[] = array($cond['depth'][1], DB::PARAM_INT);
//                            }
//                            $result['from'].= "\n  JOIN {protos} f ON ((".$w.') AND f.is_delete=0)';
//                            if ($calc) $result['group'] = ' GROUP BY f.proto_id';
//                        }else{
                            $result['from'].= "\n  JOIN {protos} f ON (f.object_id = obj.id AND f.proto_id = ? AND f.level>=? AND f.level<=? AND f.is_delete=0)";
                            $binds2[] = array($this->localId($cond['from'], false), DB::PARAM_INT);
                            $binds2[] = array($cond['depth'][0], DB::PARAM_INT);
                            $binds2[] = array($cond['depth'][1], DB::PARAM_INT);
//                        }
                    }
                }else{
                    throw new \Exception('Incorrect selection in condition: ("'.$cond['select'][0].'","'.$cond['select'][1].'")');
                }
            }
            // Сортировка
            if (!$calc && !empty($cond['order'])){
                $cnt = sizeof($cond['order']);
                for ($i=0; $i<$cnt; ++$i){
                    if (($ocnt = sizeof($cond['order'][$i])-2)>=0){
                        $jtable = $pretabel = 'obj';
                        if ($ocnt>0){
                            // Сортировка по подчиненным объектами. Требуется слияние таблиц
                            for ($o = 0; $o < $ocnt; ++$o){
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
            $convert = function($cond, $glue = ' AND ', $table = 'obj', $level = 0, &$attr_exists = array()) use (&$store, &$convert, &$result, &$joins, &$joins_link, &$joins_plain, &$joins_text){
                $level++;
                // Нормализация групп условий
                if ($cond[0] == 'any' || $cond[0] == 'all'){
                    $glue = $cond[0] == 'any'?' OR ':' AND ';
                    $cond = $cond[1];
                }else
                if (sizeof($cond)>0 && !is_array($cond[0])){
                    $cond = array($cond);
                    $glue = ' AND ';
                }
                foreach ($cond as $i => $c){
                    if (!is_array($c)) $c = array($c);
                    if (!empty($c)){
                        $c[0] = strtolower($c[0]);
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
                        if ($c[0]=='match'){
                            $alias = uniqid('text');
                            $joins_text[$alias] = array($table);
                            $mode = empty($c[2])?'': ' IN BOOLEAN MODE';
                            if (empty($c[2])){
                                $cond[$i] = '(`'.$alias.'`.id IS NOT NULL)';
                            }else{
                                $cond[$i] = 'MATCH('.$alias.'.value) AGAINST (? '.$mode.')';
                                $result['binds'][] = empty($c[1])?'':strval($c[1]);
                            }
                        }else
                        // Проверка подчиненного
                        if ($c[0]=='child'){
                            $joins[$table.'.'.$c[1]] = array($table, $c[1]);
                            // Если условий на подчиненного нет, то проверяется его наличие
                            if (empty($c[2])){
                                $cond[$i] = '(`'.$table.'.'.$c[1].'`.id IS NOT NULL)';
                            }else{
                                // Условия на подчиненного
                                $cond[$i] = '('.$convert($c[2], ' AND ', $table.'.'.$c[1], $level).')';
                            }
                        }else
                        // Проверка ссылки
                        if ($c[0]=='link'){
                            $alias = uniqid('link');
                            $joins_link[$alias] = array($table);
                            // Если условий на ссылки нет, то проверяется его наличие
                            if (empty($c[1])){
                                $cond[$i] = '(`'.$alias.'`.id IS NOT NULL)';
                            }else{
                                // Условия на ссылку
                                $cond[$i] = '('.$convert($c[1], ' AND ', $alias, $level).')';
                            }
                        }else
                        // Сверка объекта по URI
                        if ($c[0]=='eq'){
                            if (is_array($c[1])){
                                $c = $c[1];
                            }else{
                                unset($c[0]);
                            }
                            if (sizeof($c)>0){
                                $cond[$i] = '`'.$table.'`.`id` IN ('.str_repeat('?,', sizeof($c)-1).'?)';
                                foreach ($c as $j => $key) $c[$j] = $store->localId($key, false);
                                $result['binds'] = array_merge($result['binds'], $c);
                            }else{
                                $cond[$i] = '0';
                            }
                        }else
                        // Условие на наличие родителя или эквивалентности.
                        if ($c[0]=='in'){
                            if (is_array($c[1])){
                                $c = $c[1];
                            }else{
                                unset($c[0]);
                            }
                            if (sizeof($c)>0){
                                $alias = uniqid('in');
                                $cond[$i] = 'EXISTS (SELECT 1 FROM {parents} `'.$alias.'` WHERE `'.$alias.'`.`object_id`=`'.$table.'`.id AND `'.$alias.'`.parent_id IN ('.rtrim(str_repeat('?,', sizeof($c)), ',').') AND is_delete = 0)';
                                foreach ($c as $j => $key) $c[$j] = $store->localId($key, false);
                                $result['binds'] = array_merge($result['binds'], $c);
                            }else{
                                $cond[$i] = '1';
                            }
                        }else
                        // Условие на наличие прототипа или эквивалентности.
                        if ($c[0]=='is'){
                            if (is_array($c[1])){
                                $c = $c[1];
                            }else{
                                unset($c[0]);
                            }
                            if (sizeof($c)>0){
                                $alias = uniqid('is');
                                $cond[$i] = 'EXISTS (SELECT 1 FROM {protos} `'.$alias.'` WHERE `'.$alias.'`.`object_id`=`'.$table.'`.id AND `'.$alias.'`.proto_id IN ('.rtrim(str_repeat('?,', sizeof($c)), ',').') AND is_delete = 0)';
                                foreach ($c as $j => $key) $c[$j] = $store->localId($key, false);
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
                            if (sizeof($c)>0){
                                $of = rtrim(str_repeat('?,', sizeof($c)), ',');
                                $cond[$i] = 'EXISTS (SELECT 1 FROM {parents}, {protos} WHERE {parents}.object_id = {protos}.object_id AND {parents}.object_id=`'.$table.'`.id AND ({parents}.parent_id IN ('.$of.') OR {protos}.proto_id IN ('.$of.')) AND {parents}.is_delete = 0 AND {protos}.is_delete = 0)';
                                foreach ($c as $j => $key) $c[$j] = $store->localId($key, false);
                                $result['binds'] = array_merge($result['binds'], $c, $c);
                            }else{
                                $cond[$i] = '1';
                            }
                        }else
                        // Условие на наличие родителя
                        if ($c[0]=='childof'){
                            if (is_array($c[1])){
                                $c = $c[1];
                            }else{
                                unset($c[0]);
                            }
                            if (sizeof($c)>0){
                                $alias = uniqid('in');
                                $cond[$i] = 'EXISTS (SELECT 1 FROM {parents} `'.$alias.'` WHERE `'.$alias.'`.`object_id`=`'.$table.'`.id AND `'.$alias.'`.parent_id IN ('.rtrim(str_repeat('?,', sizeof($c)), ',').') AND is_delete = 0 AND level>0)';
                                foreach ($c as $j => $key) $c[$j] = $store->localId($key, false);
                                $result['binds'] = array_merge($result['binds'], $c);
                            }else{
                                $cond[$i] = '1';
                            }
                        }else
                        // Условие на наличие прототипа.
                        if ($c[0]=='heirof'){
                            if (is_array($c[1])){
                                $c = $c[1];
                            }else{
                                unset($c[0]);
                            }
                            if (sizeof($c)>0){
                                $alias = uniqid('is');
                                $cond[$i] = 'EXISTS (SELECT 1 FROM {protos} `'.$alias.'` WHERE `'.$alias.'`.`object_id`=`'.$table.'`.id AND `'.$alias.'`.proto_id IN ('.rtrim(str_repeat('?,', sizeof($c)), ',').') AND is_delete = 0 AND level > 0)';
                                foreach ($c as $j => $key) $c[$j] = $store->localId($key, false);
                                $result['binds'] = array_merge($result['binds'], $c);
                            }else{
                                $cond[$i] = '1';
                            }
                        }else
                        // Условие на наличие наследника.
                        if ($c[0]=='heirs'){
                            if (is_array($c[1])){
                                $c = $c[1];
                            }else{
                                unset($c[0]);
                            }
                            if (sizeof($c)>0){
                                $alias = uniqid('heirs');
                                $cond[$i] = 'EXISTS (SELECT 1 FROM {protos} `'.$alias.'` WHERE `'.$alias.'`.`proto_id`=`'.$table.'`.id AND `'.$alias.'`.object_id IN ('.rtrim(str_repeat('?,', sizeof($c)), ',').') AND is_delete = 0)';
                                foreach ($c as $j => $key) $c[$j] = $store->localId($key, false);
                                $result['binds'] = array_merge($result['binds'], $c);
                            }else{
                                $cond[$i] = '1';
                            }
                        }else
                        if ($c[0] == 'access'){
                            if (IS_INSTALL && ($acond = \boolive\auth\Auth::getUser()->getAccessCond($c[1]))){
                                $acond = $store->getCondSQL(array('where'=>$acond), true);

                                $cond[$i] = $acond['where'];
                                $result['joins'].= $acond['joins'];
                                $result['binds'] = array_merge($result['binds'], $acond['binds']);
                            }else{
                                $cond[$i] = '1';
                            }
                        }else
                        // Атрибут
                        /*if ($c[0]=='attr')*/{
                            if ($c[0]=='attr') array_shift($c);
                            if (sizeof($c) < 2){
                                $c[1] = '!=';
                                $c[2] = 0;
                            }
                            // Если атрибут value, то в зависимости от типа значения используется соответсвующая колонка
                            if ($c[0] == 'value'){
                                $c[0] = is_numeric($c[2]) ? 'valuef': 'value';
                            }
                            // sql услвоие
                            if ($c[1]=='eq'){
                                $c[1] = '=';
                            }
                            $cond[$i] = '`'.$table.'`.`'.$c[0].'` '.$c[1];
                            // Учитываем особенность синтаксиса условия IN
                            if (mb_strtolower($c[1]) == 'in'){
                                if (!is_array($c[2])) $c[2] = array($c[2]);
                                if (empty($c[2])){
                                    $cond[$i].='(NULL)';
                                }else{
                                    $cond[$i].='('.str_repeat('?,', sizeof($c[2])-1).'?)';
                                    $result['binds'] = array_merge($result['binds'], $c[2]);
                                }
                            }else{
                                $cond[$i].= '?';
                                $result['binds'][] = $c[2];
                            }
                            if ($c[0] == 'is_draft' || $c[0] == 'diff' || $c[0] == 'is_hidden'){
                                $attr_exists[$c[0]] = true;
                            }
                        //}
                        // Не поддерживаемые условия игнорируем
//                        else{
//                            $cond[$i] = '0';
                        }
                    }else{
                        unset($cond[$i]);
                    }
                }
                // Дополнительные услвоия по умолчанию
                if ($level == 1){
                    $more_cond = array();
                    if (empty($attr_exists['is_draft'])) $more_cond[]  = '`'.$table.'`.is_draft = 0';
                    if (empty($attr_exists['is_hidden'])) $more_cond[]  = '`'.$table.'`.is_hidden = 0';
                    $attr_exists = array('is_hidden' => true, 'is_draft' => true);
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
            $attr_exists = $only_where ? array('is_hidden' => true, 'is_draft' => true) : array();
            // Если услвоия есть, то добавляем их в SQL
            if ($w = $convert($cond['where'], ' AND ', 'obj', 0, $attr_exists)){
                if (empty($result['where'])){
                    $result['where'] = $w;
                }else{
                    $result['where'].= " AND (".$w.')';
                }
            }
        }else{
            if ($cond['select'][0] != 'self'){
                if (!empty($result['where'])) $result['where'].=' AND ';
                $result['where'].= 'obj.is_draft = 0 AND obj.is_hidden = 0';
            }
        }

        // Слияния для условий по подчиненным и сортировке по ним
        unset($joins['obj']);
        foreach ($joins as $alias => $info){
            $result['joins'].= "\n  LEFT JOIN {objects} `".$alias.'` ON (`'.$alias.'`.parent = `'.$info[0].'`.id AND `'.$alias.'`.name = ?)';
            $binds2[] = $info[1];
        }
        foreach ($joins_text as $alias => $info){
            $result['joins'].= "\n  LEFT JOIN {text} `".$alias.'` ON (`'.$alias.'`.id = `'.$info[0].'`.is_default_value AND `'.$info[0].'`.value_type=2)';
        }
        foreach ($joins_link as $alias => $info){
            $result['joins'].= "\n  LEFT JOIN {objects} `".$alias.'` ON (`'.$alias.'`.id = `'.$info[0].'`.is_link)';
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
     * @param Entity $entity Объект, для которого обновляются отношения с родителем
     * @param int $parent Новый родитель объекта
     * @param int $dl Разница между новым и старым уровнем вложенности объекта
     * @param bool $remake Признак, отношения обновлять (при смене родителя) или создавать новые (новый объект)
     */
    private function makeParents($entity, $parent, $dl, $remake = false)
    {
        if ($remake){
            // У подчинённых удалить отношения с родителями, которые есть у $entity
            $q = $this->db->prepare('
                UPDATE {parents} p, (
                    SELECT c.object_id, c.parent_id FROM {parents} p
                    JOIN {parents} c ON c.object_id = p.object_id AND c.object_id!=c.parent_id AND c.parent_id IN (SELECT parent_id FROM {parents} WHERE object_id = :obj AND object_id!=parent_id)
                    WHERE p.parent_id = :obj)p2
                SET p.is_delete = 1
                WHERE p.object_id = p2.object_id AND p.parent_id = p2.parent_id
            ');
            $q->execute(array(':obj'=>$entity));
            if ($parent > 0){
                // Для объекта и всех его подчиненных создать отношения с новыми родителями
                $q = $this->db->prepare('SELECT object_id, `level` FROM {parents} WHERE parent_id = :obj ORDER BY level');
                $make = $this->db->prepare('
                    INSERT {parents} (object_id, parent_id, `level`)
                    SELECT :obj, parent_id, `level`+1+:l FROM {parents}
                    WHERE object_id = :parent AND is_delete = 0
                    UNION SELECT :obj,:obj,0
                    ON DUPLICATE KEY UPDATE `level` = VALUES(level), is_delete = 0
                ');
                $q->execute(array(':obj'=>$entity));
                while ($row = $q->fetch(DB::FETCH_ASSOC)){
                    $make->execute(array(':obj'=>$row['object_id'], ':parent'=>$parent, ':l'=>$row['level']));
                }
            }
        }else
        if ($parent >= 0){
            $make = $this->db->prepare('
                INSERT {parents} (object_id, parent_id, `level`)
                SELECT :obj, parent_id, `level`+1 FROM {parents}
                WHERE object_id = :parent AND is_delete = 0
                UNION SELECT :obj,:obj,0
                ON DUPLICATE KEY UPDATE `level` = VALUES(level), is_delete = 0
            ');
            $make->execute(array(':obj' => $entity, ':parent'=>$parent));
        }
    }

    /**
     * Создание или обновление отношений с прототипами объекта
     * @param Entity $entity Объект, для которого обновляются отношения с прототипами
     * @param int $proto Новый прототип объекта
     * @param int $dl Разница между новым и старым уровнем вложенности объекта среди прототипов
     * @param bool $remake Признак, отношения обновлять (при смене прототипа) или создавать новые (новый объект)
     * @param bool $incomplete Признак, объект небыл сохранен, но его уже прототипировали?
     * @param null $proto_uri URI прототипа. Необходим, если прототип внешний
     */
    private function makeProtos($entity, $proto, $dl, $remake = false, $incomplete = false, $proto_uri = null)
    {
        if (isset($proto_uri) && Data::getStore($proto_uri)!=$this){
            $protos = Data::read(array(
                'from' => $proto_uri,
                'select' => array('protos'),
                'depth' => array(0, Entity::MAX_DEPTH),
                'order' => array('proto_cnt', 'DESC')
            ));
            foreach ($protos as $i => $p){
                $protos[$i] = $this->localId($p->uri());
            }
            $remote = true;
        }else{
            $remote = false;
        }
        if ($remake){
            // У наследников удалить отношения с прототипами, которые есть у $entity
            $q = $this->db->prepare('
                UPDATE {protos} p, (
                    SELECT c.object_id, c.proto_id FROM {protos} p
                    JOIN {protos} c ON c.object_id = p.object_id AND c.proto_id!=:obj AND c.object_id!=c.proto_id AND c.proto_id IN (SELECT proto_id FROM {protos} WHERE object_id = :obj  AND object_id!=proto_id)
                    WHERE p.proto_id = :obj)p2
                SET p.is_delete = 1
                WHERE p.object_id = p2.object_id AND p.proto_id = p2.proto_id
            ');
            $q->execute(array(':obj'=>$entity));
            // Для объекта и всех его наследников создать отношения с новыми прототипом
            if ($proto >= 0){
                if ($remote){
                    // Отношения с внешними прототипами для объекта и его наследников
                    $values = '';
                    foreach ($protos as $i => $p){
                        $values.='(:obj, '.$p.', '.($i+1).'+:level),';
                    }
                    $q = $this->db->prepare('
                        INSERT {protos} (object_id, proto_id, `level`)
                        VALUES '.rtrim($values,',').'
                        ON DUPLICATE KEY UPDATE `level` = VALUES(level), is_delete = 0
                    ');
                    $h = $this->db->prepare('SELECT object_id as `obj`, `level` FROM protos WHERE proto_id = :obj and is_delete=0');
                    $h->execute(array(':obj' => $entity));
                    while ($heir = $h->fetch(DB::FETCH_ASSOC)){
                        $q->execute($heir);
                    }
                }else{
                    $q = $this->db->prepare('SELECT object_id, `level` FROM {protos} WHERE proto_id = :obj AND is_delete = 0 ORDER BY `level`');
                    $make = $this->db->prepare('
                        INSERT {protos} (object_id, proto_id, `level`)
                        SELECT :obj, proto_id, `level`+1+:l FROM {protos}
                        WHERE object_id = :proto AND is_delete = 0
                        UNION SELECT :obj,:obj,0
                        ON DUPLICATE KEY UPDATE `level` = VALUES(level), is_delete = 0
                    ');
                    $q->execute(array(':obj'=>$entity));
                    while ($row = $q->fetch(DB::FETCH_ASSOC)){
                        $make->execute(array(':obj'=>$row['object_id'], ':proto'=>$proto, ':l'=>$row['level']));
                    }
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
                $make->execute(array(':obj' => $entity));
            }else{
                $check = $this->db->prepare('SELECT 1 FROM {protos} WHERE object_id=? and is_delete=0 LIMIT 0,1');
                $check->execute(array($proto));
                if ($check->fetch()){
                    // Если прототип в таблице protos
                    $make = $this->db->prepare('
                        INSERT {protos} (object_id, proto_id, `level`)
                        SELECT :obj, proto_id, `level`+1 FROM {protos}
                        WHERE object_id = :proto AND is_delete = 0
                        UNION SELECT :obj,:obj,0
                        ON DUPLICATE KEY UPDATE `level` = VALUES(level), is_delete = 0
                    ');
                    $make->execute(array(':obj' => $entity, ':proto'=>$proto));
                    if ($incomplete){
                        // Объект уже кем-то прототипирован, поэтому для них добавляются все прототипы текущего объекта
                        $q = $this->db->prepare('SELECT object_id, `level` FROM {protos} WHERE proto_id = :obj AND is_delete = 0 ORDER BY `level`');
                        $make = $this->db->prepare('
                            INSERT {protos} (object_id, proto_id, `level`)
                            SELECT :obj, proto_id, `level`+1+:l FROM {protos}
                            WHERE object_id = :proto AND is_delete = 0
                            UNION SELECT :obj,:obj,0
                            ON DUPLICATE KEY UPDATE `level` = VALUES(level), is_delete = 0
                        ');
                        $q->execute(array(':obj'=>$entity));
                        while ($row = $q->fetch(DB::FETCH_ASSOC)){
                            $make->execute(array(':obj'=>$row['object_id'], ':proto'=>$proto, ':l'=>$row['level']));
                        }
                    }
                }else{
                    if ($remote){
                        // Если прототип внешний
                        // Если объект уже прототипирован (хотя не был создан)
                        if ($incomplete){
                            // Отношения с прототипами для уже существующих наследников объекта
                            $values = '';
                            foreach ($protos as $i => $p){
                                $values.='(:obj, '.$p.', '.($i+1).'+:level),';
                            }
                            $q = $this->db->prepare('
                                INSERT {protos} (object_id, proto_id, `level`)
                                VALUES '.rtrim($values,',').'
                                ON DUPLICATE KEY UPDATE `level` = VALUES(level), is_delete = 0
                            ');
                            // Объект уже кем-то прототипирован, поэтому и для них добавляется отношения с proto
                            // У наследников объекта выбирается запись-отношение с $entity и она же вставляется с новым proto_id и увеличенным level
                            $h = $this->db->prepare('SELECT object_id as `obj`, `level` FROM protos WHERE proto_id = :obj and is_delete=0');
                            $h->execute(array(':obj' => $entity));
                            while ($heir = $h->fetch(DB::FETCH_ASSOC)){
                                $q->execute($heir);
                            }
                        }
                        // Отношения с прототипами для объекта
                        $values = '';
                        foreach ($protos as $i => $p){
                            $values.='(:obj, '.$p.', '.($i+1).'),';
                        }
                        $make = $this->db->prepare('
                            INSERT {protos} (object_id, proto_id, `level`)
                            VALUES '.$values.' (:obj,:obj,0)
                            ON DUPLICATE KEY UPDATE `level` = VALUES(level), is_delete = 0
                        ');
                        $make->execute(array(':obj' => $entity));
                    }else{
                        if ($incomplete){
                            // Объект уже кем-то прототипирован, поэтому и для них добавляется отношения с proto
                            // У наследников объекта выбирается запись-отношение с $entity и она же вставляется с новым proto_id и увеличенным level
                            $q = $this->db->prepare('
                                INSERT {protos} (object_id, proto_id, `level`)
                                SELECT object_id, :proto, `level`+1 FROM {protos}
                                WHERE proto_id = :obj AND is_delete = 0
                                ON DUPLICATE KEY UPDATE `level` = VALUES(level), is_delete = 0
                            ');
                            $q->execute(array(':obj' => $entity, ':proto'=>$proto));
                        }
                        // Считается, что у прототипа нет своего прототипа, поэтому только с ним создаётся отношение
                        $make = $this->db->prepare('
                            INSERT {protos} (object_id, proto_id, `level`)
                            VALUES (:obj,:proto,1), (:obj,:obj,0)
                            ON DUPLICATE KEY UPDATE `level` = VALUES(level), is_delete = 0
                        ');
                        $make->execute(array(':obj' => $entity, ':proto'=>$proto));
                    }
                }
            }
        }
    }

    /**
     * Полное пересоздание дерева родителей
     * @throws \Exception
     */
    function rebuildParents()
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
            while ($row = $q->fetch(\boolive\database\DB::FETCH_ASSOC)){
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
    function rebuildProtos()
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
            while ($row = $q->fetch(\boolive\database\DB::FETCH_ASSOC)){
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
     * @param bool $is_new Возвращаемый прзнак, был ли создан новый идентификатор?
     * @return int|null
     */
    function localId($uri, $create = true, &$is_new = false)
    {
        $is_new = false;
        if ($uri instanceof Entity){
            $uri = $uri->key();
        }
        if (!is_string($uri)){
            return 0;
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
                    return 0;
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
            return 0;
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
        $attribs['parent'] = $attribs['parent'] == 0 ? null : $this->key.'//'.$attribs['parent'];
        $attribs['proto'] = $attribs['proto'] == 0 ? null : $this->key.'//'.$attribs['proto'];
        $attribs['author'] = $attribs['author'] == 0 ? null : $this->key.'//'.$attribs['author'];
        $attribs['is_default_value'] = $this->key.'//'.$attribs['is_default_value'];
        $attribs['is_default_class'] = ($attribs['is_default_class'] !== '0' && $attribs['is_default_class'] != Entity::ENTITY_ID)? $this->key.'//'.$attribs['is_default_class'] : $attribs['is_default_class'];
        $attribs['is_link'] = ($attribs['is_link'] !== '1' && $attribs['is_link'] !== '0' && $attribs['is_link'] != Entity::ENTITY_ID)? $this->key.'//'.$attribs['is_link'] : $attribs['is_link'];
        $attribs['order'] = intval($attribs['order']);
        $attribs['date'] = intval($attribs['date']);
        $attribs['parent_cnt'] = intval($attribs['parent_cnt']);
        $attribs['proto_cnt'] = intval($attribs['proto_cnt']);
        $attribs['value_type'] = intval($attribs['value_type']);
        if (empty($attribs['is_draft'])) unset($attribs['is_draft']); else $attribs['is_draft'] = true;
        if (empty($attribs['is_hidden'])) unset($attribs['is_hidden']); else $attribs['is_hidden'] = true;
        if (empty($attribs['is_mandatory'])) unset($attribs['is_mandatory']); else $attribs['is_mandatory'] = true;
        if (empty($attribs['is_completed'])) unset($attribs['is_completed']); else $attribs['is_completed'] = true;
        if (empty($attribs['is_property'])) unset($attribs['is_property']); else $attribs['is_property'] = true;
        if (empty($attribs['is_relative'])) unset($attribs['is_relative']); else $attribs['is_relative'] = true;
        if (isset($attribs['is_accessible'])){
            if (!empty($attribs['is_accessible'])) unset($attribs['is_accessible']); else $attribs['is_accessible'] = false;
        }
        $attribs['is_exist'] = true;
        unset($attribs['valuef']);
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
            if ($classes = Cache::get('mysqlstore/classes')){
                // Из кэша
                $this->classes = json_decode($classes, true);
            }else{
                // Из бд и создаём кэш
                $q = $this->db->query('SELECT ids.* FROM ids JOIN objects ON objects.id = ids.id AND objects.is_default_class = 0');
                $this->classes = array();
                while ($row = $q->fetch(DB::FETCH_ASSOC)){
                    if ($row['uri'] !== ''){
                        $names = F::splitRight('/', $row['uri'], true);
                        $this->classes['//'.$row['id']] = '\\site\\'.str_replace('/', '\\', trim($row['uri'],'/')).'\\'.$names[1];
                    }else{
                        $this->classes['//'.$row['id']] = '\\site\\site';
                    }
                }
                Cache::set('mysqlstore/classes', F::toJSON($this->classes, false));
            }
        }
        if ($id != Entity::ENTITY_ID){
            if (isset($this->classes[$id])){
                return $this->classes[$id];
            }else{
                // По id выбираем запись из таблицы ids. Скорее всего объект внешний, поэтому его нет в таблице objects
                $q = $this->db->prepare('SELECT ids.* FROM ids WHERE id = ?');
                $q->execute(array($this->localId($id, false)));
                if ($row = $q->fetch(DB::FETCH_ASSOC)){
                    if (Data::isAbsoluteUri($row['uri'])){
                        $row['uri'] = Data::convertAbsoluteToLocal($row['uri']);
                    }
                    $names = F::splitRight('/', $row['uri'], true);
                    $this->classes[$id] = '\\site\\'.str_replace('/', '\\', trim($row['uri'],'/')) . '\\' . $names[1];
                }else{
                    $this->classes[$id] = '\\boolive\\data\\Entity';
                }
                Cache::set('mysqlstore/classes', F::toJSON($this->classes, false));
                return $this->classes[$id];
            }
        }
        return '\\boolive\\data\\Entity';
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
        if (($new_attr['is_default_class']!=$last_class && ($new_attr['is_default_class'] == 0 || $last_class == 0)) ||
            ($new_attr['is_default_class'] == 0 && $new_attr['uri']!=$last_attr['uri'])
        ){
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
                  `date` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Дата создания и версия',
                  `name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Имя',
                  `order` INT(11) NOT NULL DEFAULT '0' COMMENT 'Порядковый номер. Уникален в рамках родителя',
                  `parent` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Идентификатор родителя',
                  `parent_cnt` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Уровень вложенности (кол-во родителей)',
                  `proto` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Идентификатор прототипа',
                  `proto_cnt` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Уровень наследования (кол-во прототипов)',
                  `value` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Строковое значение',
                  `valuef` DOUBLE NOT NULL DEFAULT '0' COMMENT 'Числовое значение для правильной сортировки и поиска',
                  `value_type` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Тип значения. 1 - строка, 2 - текст, 3 - файл',
                  `author` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Идентификатор автора',
                  `is_draft` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Черновик (1) или нет (0)?',
                  `is_hidden` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Скрыт (1) или нет (0)?',
                  `is_link` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Используетя как ссылка или нет? Для оптимизации указывается идентификатор объекта, на которого ссылается ',
                  `is_mandatory` INT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Обязательный (1) или нет (0)? ',
                  `is_property` INT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Свойство (1) или нет (0)? ',
                  `is_relative` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Относительный (1) или нет (0) прототип?',
                  `is_default_value` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Идентификатор прототипа, чьё значение наследуется (если не наследуется, то свой id)',
                  `is_default_class` INT(10) UNSIGNED NOT NULL DEFAULT '4294967295' COMMENT 'Используется класс прототипа или свой?',
                  `is_completed` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Дополнен свйствами прототипа или нет (0 - нет, 1 - да)?',
                  PRIMARY KEY (`id`),
                  KEY `child` (`parent`,`order`,`name`,`value`,`valuef`),
                  KEY `indexation` (`parent`,`id`),
                  KEY `default_value` (`is_default_value`)
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
                ) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='Отношения объектов с родителями'
            ");
            $db->exec("
                CREATE TABLE {protos} (
                  `object_id` INT(10) UNSIGNED NOT NULL COMMENT 'Идентификатор объекта',
                  `proto_id` INT(10) UNSIGNED NOT NULL COMMENT 'Идентификатор прототипа',
                  `level` INT(10) UNSIGNED NOT NULL COMMENT 'Уровень прототипа от базового',
                  `is_delete` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Признак, удалено отношение или нет',
                  PRIMARY KEY (`object_id`,`proto_id`),
                  UNIQUE KEY `heirs` (`proto_id`,`object_id`)
                ) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='Отношения объектов с прототипами'
            ");
            $db->exec("
                CREATE TABLE `text` (
                  `id` INT(10) UNSIGNED NOT NULL COMMENT 'Идентификатор объекта',
                  `value` TEXT NOT NULL COMMENT 'Текстовое значение',
                  PRIMARY KEY (`id`),
                  FULLTEXT KEY `fulltext` (`value`)
                ) ENGINE=MYISAM DEFAULT CHARSET=utf8 COMMENT='Текстовые значения объектов'
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