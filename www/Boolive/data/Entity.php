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
    Boolive\errors\Error,
    Boolive\data\Data,
    Boolive\file\File,
    Boolive\develop\ITrace,
    Boolive\values\Rule,
    Boolive\commands\Commands,
    Boolive\input\Input,
    Boolive\functions\F;

class Entity implements ITrace, IteratorAggregate, ArrayAccess, Countable
{
    /** @var array Атрибуты */
    protected $_attribs;
    /** @var array Подчиненные объекты (выгруженные из бд или новые, то есть не обязательно все существующие) */
    protected $_children = array();
    /** @var \Boolive\data\Rule Правило для проверки атрибутов */
    protected $_rule;
    /** @var \Boolive\data\Entity Экземпляр прототипа */
    protected $_proto = false;
    /** @var \Boolive\data\Entity Экземпляр родителя */
    protected $_parent = false;
    /** @var bool Принзнак, объект в процессе сохранения? */
    protected $_is_saved = false;
    /** @var bool Признак, виртуальный объект или нет. Если объект не сохранен в секции, то он виртуальный */
    protected $_virtual = true;
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
     * @var bool|string */
    protected $_rename = false;
    /**
     * Кoнтейнер входящих данных.
     * Инициализируется в методе start()
     * В качестве правила по умолчанию используется $this->getInputRule()
     * @var \Boolive\input\Input
     */
    protected $_input;
    /**
     * Команды, передающиеся по всем исполняемым объектам.
     * Инициализируется в методе start()
     * @var \Boolive\commands\Commands
     */
    protected $_commands;

    public function __construct($attribs = array())
    {
        $this->_attribs = $attribs;
    }

