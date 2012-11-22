<?php
/**
 * Сущность
 * Базовая логика для объектов модели данных.
 * @version 1.0
 * @link http://boolive.ru/createcms/data-and-entity
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\data;

use ArrayAccess, IteratorAggregate, ArrayIterator, Countable, Exception,
    Boolive\values\Values,
    Boolive\values\Check,
    Boolive\errors\Error,
    Boolive\data\Data,
    Boolive\file\File,
    Boolive\develop\ITrace,
    Boolive\develop\Trace,
    Boolive\values\Rule,
    Boolive\input\Input,
    Boolive\functions\F,
    Boolive\auth\Auth;

class Entity implements ITrace, IteratorAggregate, ArrayAccess, Countable
{
    /** @var array Атрибуты */
    protected $_attribs;
    /** @var array Подчиненные объекты (выгруженные из бд или новые, то есть не обязательно все существующие) */
    protected $_children = array();
    /** @var \Boolive\values\Rule Правило для проверки атрибутов */
    protected $_rule;
    /** @var \Boolive\data\Entity Экземпляр прототипа */
    protected $_proto = false;
    /** @var \Boolive\data\Entity Экземпляр родителя */
    protected $_parent = false;
    /** @var bool Принзнак, объект в процессе сохранения? */
    protected $_is_saved = false;
    /** @var bool Признак, виртуальный объект или нет. Если объект не сохранен в секции, то он виртуальный */
    protected $_virtual = true;
    /** @var bool Признак, сущесвтует объект или нет. Объект не существует, если виртуальный и все его прототипы виртуальные */
    protected $_exist = false;
    /** @var bool Признак, доступен объект или нет */
    protected $_accessible = true;
    /** @var bool Признак, изменены ли атрибуты объекта */
    protected $_changed = false;
    /** @var bool Признак, проверен ли объект или нет */
    protected $_checked = false;
    /** @var string uri родителя */
    protected $_parent_uri = null;
    /** @var string Имя объекта, определенное по uri */
    protected $_name = null;
    /**
     * Признак, требуется ли подобрать уникальное имя перед сохранением или нет?
     * Также означает, что текущее имя (uri) объекта временное
     * Если строка, то определяет базовое имя, к кторому будут подбираться числа для уникальности
     * @var bool|string
     */
    protected $_rename = false;

    public function __construct($attribs = array(), $virtual = true, $exist = false, $accessible = true)
    {
        $this->_attribs = $attribs;
        $this->_virtual = (bool)$virtual;
        $this->_exist = (bool)$exist;
        $this->_accessible = (bool)$accessible;
    }

    /**
     * Установка правила на атрибуты
     */
    protected function defineRule()
    {
        $this->_rule = Rule::arrays(array(
                'uri'    	 => Rule::uri()->max(255)->required(), // URI - идентификатор объекта. Уникален в рамках проекта
                'lang'		 => Rule::string()->max(3)->default('')->required(), // Язык (код)
                'owner'		 => Rule::int()->default(0)->required(), // Владелец (код)
                'date'		 => Rule::int(), // Дата создания в секундах
                'order'		 => Rule::any(Rule::null(), Rule::int()), // Порядковый номер. Уникален в рамках родителя
                'proto'		 => Rule::any(Rule::uri()->max(255), Rule::null()), // URI прототипа
                'value'	 	 => Rule::any(Rule::null(), Rule::string()), // Значение любой длины
                'is_logic'	 => Rule::bool()->int(), // Имеется ли у объекта свой класс в его директории. Имя класса по uri
                'is_file'	 => Rule::bool()->int(), // Связан ли с файлом (значение = имя файла). Путь на файл по uri
                'is_history' => Rule::bool()->int()->default(0)->required(), // В истории или нет
                'is_delete'	 => Rule::bool()->int(), // Удаленный или нет
                'is_hidden'	 => Rule::bool()->int(), // Скрытый или нет
                'is_link'	 => Rule::bool()->int(), // Ссылка или нет
                'override'	 => Rule::bool()->int(), // Переопределять всех подчиенных от прототипа или нет
                // Сведения о загружаемом файле. Не является атрибутом объекта
                'file'		 => Rule::arrays(array(
                        'tmp_name'	=> Rule::string(), // Путь на связываемый файл
                        'name'		=> Rule::lowercase()->ospatterns('*.*')->required()->ignore('lowercase'), // Имя файла, из которого будет взято расширение
                        'size'		=> Rule::int(), // Размер в байтах
                        'error'		=> Rule::int()->eq(0, true) // Код ошибки. Если 0, то ошибки нет
                    )
                )
            )
        );
    }

    /**
     * Возвращает правило на атрибуты
     * @return Rule
     */
    public function getRule()
    {
        if (!isset($this->_rule)) $this->defineRule();
        return $this->_rule;
    }

    #################################################
    #                                               #
    #            Управление атрибутами              #
    #                                               #
    #################################################

    /**
     * Получение атрибута или подчиненного объекта по имени
     * Если объекта нет, то он НЕ выбирается из секции, а возвращается null
     * @example $sub = $obj['sub'];
     * @param string $name Имя атрибута
     * @return mixed
     */
    public function offsetGet($name)
    {
        if (isset($this->_attribs[$name]) || array_key_exists($name, $this->_attribs)){
            if ($name == 'value' && !empty($this->_attribs['is_file'])){
                if (mb_substr($this->_attribs[$name], mb_strlen($this->_attribs[$name])-6) == '.value'){
                    try{
                        $this->_attribs[$name] = file_get_contents(DIR_SERVER_PROJECT.$this['uri'].'/'.$this->_attribs[$name]);
                    }catch(Exception $e){
                        $this->_attribs[$name] = '';
                    }
                    $this->_attribs['is_file'] = false;
                }
            }
            return $this->_attribs[$name];
        }else
        if ($proto = $this->proto()){
            return $proto[$name];
        }
        return null;
    }

    /**
     * Установка значений атриубту
     * @example $object[$name] = $value;
     * @param string $name Имя атрибута
     * @param mixed $value Значение
     */
    public function offsetSet($name, $value)
    {
        if (!isset($this->_attribs[$name]) || $this->offsetGet($name)!=$value){
            // Если не виртуальный, то запретить менять uri, lang, owner, date
            //if (!$this->_virtual && in_array($name, array('uri', 'lang', 'owner', 'date'))) return;

            if ($name == 'proto'){
                $this->_proto = null;
            }else
            if ($name == 'uri'){
                // Обновление uri текущих подчиненных
                if (isset($this->_attribs['uri'])){
                    // Удаление себя из текущего родителя, так как родитель поменяется
                    if (!empty($this->_parent)){
                        $this->_parent->offsetUnset($this->getName());
                        $this->_parent = null;
                    }
                    $this->updateName($value);
                }
                $this->_name = null;
                $this->_parent_uri = null;
                $this->_virtual = true; // @todo Возможно, объект с указанным uri существует и он не виртуальный
            }
            $this->_attribs[$name] = $value;
            $this->_changed = true;
            $this->_checked = false;
        }
    }

    /**
     * Удаление атрибута
     * @param string $name Имя атрибута
     */
    public function offsetUnset($name)
    {
        if (isset($this->_attribs[$name])) unset($this->_attribs[$name]);
        $this->_changed = true;
        $this->_checked = false;
    }

    /**
     * Проверка существования атрибута
     * @param string $name Имя атрибута
     * @return bool
     */
    public function offsetExists($name)
    {
        return isset($this->_attribs[$name]) || array_key_exists($name, $this->_attribs);
    }

    /**
     * Проверка, атрибут отсутсвует или его значение неопредлено?
     * @param string $name Имя атрибута
     * @return bool
     */
    public function offsetEmpty($name)
    {
        return empty($this->_attribs[$name]);
    }

    /**
     * Замена всех атрибутов новыми значениями
     * @param array $attribs Новые значения атрибутов
     */
    public function exchangeAttribs($attribs)
    {
        $this->_attribs = array();
        $this->updateAttribs($attribs);
    }

    /**
     * Обновление атриубтов на соответсвующие значения $input
     * @param array $attribs Новые значения атрибутов
     */
    public function updateAttribs($attribs)
    {
        if (is_array($attribs)){
            foreach ($attribs as $key => $value){
                $this->offsetSet($key, $value);
            }
        }
    }

    /**
     * Каскадное обновление URI подчиненных на основании своего uri
     * Обновляются uri только выгруженных/присоединенных на данный момент подчиенных
     * @param $uri Свой новый URI
     */
    protected function updateName($uri)
    {
        $this->_attribs['uri'] = $uri;
        foreach ($this->_children as $child_name => $child){
            /* @var \Boolive\data\Entity $child */
            $child->updateName($uri.'/'.$child_name);
        }
    }

    /**
     * Все атрибуты объекта
     * @return array
     */
    public function getAllAttribs()
    {
        return $this->_attribs;
    }

    #################################################
    #                                               #
    #     Управление подчиненными объектами         #
    #                                               #
    #################################################

    /**
     * Получение подчиненного объекта по имени
     * @example $sub = $obj->sub;
     * @param string $name Имя подчиенного объекта
     * @return \Boolive\data\Entity
     */
    public function __get($name)
    {
        if (isset($this->_children[$name])){
            return $this->_children[$name];
        }else{
            $obj = Data::read($this['uri'].'/'.$name, (string)$this['lang'], (int)$this['owner']);
            $this->__set($name, $obj);
            return $obj;
        }
    }

    /**
     * Установка подчиенного объекта
     * Если $value не является объектом, то выполняется установка атрибута 'value' подчиненному объекту.
     * Если подчиненного с именем $name ещё нет, то будет создан новый.
     * Если $value является объектом, но uri отличяющийся от uri родителя + $name, то будет создан новый
     * объект, прототипированием от $value.
     * Если подчиенный с именем $name уже есть, он будет заменен
     * @example $object->sub = $sub;
     * @param $name
     * @param $value
     * @return \Boolive\data\Entity
     */
    public function __set($name, $value)
    {
        if ($value instanceof Entity){
            /** @var \Boolive\data\Entity $value */
            // Если имя неопределенно, то потрубуется подобрать уникальное автоматически при сохранении
            // Перед сохранением используется временное имя
            if (is_null($name)){
                $name = uniqid('rename');
                $rename = 'entity';
            }
            // Если у объект есть uri и он отличается от необходимого, то прототипируем объект
            if (isset($value->_attribs['uri']) && isset($this->_attribs['uri']) && $value->_attribs['uri']!= $this->_attribs['uri'].'/'.$name){
                // В качестве базового имени - имя прототипа.
                if (isset($rename)) $rename = $value->getName();
                $value = $value->birth($this);
            }
            // Установка uri для объекта, если есть свой uri
            if (isset($this->_attribs['uri'])){
                $value->_attribs['uri'] = $this->_attribs['uri'].'/'.$name;
            }
            if (isset($rename)) $value->_rename = $rename;
            $value->_parent = $this;

            $this->_children[$name] = $value;
            return $value;
        }else{
            // Установка значения для подчиненного
            $this->__get($name)->offsetSet('value', $value);
            return $this->__get($name);
        }
    }

    /**
     * Добавление подчиненного с автоматическим именованием
     * @param $value
     * @return \Boolive\data\Entity
     */
    public function add($value){
        $obj = $this->__set(null, $value);
        $obj->_changed = true;
        return $obj;
    }

    /**
     * Проверка, имеется ли подчиенный с именем $name в списке выгруженных?
     * @example $result = isset($object->sub);
     * @param $name Имя подчиненного объекта
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->_children[$name]);
    }

    /**
     * Удаление из списка выгруженных подчиенного с именем $name
     * @example unset($object->sub);
     * @param $name Имя подчиненного объекта
     */
    public function __unset($name)
    {
        unset($this->_children[$name]);
    }

    /**
     * Подобрать уникальное имя при сохранении
     * За основу берется текущее имя
     */
    public function chooseUniqueName()
    {
        $this->_rename = $this->getName();
    }