    /**
     * Установка правила на атрибуты
     */
    protected function defineRule()
    {
        $this->_rule = Rule::arrays(array(
                'uri'    	 => Rule::uri()->max(255)->required(), // URI - идентификатор объекта. Уникален в рамках проекта
                'lang'		 => Rule::string()->max(3)->default(0)->required(), // Язык (код)
                'owner'		 => Rule::int()->default(0)->required(), // Владелец (код)
                'date'		 => Rule::int(), // Дата создания в секундах
                'order'		 => Rule::any(Rule::null(), Rule::int()), // Порядковый номер. Уникален в рамках родителя
                'is_history' => Rule::bool()->int()->default(0)->required(), // В истории или нет
                'is_delete'	 => Rule::bool()->int(), // Удаленный или нет
                'is_hidden'	 => Rule::bool()->int(), // Скрытый или нет
                'is_logic'	 => Rule::bool()->int(), // Имеется ли у объекта свой класс в его директории. Имя класса по uri
                'is_file'	 => Rule::bool()->int(), // Связан ли с файлом (значение = имя файла). Путь на файл по uri
                'proto'		 => Rule::any(Rule::uri()->max(255), Rule::null()), // URI прототипа
                'value'	 	 => Rule::any(Rule::null(), Rule::string()), // Значение любой длины
                // Сведения о загружаемом файле. Не является атрибутом объекта
                'file'		 => Rule::arrays(array(
                                    'tmp_name'	=> Rule::string()->more(0)->required(), // Путь на связываемый файл
                                    'name'		=> Rule::ospatterns('*.*')->required(), // Имя файла, из которого будет взято расширение
                                    'size'		=> Rule::int(), // Размер в байтах
                                    'error'		=> Rule::in(0) // Код ошибки. Если 0, то ошибки нет
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

    /**
     * Возвращает правило на входящие данные
     * Используется, если объект исполняется как контроллер для обработки запроса
     * По умолчанию любые значения
     * @return null|\Boolive\values\Rule
     */
    public function getInputRule()
    {
        return Rule::any();
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
        if ($this->offsetExists($name)){
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
        if (!$this->offsetExists($name) || $this->offsetGet($name)!=$value){
            // Если не виртуальный, то запретить менять uri, lang, owner, date
            //if (!$this->_virtual && in_array($name, array('uri', 'lang', 'owner', 'date'))) return;

            if ($name == 'proto'){
                $this->_proto = null;
            }else
            if ($name == 'uri'){
                // Обновление uri текущих подчиненных
                if ($this->offsetExists($name)){
                    // Удаление себя из текущего родителя, так как родитель поменяется
                    if ($this->_parent instanceof Entity){
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
        if (!$this->offsetExists($name)) unset($this->_attribs[$name]);
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
        return array_key_exists($name, $this->_attribs);
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

    #################################################
    #                                               #
    #     Управление подчиненными объектами         #
    #                                               #
    #################################################

    /**
     * Получение подчиненного объекта по имени
     * Если объекта нет, то будет выгружен из секции или создан виртуальный
     * @example $sub = $obj->sub;
     * @param string $name
     * @return array|Values|void
     */
    public function __get($name)
    {
        if (isset($this->_children[$name])){
            return $this->_children[$name];
        }else{
            $uri = $this['uri'].'/'.$name;
            // Если объекта нет в секции, то создается виртуальный
            if (!($obj = Data::object($uri, (string)$this['lang'], (int)$this['owner']))){
                // Поиск прототипа для объекта
                // Прототип тоже может оказаться виртуальным!
                if ($proto = $this->proto()){
                    $proto = $proto->{$name};
                }
                if ($proto){
                    $obj = $proto->birth();
                }else{
                    $obj = new Entity(array('uri'=>$uri, 'lang'=>$this['lang'], 'owner'=>$this['owner']));
                }
            }
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
            if (isset($value->_attribs['uri']) && $value->_attribs['uri']!= $this->_attribs['uri'].'/'.$name){
                // В качестве базового имени - имя прототипа.
                if (isset($rename)) $rename = $value->getName();
                $value = $value->birth();
            }
            // Установка uri для объекта, если есть свой uri
            if (isset($this['uri'])) $value['uri'] = $this['uri'].'/'.$name;
            if (isset($rename)) $value->_rename = $rename;
            $value->_parent = $this;
            $this->_children[$name] = $value;
        }else{
            // Установка значения для подчиненного
            $this->__get($name)->offsetSet('value', $value);
            return;
        }
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
     * Поиск подчиенных объектов
     * @todo По умолчанию указать язык и владельца
     * @param array $cond Услвоие поиска
     * <code>
     * $cond = array(
     *   'where' => '', // Условие на атрибуты объекта. Условие как в SQL на колонки таблицы.
     *   'values' => array(), // Массив значений для вставки в условие вместо "?"
     *   'order' => '', // Способ сортировки. Задается как в SQL, например: `order` DESC, `value` ASC
     *   'count' => 0, // Количество выбираемых объектов
     *   'start' => 0 // Смещение от первого найденного объекта, с которого начинать выбор
     * );
     * </code>
     * @param bool $load Признак, загрузить (true) иле нет (false) найденные объекты в список подчиненных?
     * @param null $key_by Имя атрибута, значение которого использоваться в качестве ключей массива результата
     * @return array
     */
    public function find($cond = array(), $load = false, $key_by = null)
    {
        if ($s = Data::section($this['uri'], false)){
            if (!empty($cond['where'])){
                $cond['where'].=' AND uri like ? AND level=?';
            }else{
                $cond['where'] = 'uri like ? AND level=?';
            }
            $cond['values'][] = $this['uri'].'/%';
            $cond['values'][] = $this->getLevel()+1;
            $results = $s->select($cond);
            if ($load) $this->_children = $results;
            // Смена ключей масива
            if ($key_by){
                $list = array();
                $cnt = sizeof($results);
                for ($i=0; $i<$cnt; $i++){
                    switch ($key_by){
                        case 'name': $key = $results[$i]->getName();
                            break;
                        case 'value': $key = $results[$i]->getValue();
                            break;
                        default: $key = $results[$i][$key_by];
                    }
                    $list[$key] = $results[$i];
                }
                $results = $list;
            }

            return $results;
        }
        return array();
    }

    /**
     * Поиск подчиненных объектов с учетом унаследованных
     * @todo Ограничение количества выборки..
     * @param array $cond Услвоие поиска
     * <code>
     * $cond = array(
     *   'where' => '', // Условие на атрибуты объекта. Условие как в SQL на колонки таблицы.
     *   'values' => array(), // Массив значений для вставки в условие вместо "?"
     *   'order' => '', // Способ сортировки. Задается как в SQL, например: `order` DESC, `value` ASC
     *   'count' => 0, // Количество выбираемых объектов
     *   'start' => 0 // Смещение от первого найденного объекта, с которого начинать выбор
     * );
     * </code>
     * @param bool $load Признак, загрузить (true) иле нет (false) найденные объекты в список подчиненных?
     * @param null $key_by Имя атрибута, значение которого использоваться в качестве ключей массива результата
     * @param bool $req Признак рекурсивного вызова метода (используется самим методом)
     * @return array
     */
    public function findAll($cond = array(), $load = false, $key_by = 'name', $req = false)
    {
        $results = $this->find($cond, false, 'name');
        if ($proto = $this->proto()){
            $proto_sub = $proto->findAll($cond, false, 'name', true);
            foreach ($proto_sub as $key => $child){
                if (!isset($results[$key])){
                    $results[$key] = $child->birth();
                    $results[$key]['uri'] = $this['uri'].'/'.$key;
                    $results[$key]['order'] = $child['order'];
                }
            }
        }
        if (!$req){
            // Смена ключей, если требуется
            if (empty($key_by)){
                $results = array_values($results);
            }else
            if ($key_by != 'name'){
                $list = array();
                foreach ($results as $child){
                    switch ($key_by){
                        case null: $list[] = $child;
                            break;
                        case 'value': $list[$child->getValue()] = $child;
                            break;
                        default: $list[$child[$key_by]] = $child;
                    }
                }
                $results = $list;
            }
            // Сортировки
            if (!empty($cond['order']) && preg_match('/`([a-z_]+)`\s*(DESC)?/iu', $cond['order'], $math)){
                uasort($results, function($a, $b) use ($math){
                    if ($a[$math[1]] == $b[$math[1]]){
                        return 0;
                    }
                    $comp = $a[$math[1]] < $b[$math[1]]? -1: 1;
                    return (!empty($math[2]))? -1*$comp : $comp;
                });
            }
            // Запоминаем результат в экземпляре
            if ($load) $this->_children = $results;
        }
        return $results;
    }

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
        if ($this->_checked) return true;
        // "Контейнер" для ошибок по атрибутам и подчиненным объектам
        $errors = new Error('Неверный объект', $this['uri']);
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
            $errors = null;
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
    public function save($history = true, &$error = null)
    {
        if (!$this->_is_saved && $this->check($error)){
            try{
                $this->_is_saved = true;
                // Если создаётся история, то нужна новая дата
                if ($history) $this->_attribs['date'] = time();
                // Сохранение себя
                if ($this->_changed){
                    if ($s = Data::section($this['uri'], true)){
                        $s->put($this);
                        $this->_virtual = false;
                        $this->_changed = false;
                    }
                }
                // @todo Если было переименование из-за _rename, то нужно обновить uri подчиненных
                // Сохранение подчиененных
                $children = $this->getChildren();
                foreach ($children as $child){
                    /** @var \Boolive\data\Entity $child */
                    $child->save();
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
        $this['is_delete'] = true;
        return $this;
    }

    /**
     * Создание нового объекта прототипированием от себя
     * @return \Boolive\data\Entity
     */
    public function birth()
    {
        $class = get_class($this);
        return new $class(array('proto'=>Data::makeURI($this['uri'], $this['lang'], $this['owner'])));
    }

    /**
     * Родитель объекта
     * @return \Boolive\data\Entity|null
     */
    public function parent()
    {
        if ($this->_parent === false){
            $this->_parent = Data::object($this->getParentUri(), '', 0, null, null, true);
        }
        return $this->_parent;
    }

    /**
     * Прототип объекта
     * @return \Boolive\data\Entity|null
     */
    public function proto()
    {
        if ($this->_proto === false){
            if (isset($this['proto'])){
                $info = Data::getURIInfo($this['proto']);
                $this->_proto = Data::object($info['uri'], $info['lang'], $info['owner'], null, null, true);
            }else{
                $this->_proto = null;
            }
        }
        return $this->_proto;
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
        if ($s = Data::section($this['uri'], true)){
            $s->call($method, $args);
        }
    }

    /**
     * URI родителя
     * @return string|null Если родителя нет, то null. Пустая строка является корректным uri
     */
    public function getParentUri()
    {
        if (!isset($this->_parent_uri)){
            if (isset($this['uri'])){
                $names = F::splitRight('/',$this['uri']);
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
     * Имя объекта
     * @return string|null
     */
    public function getName()
    {
        if (!isset($this->_parent_uri)){
            if (isset($this['uri'])){
                $names = F::splitRight('/',$this['uri']);
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
    public function getValue()
    {
        $value = $this->offsetGet('value');
        if (!isset($value) && ($proto = $this->proto())){
            $value = $proto->getValue();
        }
        return $value;
    }

    /**
     * Уровень вложенности
     * Вычисляется по uri
     */
    public function getLevel()
    {
        return mb_substr_count($this['uri'], '/')+1;
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
        if ($this['is_file']){
            $file = $this->getDir($root);
            if ($this->_attribs['is_history']) $file.='_history_/'.$this->_attribs['date'].'_';
            return $file.$this->_attribs['value'];
        }else
        if (!isset($this->_attribs['value']) && $proto = $this->proto()){
            return $proto->getFile($root);
        }
        return null;
    }

    /**
     * Проверка, является ли объект файлом.
     * Проверяется с учетом прототипа
     * @return bool
     */
    public function isFile()
    {
        return !empty($this['is_file']) || (!isset($this['value']) && ($proto = $this->proto()) && $proto->isFile());
    }

    /**
     * Сравнение объектов по uri
     * @param \Boolive\data\Entity $entity
     * @return bool
     */
    public function isEqual($entity)
    {
        if (!$entity) return false;
        return ($this->offsetExists('uri') && $this->offsetGet('uri') == $entity->offsetGet('uri'));
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
    public function isChildOf($parent){
        return $parent['uri'].'/' == mb_substr($this['uri'],0,mb_strlen($parent['uri'])+1);
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
        $trace['_checked'] = $this->_checked;
        /*if ($this->_rename) */$trace['_rename'] = $this->_rename;
        //$trace['_proto'] = $this->_proto;
        //$trace['_parent'] = $this->_parent;
        $trace['_children'] = $this->_children;
        return $trace;
    }

    #################################################
    #                                               #
    #            Исполнение объекта                 #
    #                                               #
    #################################################

    /**
     * Запуск объекта
     * @param \Boolive\commands\Commands $commands Команды для исполнения в соответствующих сущностях
     * @param \Boolive\input\Input $input Входящие данные
     * @return null|string Результат выполнения контроллера
     */
    public function start(Commands $commands, Input $input)
    {
        // Команды и входящие данные запоминаем, чтобы использовать их и передавать подчиненным по требованию
        $this->_commands = $commands;
        $this->_input = $input->getCopy($this->getInputRule());
        //Проверка возможности работы
        if ($this->canWork()){
            // Подчиненные не запускались ещё
            $this->_input['previous'] = false;
            //Выполнение подчиненных
            ob_start();
                // Выполнение своей работы
                $result = $this->work();
                $result = ob_get_contents().$result;
            ob_end_clean();
        }else{
            $result = null;
        }
        $this->_input = null;
        return $result;
    }

    /**
     * Проверка возможности работы.
     * По умолчанию проверяются входящие данные на соответсвие правилу.
     * @return bool Признак, может ли работать объект или нет
     */
    public function canWork()
    {
        return $this->_input->filter();
    }

    /**
     * Работа объекта. Обработка запроса и формирование вывода.
     * Результат выводится функциями echo, print или возвращается через return
     * @return string|void Результат работы. Вместо return можно использовать вывод строк (echo, print,...)
     */
    public function work()
    {
        return (string)$this;
    }

    /**
     * Запуск подчиненного по имени
     * @param $name Имя подчиненного
     * @return null|string
     */
    public function startChild($name)
    {
        $result = $this->{$name}->start($this->_commands, $this->_input);
        if ($result){
            $this->_input['previous'] = true;
        }
        return $result;
    }

    /**
     * Запуск всех подчиненных объектов
     * @return array Результаты подчиненных объектов. Ключи массива - названия объектов.
     */
    public function startChildren()
    {
        $result = array();
        $list = $this->findAll(array('where'=>'is_history=0 AND is_delete=0 AND is_hidden=0', 'order'=>'`order` ASC'));
        foreach ($list as $key => $child){
            /** @var $child \Boolive\data\Entity */
            $out = $child->start($this->_commands, $this->_input);
            if ($out){
                $result[$key] = $out;
                $this->_input['previous'] = true;
            }
        }
        return $result;
    }
}