//    /**
//     * Поиск подчиенных объектов
//     * @todo По умолчанию указать язык и владельца
//     * @param array $cond Услвоие поиска
//     * <code>
//     * $cond = array(
//     *   'where' => '', // Условие на атрибуты объекта. Условие как в SQL на колонки таблицы.
//     *   'values' => array(), // Массив значений для вставки в условие вместо "?"
//     *   'order' => '', // Способ сортировки. Задается как в SQL, например: `order` DESC, `value` ASC
//     *   'count' => 0, // Количество выбираемых объектов
//     *   'start' => 0 // Смещение от первого найденного объекта, с которого начинать выбор
//     * );
//     * </code>
//     * @param bool $load Признак, загрузить (true) иле нет (false) найденные объекты в список подчиненных?
//     * @param null $key_by Имя атрибута, значение которого использоваться в качестве ключей массива результата
//     * @return array
//     */
//    public function find($cond = array(), $load = false, $key_by = null)
//    {
//        if ($s = Data::getSection($this['uri'], false)){
//            if (!empty($cond['where'])){
//                $cond['where'].=' AND uri like ? AND level=?';
//            }else{
//                $cond['where'] = 'uri like ? AND level=?';
//            }
//            $cond['values'][] = $this->_attribs['uri'].'/%';
//            $cond['values'][] = $this->getLevel()+1;
//            $results = $s->select($cond);
//            if ($load) $this->_children = $results;
//            // Смена ключей масива
//            if ($key_by){
//                $list = array();
//                $cnt = sizeof($results);
//                for ($i=0; $i<$cnt; $i++){
//                    switch ($key_by){
//                        case 'name': $key = $results[$i]->getName();
//                            break;
//                        case 'value': $key = $results[$i]->getValue();
//                            break;
//                        default: $key = $results[$i][$key_by];
//                    }
//                    $list[$key] = $results[$i];
//                }
//                $results = $list;
//            }
//
//            return $results;
//        }
//        return array();
//    }

//    /**
//     * Поиск подчиненных объектов с учетом унаследованных
//     * @todo Ограничение количества выборки..
//     * @param array $cond Услвоие поиска
//     * <code>
//     * $cond = array(
//     *   'where' => '', // Условие на атрибуты объекта. Условие как в SQL на колонки таблицы.
//     *   'values' => array(), // Массив значений для вставки в условие вместо "?"
//     *   'order' => '', // Способ сортировки. Задается как в SQL, например: `order` DESC, `value` ASC
//     *   'count' => 0, // Количество выбираемых объектов
//     *   'start' => 0 // Смещение от первого найденного объекта, с которого начинать выбор
//     * );
//     * </code>
//     * @param bool $load Признак, загрузить (true) иле нет (false) найденные объекты в список подчиненных?
//     * @param null|string $key_by Имя атрибута, значение которого использоваться в качестве ключей массива результата
//     * @param bool $req Признак рекурсивного вызова метода (используется самим методом)
//     * @return array
//     */
//    public function findAll($cond = array(), $load = false, $key_by = 'name', $req = false)
//    {
//        $results = $this->find($cond, false, 'name');
//        if (empty($this->_attribs['override']) && $proto = $this->proto()){
//            $proto_sub = $proto->findAll($cond, false, 'name', true);
//            foreach ($proto_sub as $key => $child){
//                if (!isset($results[$key])){
//                    $bkey = $this['uri'].'/'.$key;//.' '.$this['lang'].' '.(int)$this['owner'];
//                    if (Data::bufferExist($bkey)){
//                        $results[$key] = Data::bufferGet($bkey);
//                    }else{
//                        $results[$key] = $child->birth();
//                        $results[$key]['uri'] = $this['uri'].'/'.$key;
//                        $results[$key]['order'] = $child['order'];
//                        $results[$key]['lang'] = $child['lang'];
//                        $results[$key]['owner'] = $child['owner'];
//                        // Объект ссылка?
//                        if (!empty($this->_attribs['is_link']) || !empty($proto->_attribs['is_link'])){
//                            $results[$key]['is_link'] = 1;
//                        }
//                        Data::bufferAdd($bkey, $results[$key]);
//                    }
//                }
//            }
//        }
//        if (!$req){
//            // Смена ключей, если требуется
//            if (empty($key_by)){
//                $results = array_values($results);
//            }else
//            if ($key_by != 'name'){
//                $list = array();
//                foreach ($results as $child){
//                    switch ($key_by){
//                        case null: $list[] = $child;
//                            break;
//                        case 'value': $list[$child->getValue()] = $child;
//                            break;
//                        default: $list[$child[$key_by]] = $child;
//                    }
//                }
//                $results = $list;
//            }
//            // Сортировки
//            if (!empty($cond['order']) && preg_match('/`([a-z_]+)`\s*(DESC)?/iu', $cond['order'], $math)){
//                uasort($results, function($a, $b) use ($math){
//                    if ($a[$math[1]] == $b[$math[1]]){
//                        return 0;
//                    }
//                    $comp = $a[$math[1]] < $b[$math[1]]? -1: 1;
//                    return (!empty($math[2]))? -1*$comp : $comp;
//                });
//            }
//            // Запоминаем результат в экземпляре
//            if ($load) $this->_children = $results;
//
//            //Trace::groups('DB')->group('findAll_count')->set(Trace::groups('DB')->group('findAll_count')->get()+1);
//        }
//        return $results;
//    }

    public function findAll2($cond = array(), $load = false, $key_by = 'name', $depth = 1)
    {
        $cond['from'] = array($this['uri'], $depth);
        $result = Data::select($cond, $key_by);
        if ($load){
            if ($key_by == 'name'){
                $this->_children = $result;
            }else{
                foreach ($result as $obj){
                    $this->_children[$obj->getName()] = $obj;
                }
            }
        }
        return $result;
    }

//    /**
//     * Количество подчиенных удовлетворяющих условию
//     * @param array $cond Услвоие поиска
//     * @return int
//     */
//    public function findCount($cond = array())
//    {
//        if ($s = Data::getSection($this['uri'], false)){
//            if (!empty($cond['where'])){
//                $cond['where'].=' AND uri like ? AND level=?';
//            }else{
//                $cond['where'] = 'uri like ? AND level=?';
//            }
//            $cond['values'][] = $this->_attribs['uri'].'/%';
//            $cond['values'][] = $this->getLevel()+1;
//            return $s->select_count($cond);
//        }
//        return 0;
//    }

    /**
     * Проверка объекта соответствию условию
     * @param array $cond Условие
     * @return bool
     */
    public function verify($cond)
    {
        if (empty($cond)) return true;
        if (is_array($cond[0])) $cond = array('all', $cond);
        switch ($cond[0]){
            // Все услвоия (AND)
            case 'all':
                foreach ($cond[1] as $c){
                    if (!$this->verify($c)) return false;
                }
                return true;
            // Хотябы одно условие (OR)
            case 'any':
                foreach ($cond[1] as $c){
                    if ($this->verify($c)) return true;
                }
                return !sizeof($cond[1]);
            // Отрицание условия (NOT)
            case 'not':
                return !$this->verify($cond[1]);
            // Сравнение атрибута
            case 'attr':
                switch ($cond[2]){
                    case '=': return $this[$cond[1]] == $cond[3];
                    case '<': return $this[$cond[1]] < $cond[3];
                    case '>': return $this[$cond[1]] > $cond[3];
                    case '>=': return $this[$cond[1]] >= $cond[3];
                    case '<=': return $this[$cond[1]] <= $cond[3];
                    case '!=':
                    case '<>': return $this[$cond[1]] != $cond[3];
                    case 'like':
                        $pattern = strtr($cond[3], array('%' => '*', '_' => '?'));
                        return fnmatch($pattern, $this[$cond[1]]);
                    case 'in':
                        if (!is_array($cond[3])) $cond[3] = array($cond[3]);
                        return in_array($cond[1], $cond[3]);
                }
                return false;
            // Проверка родителя
            case 'parent':
                return mb_strpos($this->_attribs['uri'], $cond[1].'/') === 0;
            // Является частью чего-то с учётом наследования
            // Например заголовок статьи story1 является его частью и частью эталона статьи
            // Пример из жизни: Ветка является частью дерева, но не конкретезируется, какого именно
            case 'of':
                // @todo Учёт наследования
                if (!isset($cond[2])) $cond[2] = 1;
                if ($cond[2] == 3){
                    return $this->_attribs['uri'] == $cond[1];
                }else
                if ($cond[2] == 2){
                    return mb_strpos($this->_attribs['uri'], $cond[1].'/') === 0;
                }else{
                    return $this->_attribs['uri'] == $cond[1] || mb_strpos($this->_attribs['uri'], $cond[1].'/') === 0;
                }
            // Услвоия на подчиненного
            case 'child':
                $child = $this->{$cond[1]};
                if ($child->isExist()){
                    if (isset($cond[2])){
                        return $child->verify($cond[2]);
                    }
                    return true;
                }
                return false;
            // Проверка наследования
            case 'is':
                if (!is_array($cond[1])) $cond[1] = array($cond[1]);
                foreach ($cond[1] as $proto){
                    if ($this->is($proto)) return true;
                }
                return false;
            // Неподдерживаемые условия
            default: return false;
        }
    }

//    /**
//     * Количество подчиенных удовлетворяющих условию с учётом прототипирования
//     * @param array $cond Услвоие поиска
//     * @param bool $req
//     * @return int
//     */
//    public function findCountAll($cond = array(), $req = false)
//    {
//        $results = $this->find($cond, false, 'name');
//        if ($proto = $this->proto()){
//            $proto_sub = $proto->findCountAll($cond, true);
//            $results = array_replace($results, $proto_sub);
//        }
//        if ($req) return $results;
//        return count($results);
//    }

    /**
     * Количество подчиненных в списке выгруженных
     * @example
     * $cnt = count($object);
     * $cnt = $object->count();
     * @return int
     */
    public function count()
    {
        return count($this->_children);
    }

    /**
     * Итератор по выгруженным подчиненным объектам
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_children);
    }

    /**
     * Список выгруженных подчиненных
     * @return array
     */
    public function getChildren()
    {
        return $this->_children;
    }

    #################################################
    #                                               #
    #            Управление объектом                #
    #                                               #
    #################################################

    /**
     * Проверка объекта
     * @param null $errors Возвращаемый объект ошибки
     * @return bool Признак, корректен объект (true) или нет (false)
     */
    public function check(&$errors = null)
    {
        // "Контейнер" для ошибок по атрибутам и подчиненным объектам
        $errors = new Error('Неверный объект', $this['uri']);
        if ($this->_checked) return true;

        // Проверка и фильтр атрибутов
        $attribs = new Values($this->_attribs);
        $this->_attribs = $attribs->get($this->getRule(), $error);
        if ($error){
            $errors->_attribs->add($error->getAll());
        }
        // Проверка подчиненных
        foreach ($this->_children as $child){
            $error = null;
            /** @var \Boolive\data\Entity $child */
            if (!$child->check($error)){
                $errors->_children->add($error);
            }

        }
        // Проверка родителем.
        if ($p = $this->parent()) $p->checkChild($this, $errors);
        // Если ошибок нет, то удаляем контейнер для них
        if (!$errors->isExist()){
            //$errors = null;
            return $this->_checked = true;
        }
        return false;
    }

    /**
     * Проверка подчиненного в рамках его родителей
     * Возможно обращение к родителям выше уровнем, чтобы объект проверялся в ещё более глобальном окружении,
     * например для проверки уникальности значения по всему разделу/базе.
     * @param \Boolive\data\Entity $child Проверяемый подчиненный
     * @param \Boolive\errors\Error $error Объект ошибок подчиненного
     * @return bool Признак, корректен объект (true) или нет (false)
     */
    protected function checkChild(Entity $child, Error $error)
    {
        /** @example
         * if ($child->getName() == 'bad_name'){
         *     // Так как ошибка из-за атрибута, то добавляем в $error->_attribs
         *     // Если бы проверяли подчиненного у $child, то ошибку записывали бы в $error->_children
         *	   $error->_attribs->name = new Error('Недопустимое имя', 'impossible');
         *     return false;
         * }
         */
        return true;
    }

    /**
     * Сохранение объекта в секции
     * @throws
     */
    public function save($history = true, &$error = null, $access = true)
    {
        if (!$this->_is_saved && $this->check($error)){
            try{
                $this->_is_saved = true;
                // Если создаётся история, то нужна новая дата
                if ($history) $this->_attribs['date'] = time();
                // Сохранение себя
                if ($this->_changed && Data::write($this, $error, $access)){
                    $this->_virtual = false;
                    $this->_exist = true;
                    $this->_changed = false;
                }
                // @todo Если было переименование из-за _rename, то нужно обновить uri подчиненных
                // Сохранение подчиененных
                $children = $this->getChildren();
                foreach ($children as $child){
                    /** @var \Boolive\data\Entity $child */
                    $child->save($history, $error_child, $access);
                }
                $this->_is_saved = false;
            }catch (Exception $e){
                $this->_is_saved = false;
                throw $e;
            }
        }
    }

    /**
     * Удаление объекта
     * Объект помечается как удаленный
     * @todo Установка атрибута is_delete и сохранение в секции без проверок и прочих сохранений
     */
    public function delete()
    {
        $this->_attribs['is_delete'] = true;
        return $this;
    }

    /**
     * Создание нового объекта прототипированием от себя
     * @param null|\Boolive\data\Entity $for Для кого создаётся новый объект?
     * @return \Boolive\data\Entity
     */
    public function birth($for = null)
    {
        $class = get_class($this);
        if (isset($for)){
            $attr = $this->_attribs;
            $attr['uri'] = $for['uri'].'/'.$this->getName();
            $attr['value'] = null;
            $attr['is_file'] = 0;
            $attr['is_logic'] = 0;
            if (isset($attr['level'])) $attr['level'] = mb_substr_count($attr['uri'], '/');
            if (!empty($for->_attribs['is_link'])) $attr['is_link'] = 1;
            $access = $this->_accessible && $for->isAccessible();
        }else{
            $attr = array();
            $access = $this->_accessible;
        }
        $attr['proto'] = Data::makeURI($this['uri'], $this['lang'], $this['owner']);
        if (!empty($this['is_link'])) $attr['is_link'] = true;
        return new $class($attr, true, $this->_exist, $access);
    }

    /**
     * Родитель объекта
     * @return \Boolive\data\Entity|null
     */
    public function parent()
    {
        if ($this->_parent === false){
            $this->_parent = Data::read($this->getParentUri());
        }
        return $this->_parent;
    }

    /**
     * Прототип объекта
     * @param bool $use_index
     * @return \Boolive\data\Entity|null
     */
    public function proto($use_index = true)
    {
        if ($this->_proto === false){
            if (isset($this->_attribs['proto'])){
                $info = Data::getURIInfo($this->_attribs['proto']);
                if ($use_index){
                    $this->_proto = Data::read($info['uri'], $info['lang'], $info['owner']);
                }else{
                    $this->_proto = Data::read($info['uri'], $info['lang'], $info['owner']);
                }
            }else{
                $this->_proto = null;
            }
        }
        return $this->_proto;
    }

    /**
     * Реальный объект
     * Возвращает себя, если не виртуальный, либо первого реального прототипа объекта
     * Если реальных прототипов нет, то возвращается null
     * @return Entity | null
     */
    public function real()
    {
        $obj = $this;
        while ($obj && $obj->isVirtual()) $obj = $obj->proto();
        return $obj;
    }

    /**
     * Возращает данный объект ($this) или первый из его прототипов не являющимся ссылкой (is_link=0)
     * Если объект и все его прототипы являются ссылками, то возвращается null
     * @return \Boolive\data\Entity|null
     */
    public function notLink()
    {
        if (empty($this->_attribs['is_link'])) return $this;
        if ($proto = $this->proto()) return $proto->notLink();
        return null;
    }

    /**
     * Все прототипы объекта
     * @return array Список URI прототипов
     */
    public function getAllProto($use_index = true)
    {
        $result = array();
        $obj = $this;
        while ($obj && !empty($obj->_attribs['proto'])){
            $result[] = $obj->_attribs['proto'];
            $obj = $obj->proto($use_index);
        }
        return $result;
    }
    /**
     * При обращении к объекту как к скалярному значению (строке), возвращается значение атрибута value
     * @example
     * print $object;
     * $value = (string)$obgect;
     * @return mixed
     */
    public function __toString()
    {
        return (string)$this->getValue();
    }

    /**
     * Вызов несуществующего метода
     * Если объект внешний, то вызов произведет модуль секции объекта
     * @param string $method
     * @param array $args
     * @return null|void
     */
    public function __call($method, $args)
    {
        return null;
//        if ($s = Data::section($this['uri'], true)){
//            $s->call($method, $args);
//        }
    }

    /**
     * URI родителя
     * @return string|null Если родителя нет, то null. Пустая строка является корректным uri
     */
    public function getParentUri()
    {
        if (!isset($this->_parent_uri)){
            if (isset($this->_attribs['uri'])){
                $names = F::splitRight('/',$this->_attribs['uri']);
                $this->_parent_uri = $names[0];
                $this->_name = $names[1];
            }else
            if (isset($this->_parent)){
                $this->_parent_uri = $this->_parent['uri'];
            }else{
                $this->_parent_uri = null;
            }
        }
        return $this->_parent_uri;
    }

    /**
     * Имя родителя
     * @return string
     */
    public function getParentName()
    {
        if ($parent_uri = $this->getParentUri()){
            $names = F::splitRight('/', $parent_uri);
            return $names[1];
        }
        return '';
    }

    /**
     * Имя объекта
     * @return string|null
     */
    public function getName()
    {
        if (!isset($this->_parent_uri)){
            if (isset($this->_attribs['uri'])){
                $names = F::splitRight('/',$this->_attribs['uri']);
                $this->_parent_uri = $names[0];
                $this->_name = $names[1];
            }
        }
        return $this->_name;
    }

    /**
     * Значение объекта с учётом прототипирования
     * @return string|null
     */
    public function getValue($use_index = true)
    {
        $value = $this->offsetGet('value');
        if (!isset($value) && ($proto = $this->proto($use_index))){
            $value = $proto->getValue($use_index);
        }
        return $value;
    }

    /**
     * Уровень вложенности
     * Вычисляется по uri
     */
    public function getLevel()
    {
        return mb_substr_count($this['uri'], '/');
    }

    /**
     * Дмректория объекта
     * @param bool $root Признак, возвращать путь от корня сервера или от web директории (www)
     * @return string
     */
    public function getDir($root = false)
    {
        if ($root){
            return DIR_SERVER_PROJECT.ltrim($this['uri'].'/','/');
        }else{
            return DIR_WEB_PROJECT.ltrim($this['uri'].'/','/');
        }
    }

    /**
     * Путь на файл, если объект ассоциирован с файлом.
     * Если значение null, то информация берется от прототипа
     * @param bool $root
     * @return null|string
     */
    public function getFile($root = false)
    {

        if (!empty($this->_attribs['is_file'])){
            $file = $this->getDir($root);
            if (!empty($this->_attribs['is_history'])) $file.='_history_/'.$this['date'].'_';
            return $file.$this->_attribs['value'];
        }else{
            $proto = $this->proto();
            if (!isset($this->_attribs['value']) && $proto){
                return $proto->getFile($root);
            }
        }

        return null;
    }

    /**
     * Проверка, является ли объект файлом.
     * Проверяется с учетом прототипа
     * @return bool
     */
    public function isFile($use_index = true)
    {
        return !empty($this->_attribs['is_file']) || (!isset($this->_attribs['value']) && ($proto = $this->proto($use_index)) && $proto->isFile($use_index));
    }

    /**
     * Сравнение объектов по uri
     * @param \Boolive\data\Entity $entity
     * @return bool
     */
    public function isEqual($entity)
    {
        if (!$entity) return false;
        return $this['uri'] == $entity['uri'];
    }

    /**
     * Признак, изменены атрибуты объекта или нет
     * @return bool
     */
    public function isChenged()
    {
        return $this->_changed;
    }

    /**
     * Признак, виртуальный объект или нет. Если объект не сохранен в секции, то он виртуальный
     * @return bool
     */
    public function isVirtual()
    {
        return $this->_virtual;
    }

    /**
     * Признак, сущесвтует объект или нет. Объект не существует, если виртуальный и все его прототипы виртуальные.
     * @return bool
     */
    public function isExist()
    {
        return $this->_exist;
    }

    /**
     * Признак, доступен объект или нет для совершения опредленного действия над ним
     * Доступность проверяется для текущего пользователя
     * @param string $action_kind Название действия. По умолчанию дейсвте чтения объекта.
     * @return bool
     */
    public function isAccessible($action_kind = 'read')
    {
        if ($this->_accessible){
            if ($action_kind != 'read'){
                return $this->verify(Auth::getUser()->getAccessCond($action_kind, $this->getParentUri(), 1));
            }
            return true;
        }
        return false;
    }

    /**
     * Признак, находится ли объект в процессе сохранения?
     * @return bool
     */
    public function isSaved()
    {
        return $this->_is_saved;
    }

    /**
	 * Проверка, является ли объект подчиенным для указанного?
	 * @param \Boolive\data\Entity $parent
	 * @return bool
	 */
    public function isChildOf($parent)
    {
        return $parent['uri'].'/' == mb_substr($this['uri'],0,mb_strlen($parent['uri'])+1);
    }

    /**
     * Проверка, имеется ли у объекта указанный прототип
     * @param $uri
     * @return bool
     */
    public function is($uri){
        $obj = $this;
        // Поиск варианта отображения для объекта
        do{
            // Если виджеты не исполнялись, тогда ищем соответсвие по прототипу
            if ($obj['uri'] == $uri || $obj['proto'] == $uri){
                return true;
            }
        }while($obj = $obj->proto());
        return false;
    }
    /**
     * Клонирование объекта
     */
    public function __clone()
    {
        foreach ($this->_children as $name => $child){
            $this->__set($name, clone $child);
        }
    }

    /**
     * Значения внутренных свойств объекта для трасировки при отладки
     * @return array
     */
    public function trace()
    {
        //$trace['hash'] = spl_object_hash($this);
        $trace['_attribs'] = $this->_attribs;
        $trace['_changed'] = $this->_changed;
        $trace['_virtual'] = $this->_virtual;
        $trace['_exist'] = $this->_exist;
        $trace['_accesible'] = $this->_accessible;
        $trace['_checked'] = $this->_checked;
        /*if ($this->_rename) */$trace['_rename'] = $this->_rename;
        //$trace['_proto'] = $this->_proto;
        //$trace['_parent'] = $this->_parent;
        $trace['_children'] = $this->_children;
        return $trace;
    }
}
