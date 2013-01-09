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

class Entity implements ITrace/*, IteratorAggregate, ArrayAccess, Countable*/
{
    /** @const int Максимальное порядковое значение */
    const MAX_ORDER = 4294967296;
    /** @var array Атрибуты */
    protected $_attribs = array(
        'uri'          => null,
        'id'           => null,
        'name'         => 'entity',
        'owner'		   => null,
        'lang'		   => null,
        'order'		   => 0,
        'date'		   => 0,
        'parent'       => null,
        'proto'        => null,
        'value'	 	   => '',
        'is_file'	   => 0,
        'is_history'   => 0,
        'is_delete'	   => 0,
        'is_hidden'	   => 0,
        'is_link'      => 0,
        'is_virtual'   => 1,
        'is_default_value' => 0,
        'is_default_class' => 0,
        'is_default_children' => 1,
        'is_accessible' => 1,
        'is_exist' => 0,
        'index_depth' => 0,
        'index_step' => 0
    );
    /** @var array Подчиненные объекты (выгруженные из бд или новые, то есть не обязательно все существующие) */
    protected $_children = array();
    /** @var bool Признак, загружены ли все подчиненные? */
    private $_all_children = false;
    /** @var \Boolive\values\Rule Правило для проверки атрибутов */
    protected $_rule;
    /** @var \Boolive\data\Entity Экземпляр прототипа */
    protected $_proto = false;
    /** @var \Boolive\data\Entity Экземпляр родителя */
    protected $_parent = false;
    /** @var \Boolive\data\Entity Экземпляр владельца */
    protected $_owner = false;
    /** @var \Boolive\data\Entity Экземпляр языка */
    protected $_lang = false;
    /** @var bool Принзнак, объект в процессе сохранения? */
    protected $_is_saved = false;
    /** @var bool Признак, изменены ли атрибуты объекта */
    protected $_changed = false;
    /** @var bool Признак, проверен ли объект или нет */
    protected $_checked = false;
    /** @var string uri родителя */
    //protected $_parent_uri = null;
    /**
     * Признак, требуется ли подобрать уникальное имя перед сохранением или нет?
     * Также означает, что текущее имя (uri) объекта временное
     * Если строка, то определяет базовое имя, к кторому будут подбираться числа для уникальности
     * @var bool|string
     */
    protected $_rename = false;

    /**
     * Конструктор
     * @param array $attribs
     */
    public function __construct($attribs = array())
    {
        $this->_attribs = array_replace($this->_attribs, $attribs);
    }

    /**
     * Установка правила на атрибуты
     */
    protected function defineRule()
    {
        $this->_rule = Rule::arrays(array(
                'id'           => Rule::uri(), // Сокращенный или полный URI
                'name'         => Rule::string()->regexp('|^[^/@]*$|')->max(50)->required(), // Имя объекта
                'owner'		   => Rule::uri(), // Владелец (идентификатор объекта-пользователя)
                'lang'		   => Rule::uri(), // Язык (идентификатор объекта-языка)
                'order'		   => Rule::int(), // Порядковый номер. Уникален в рамках родителя
                'date'		   => Rule::int(), // Дата создания в секундах. Версия объекта
                'parent'       => Rule::uri(), // URI родителя
                'proto'        => Rule::uri(), // URI прототипа
                'value'	 	   => Rule::string(), // Значение любой длины
                'is_file'	   => Rule::bool()->int(), // Связан ли с файлом (значение = имя файла). Путь на файл по uri
                'is_history'   => Rule::bool()->int()->default(0)->required(), // В истории или нет
                'is_delete'	   => Rule::bool()->int(), // Удаленный или нет
                'is_hidden'	   => Rule::bool()->int(), // Скрытый или нет
                'is_link'      => Rule::bool()->int(), // Ссылка или нет
                'is_virtual'   => Rule::bool()->int(), // Виртуальный или нет
                'is_default_value' => Rule::uri(), // Используется значение прототипа или оно переопределено?
                'is_default_class' => Rule::uri(), // Используется класс прототипа или свой?
                'is_default_children' => Rule::bool()->int(), // Используются свойства прототипа или нет?
                // Сведения о загружаемом файле. Не является атрибутом объекта
                'file'	=> Rule::arrays(array(
                        'tmp_name'	=> Rule::string(), // Путь на связываемый файл
                        'name'		=> Rule::lowercase()->ospatterns('*.*')->required()->ignore('lowercase'), // Имя файла, из которого будет взято расширение
                        'size'		=> Rule::int(), // Размер в байтах
                        'error'		=> Rule::int()->eq(0, true) // Код ошибки. Если 0, то ошибки нет
                    )
                ),
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
     * Идентификатор объекта. Сокращенный URI
     * @return null
     */
    public function id()
    {
        return isset($this->_attribs['id'])? $this->_attribs['id'] : null;
    }

    /**
     * Имя объекта
     * @param null $new_name
     * @param bool $choose_unique
     * @return mixed
     */
    public function name($new_name = null, $choose_unique = false)
    {
        // Смена имени
        if (isset($new_name) && ($this->_attribs['name'] != $new_name || $choose_unique)){
            if ($choose_unique){
                $this->_rename = $new_name;
                $new_name = uniqid($new_name);
            }
            $this->_attribs['name'] = $new_name;

            if (isset($this->_attribs['uri'])){
                $uri = F::splitRight('/',$this->_attribs['uri']);
                $this->updateURI(is_null($uri[0])? $new_name : $uri[0].'/'.$new_name);
            }else{
                $this->updateURI(null);
            }
            $this->_changed = true;
            $this->_checked = false;
        }
        return $this->_attribs['name'];
    }

    /**
     * URI объекта
     * @return mixed
     */
    public function uri()
    {
        if (!isset($this->_attribs['uri'])){
            if ($parent = $this->parent()){
               $this->_attribs['uri'] = $parent->uri().'/'.$this->_attribs['name'];
            }else{
                $this->_attribs['uri'] = $this->_attribs['name'];
            }
        }
        return $this->_attribs['uri'];
    }


    /**
     * Ключ
     * Полный или сокращенный URI в зависимости от их наличия
     * @return mixed|string
     */
    public function key()
    {
        return isset($this->_attribs['id']) ? $this->_attribs['id'] : $this->uri();
    }

        /**
     * Каскадное обновление URI подчиненных на основании своего uri
     * Обновляются uri только выгруженных/присоединенных на данный момент подчиенных
     * @param $uri Свой новый URI
     */
    private function updateURI($uri)
    {
        $this->_attribs['uri'] = $uri;
        foreach ($this->_children as $child_name => $child){
            /* @var \Boolive\data\Entity $child */
            $child->updateURI(is_null($uri)? null : $uri.'/'.$child_name);
        }
    }

    /**
     * Дата изменения или версия объекта.
     * Если история изменения объекта не ведется, то является датой создания объекта
     * @return mixed
     */
    public function date()
    {
        if (isset($this->_attribs['date'])){
            $this->_attribs['date'] = time();
        }
        return $this->_attribs['date'];
    }

    /**
     * Порядковое значение объекта
     * @param null $new_order Новое значение. Если с указыннм порядковым номером имеется объект, то он будет смещен
     * @return mixed
     */
    public function order($new_order = null)
    {
        if (isset($new_order)){
            $this->_attribs['order'] = $new_order;
        }
        return isset($this->_attribs['order'])? $this->_attribs['order'] : null;
    }

    /**
     * Значение
     * @param null|string $new_value Новое значение. Устнавливается если не null
     * @return string
     */
    public function value($new_value = null)
    {
        // Установка значения
        if (isset($new_value) && (!isset($this->_attribs['value']) || $this->_attribs['value']!=$new_value)){
            $this->_attribs['value'] = $new_value;
            $this->_attribs['is_default_value'] = 0;
            $this->_changed = true;
            $this->_checked = false;
        }
        // Возврат значения
        if (!isset($this->_attribs['value'])){
            return null;
        }else
        if ($this->isFile()){
            // Значение в файле
            if (mb_substr($this->_attribs['value'], mb_strlen($this->_attribs['value'])-6) == '.value'){
                try{
                    $this->_attribs['value'] = file_get_contents($this->file(null, true));
                }catch(Exception $e){
                    $this->_attribs['value'] = '';
                }
                $this->_attribs['is_file'] = false;
            }
        }
        return $this->_attribs['value'];
    }

    /**
     * Файл, ассоциированный с объектом
     * @param null|array|string $new_file Информация о новом файле. Полный путь к файлу или сведения из $_FILES
     * @param bool $root Возвращать полный путь или от директории сайта
     * @return null|string
     * @todo Учесть в пути владельца и язык объекта
     */
    public function file($new_file = null, $root = false)
    {
        // Установка нового файла
        if (isset($new_file)){
            if (empty($new_file)){
                unset($this->_attribs['file']);
            }else{
                if (is_string($new_file)){
                    $new_file = array(
                        'tmp_name'	=> $new_file,
                        'name' => basename($new_file),
                        'size' => @filesize($new_file),
                        'error'	=> is_file($new_file)? 0 : true
                    );
                }
            }
            $this->_attribs['file'] = $new_file;
            $this->_changed = true;
            $this->_checked = false;
        }
        // Возврат пути к текущему файлу, если есть
        if (!empty($this->_attribs['is_file'])){
            if ($proto = $this->isDefaultValue(null, true)){
                return $proto->file(null, $root);
            }else{
                $file = $this->dir($root);
                if (!empty($this->_attribs['is_history'])) $file.='_history_/'.$this['date'].'_';
                return $file.$this->_attribs['value'];
            }
        }
        return null;
    }

    /**
     * Директория объекта
     * @param bool $root Признак, возвращать путь от корня сервера или от web директории (www)
     * @return string
     */
    public function dir($root = false)
    {
        if ($root){
            return DIR_SERVER_PROJECT.ltrim($this->uri().'/','/');
        }else{
            return DIR_WEB_PROJECT.ltrim($this->uri().'/','/');
        }
    }

    /**
     * Признак, является занчение файлом или нет?
     * @return bool
     */
    public function isFile()
    {
        return !empty($this->_attribs['is_file']);
    }

    /**
     * Признак, объект в истории или нет?
     * @param null|bool $history Новое значение, если не null
     * @return bool
     */
    public function isHistory($history = null)
    {
        if (isset($delete) && (empty($this->_attribs['is_history']) == $history)){
            $this->_attribs['is_history'] = $history;
            $this->_changed = true;
            $this->_checked = false;
        }
        return !empty($this->_attribs['is_history']);
    }

    /**
     * Признак, объект удален или нет?
     * @param null|bool $delete Новое значение, если не null
     * @return bool
     */
    public function isDelete($delete = null)
    {
        if (isset($delete) && (empty($this->_attribs['is_delete']) == $delete)){
            $this->_attribs['is_delete'] = $delete;
            $this->_changed = true;
            $this->_checked = false;
        }
        return !empty($this->_attribs['is_delete']);
    }

    /**
     * Признак, скрытый объект или нет?
     * @param null|bool $hide Новое значение, если не null
     * @return bool
     */
    public function isHidden($hide = null)
    {
        if (isset($hide) && (empty($this->_attribs['is_hidden']) == $hide)){
            $this->_attribs['is_hidden'] = $hide;
            $this->_changed = true;
            $this->_checked = false;
        }
        return !empty($this->_attribs['is_hidden']);
    }

    /**
     * Признак, объект является ссылкой или нет?
     * @param null|bool $linked Новое значение, если не null
     * @return bool
     */
    public function isLink($linked = null)
    {
        if (isset($linked) && (empty($this->_attribs['is_link']) == $linked)){
            $this->_attribs['is_link'] = $linked;
            $this->_changed = true;
            $this->_checked = false;
        }
        return !empty($this->_attribs['is_link']);
    }

    /**
     * Признак, виртуальный объект или нет?
     * Виртуальные объекты образуются автоматически за счет наследования
     * @return bool
     */
    public function isVirtual()
    {
        return !empty($this->_attribs['is_virtual']);
    }

    /**
     * Признак, сущесвтует объект или нет?
     * @return bool
     */
    public function isExist()
    {
        return !empty($this->_attribs['is_exist']);
    }

    /**
     * Признак, доступен объект или нет для совершения указываемого действия над ним
     * Доступность проверяется для текущего пользователя
     * @param string $action Название действия. По умолчанию дейсвте чтения объекта.
     * @return bool
     */
    public function isAccessible($action = 'read')
    {
        if (!empty($this->_attribs['is_accessible'])){
            if ($action != 'read'){
                return $this->verify(Auth::getUser()->getAccessCond($action));
            }
            return true;
        }
        return false;
    }

    /**
     * Признак, наследуется ли значение от прототипа и от кого именно
     * @param null $is_default Новое значение признака. Для отмены значения по умолчанию необходимое изменить само значение.
     * @param $return_proto Если значение по умолчанию, то возвращать прототип, чьё значение наследуется или true?
     * @return bool|Entity
     */
    public function isDefaultValue($is_default = null, $return_proto = false)
    {
        if (isset($is_default) && (empty($this->_attribs['is_default_value']) == $is_default)){
            if ($is_default){
                // Поиск прототипа, от котоого наследуется значение, чтобы взять его значение
                if ($proto = $this->proto()){
                    if ($p = $proto->isDefaultValue(null, true)) $proto = $p;
                }
                if (isset($proto) && $proto->isExist()){
                    $this->_attribs['is_default_value'] = $proto->id();
                    $this->_attribs['value'] = $proto->value();
                    $this->_attribs['is_file'] = $proto->isFile();
                    $this->_changed = true;
                    $this->_checked = false;
                }
            }
        }
        if (!empty($this->_attribs['is_default_value']) && $return_proto){
            // Поиск прототипа, от котоого наследуется значение, чтобы возратить его
            return Data::read($this->_attribs['is_default_value'], $this->owner(), $this->lang());
        }else{
            return !empty($this->_attribs['is_default_value']);
        }
    }

    /**
     * Признак, используется класс прототипа или свой?
     * @param null $is_default
     * @param bool $return_proto
     * @return bool
     */
    public function isDefaultClass($is_default = null, $return_proto = false)
    {
        if (isset($is_default) && (empty($this->_attribs['is_default_class']) == $is_default)){
            if ($is_default){
                // Поиск прототипа, от котоого наследуется значение, чтобы взять его значение
                if ($proto = $this->proto()){
                    if ($p = $proto->isDefaultClass(null, true)) $proto = $p;
                }
                if (isset($proto) && $proto->isExist()){
                    $this->_attribs['is_default_class'] = $proto->id();
                }else{
                    $this->_attribs['is_default_class'] = 4294967295;
                }
            }else{
                $this->_attribs['is_default_class'] = 0;
            }
            $this->_changed = true;
            $this->_checked = false;
        }
        if (!empty($this->_attribs['is_default_class']) && $return_proto){
            if ($this->_attribs['is_default_class']==4294967295){
                return new Entity(array('id'=>4294967295));
            }
            // Поиск прототипа, от котоого наследуется значение, чтобы возратить его
            return Data::read($this->_attribs['is_default_class'], $this->owner(), $this->lang());
        }else{
            return !empty($this->_attribs['is_default_class']);
        }
    }

    /**
     * Признак, наследуются ли свойства от прототипов
     * @param null $is_default
     * @return bool
     */
    public function isDefaultChildren($is_default = null)
    {
        if (isset($is_default) && (empty($this->_attribs['is_default_children']) == $is_default)){
            $this->_attribs['is_default_children'] = $is_default;
            $this->_changed = true;
            $this->_checked = false;
        }
        return !empty($this->_attribs['is_default_children']);
    }

    /**
     * Все атрибуты объекта
     * @return array
     */
    public function attributes()
    {
        return $this->_attribs;
    }

    #################################################
    #                                               #
    #        Отношения с другими объектами          #
    #                                               #
    #################################################

    /**
     * Родитель объекта
     * @param null|Entity $new_parent Новый родитель. Чтобы удалить родителя, указывается false
     * @return \Boolive\data\Entity|null
     */
    public function parent($new_parent = null)
    {
        // Смена родителя
        if (isset($new_parent) && (empty($new_parent)&&!empty($this->_attribs['parent']) || !$new_parent->isEqual($this->parent()))){
            if (empty($new_parent)){
                // Удаление прототипа
                $this->_attribs['parent'] = null;
                $this->_attribs['parent_cnt'] = 0;
                $this->_parent = null;
                $this->updateURI($this->name());
            }else{
                // Смена прототипа

                $this->_attribs['parent'] = $new_parent->id();
                $this->_attribs['parent_cnt'] = $new_parent->parentCount() + 1;
                $this->_parent = $new_parent;
                // Установка атрибутов, зависимых от прототипа
                if ($new_parent->isLink() || !isset($this->_attribs['is_link'])) $this->_attribs['is_link'] = 1;
                // Обновление доступа
                if (!$new_parent->isAccessible() || !isset($this->_attribs['is_accessible'])) $this->_attribs['is_accessible'] = $new_parent->isAccessible();
                $this->updateURI($new_parent->uri().'/'.$this->name());
            }
            $this->_changed = true;
            $this->_checked = false;
        }
        // Возврат объекта-родителя
        if ($this->_parent === false){
            if (isset($this->_attribs['parent'])){
                $this->_parent = Data::read($this->_attribs['parent'], $this->_attribs['owner'], $this->_attribs['lang']);
            }else{
                $this->_parent = null;
            }
        }
        return $this->_parent;
    }

    /**
     * Количество родителей у объекта. Уровень вложенности
     * @return int
     */
    public function parentCount()
    {
        if (!isset($this->_attribs['parent_cnt'])){
            if (isset($this->_attribs['uri'])){
                $this->_attribs['parent_cnt'] = mb_substr_count($this->_attribs['uri'], '/');
            }else
            if ($parent = $this->parent()){
                $this->_attribs['parent_cnt'] = $parent->parentCount() + 1;
            }else{
                $this->_attribs['parent_cnt'] = 0;
            }
        }
        return $this->_attribs['parent_cnt'];
    }

    /**
     * URI родителя
     * Если известен свой URI, то URI родителя определяется без обращения к родителю
     * @return string|null Если родителя нет, то null. Пустая строка является корректным uri
     */
    public function parentUri()
    {
        if (!isset($this->_attribs['parent_uri'])){
            $uri = $this->uri();
            $names = F::splitRight('/', $uri);
            $this->_attribs['parent_uri'] = $names[0];
        }
        return $this->_attribs['parent_uri'];
    }

    /**
     * Имя родителя
     * Если известен свой URI, то имя родителя определяется без обращения к родителю
     * @return string
     */
    public function parentName()
    {
        if ($parent_uri = $this->parentUri()){
            $names = F::splitRight('/', $parent_uri);
            return $names[1];
        }
        return '';
    }

    /**
     * Прототип объекта
     * @param null|Entity $new_proto Новый прототип. Чтобы удалить прототип, указывается false
     * @return \Boolive\data\Entity|null
     */
    public function proto($new_proto = null)
    {
        // Смена прототипа
        if (isset($new_proto) && (empty($new_proto)&&!empty($this->_attribs['proto']) || !$new_proto->isEqual($this->proto()))){
            if (empty($new_proto)){
                // Удаление прототипа
                $this->_attribs['proto'] = null;
                $this->_attribs['proto_cnt'] = 0;
                $this->_attribs['is_default_value'] = 0;
                $this->_proto = null;
            }else{
                // Смена прототипа
                $this->_attribs['proto'] = $new_proto->id();
                $this->_attribs['proto_cnt'] = $new_proto->protoCount() + 1;
                $this->_proto = $new_proto;
                // Установка атрибутов, зависимых от прототипа
                if ($new_proto->isLink() || !isset($this->_attribs['is_link'])) $this->_attribs['is_link'] = 1;
                // Обновление доступа
                if (!$new_proto->isAccessible() || !isset($this->_attribs['is_accessible'])) $this->_attribs['is_accessible'] = $new_proto->isAccessible();
                // Наследование значения
                if ($this->isDefaultValue()){
                    $this->_attribs['value'] = $new_proto->value();
                    $this->_attribs['is_file'] = $new_proto->isFile();
                    if ($vp = $new_proto->isDefaultValue(null, true)){
                        $this->_attribs['is_default_value'] = $vp->id();
                    }else{
                        $this->_attribs['is_default_value'] = $new_proto->id();
                    }
                }
            }
            $this->_changed = true;
            $this->_checked = false;
        }
        // Возврат объекта-прототипа
        if ($this->_proto === false){
            if (isset($this->_attribs['proto'])){
                $this->_proto = Data::read($this->_attribs['proto'], $this->_attribs['owner'], $this->_attribs['lang']);
            }else{
                $this->_proto = null;
            }
        }
        return $this->_proto;
    }

    /**
     * Количество прототипов у объекта. Уровень наследования.
     * @return int
     */
    public function protoCount()
    {
        if (!isset($this->_attribs['proto_cnt'])){
            if ($proto = $this->proto()){
                $this->_attribs['proto_cnt'] = $proto->protoCount() + 1;
            }else{
                $this->_attribs['proto_cnt'] = 0;
            }
        }
        return $this->_attribs['proto_cnt'];
    }

    /**
     * Владелец объекта
     * @param null|Entity $new_owner Новый владелец. Чтобы сделать общим, указывается false
     * @return \Boolive\data\Entity|null
     */
    public function owner($new_owner = null)
    {
        // Смена владельца
        if (isset($new_owner) && (empty($new_owner)&&!empty($this->_attribs['owner']) || !$new_owner->isEqual($this->owner()))){
            if (empty($new_owner)){
                // Удаление языка
                $this->_attribs['owner'] = null;
                $this->_owner = null;
            }else{
                $this->_attribs['owner'] = $new_owner->id();
                $this->_owner = $new_owner;
            }
            $this->_changed = true;
            $this->_checked = false;
        }
        // Возврат объекта-владельца
        if ($this->_owner === false){
            if (isset($this->_attribs['owner'])){
                $this->_owner = Data::read($this->_attribs['owner'], $this->_attribs['owner'], $this->_attribs['lang']);
            }else{
                $this->_owner = null;
            }
        }
        return $this->_owner;
    }

    /**
     * Язык объекта
     * @param null|Entity $new_lang Новый язык. Чтобы сделать общим, указывается false
     * @return \Boolive\data\Entity|null
     */
    public function lang($new_lang = null)
    {
        // Смена языка
        if (isset($new_lang) && (empty($new_lang)&&!empty($this->_attribs['lang']) || !$new_lang->isEqual($this->lang()))){
            if (empty($new_lang)){
                // Удаление языка
                $this->_attribs['lang'] = null;
                $this->_owner = null;
            }else{
                $this->_attribs['lang'] = $new_lang->id();
                $this->_lang = $new_lang;
            }
            $this->_changed = true;
            $this->_checked = false;
            $this->_changed = true;
            $this->_checked = false;
        }
        // Возврат объекта-языка
        if ($this->_lang === false){
            if (isset($this->_attribs['lang'])){
                $this->_lang = Data::read($this->_attribs['lang'], $this->_attribs['owner'], $this->_attribs['lang']);
            }else{
                $this->_lang = null;
            }
        }
        return $this->_lang;
    }

    /**
     * Объект, на которого ссылется данный, если является ссылкой
     * Если данный объект не является ссылкой, то возарщается $this,
     * иначе возвращается первый из прототипов, не являющейся ссылкой
     * @param bool $clone Клонировать, если объект является ссылкой?
     * @return \Boolive\data\Entity
     */
    public function linked($clone = false)
    {
        if (empty($this->_attribs['is_link'])) return $this;
        if ($proto = $this->proto()){
            $proto = $proto->linked();
            if ($clone) $proto = clone $proto;
            return $proto;
        }
        return $this;
    }

    #################################################
    #                                               #
    #     Управление подчинёнными (свойствами)      #
    #                                               #
    #################################################

    /**
     * Получение подчиненного объекта по имени с учётом владельца и языка его родителя
     * @example $sub = $obj->sub;
     * @param string $name Имя подчиенного объекта
     * @return \Boolive\data\Entity
     */
    public function __get($name)
    {
        if (isset($this->_children[$name])){
            return $this->_children[$name];
        }else{
            if (!$this->isExist()){
                if (($p = $this->proto()) && $p->{$name}->isExist()){
                    $obj = $p->{$name}->birth($this);
                }else{
                    $obj = new Entity(array('owner'=>$this->_attribs['owner'], 'lang'=>$this->_attribs['lang']));
                }
            }else{
                $obj = Data::read(array($this, $name), $this->_attribs['owner'], $this->_attribs['lang']);
            }
            if (!$obj->isExist()){
                $obj->_attribs['name'] = $name;
                $obj->_attribs['uri'] = $this->uri().'/'.$name;
            }else{

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
     * @param Entity $value
     * @return \Boolive\data\Entity
     */
    public function __set($name, $value)
    {
        if ($value instanceof Entity){
            // Именование
            // Если имя неопределенно, то потрубуется подобрать уникальное автоматически при сохранении
            // Перед сохранением используется временное имя
            if (is_null($name)){
                $name = uniqid($value->_attribs['name']);
                $value->name($value->_attribs['name'], true);
            }else{
                if ($this->uri().'/'.$name != $value->uri()){
                    $value->name($name, true);
                }
//                if (!$this->isEqual($value->parent())){
//
//                }else{
//                    $value->name($name);
//                }
            }
            // Установка себя в качетсве родителя
            $value->parent($this);
            // В список загруженный подчиенных
            $this->_children[$name] = $value;
            return $value;
        }else{
            // Установка значения для подчиненного
            $this->__get($name)->value($value);
            return $this->__get($name);
        }
    }

    /**
     * Проверка, имеется ли подчиненный с именем $name в списке выгруженных?
     * @example $result = isset($object->sub);
     * @param $name Имя подчиненного объекта
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->_children[$name]);
    }

    /**
     * Удаление подчиненного с именем $name из списка выгруженных
     * @example unset($object->sub);
     * @param $name Имя подчиненного объекта
     */
    public function __unset($name)
    {
        unset($this->_children[$name]);
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
     * Поиск подчиненных объектов
     * @param array $cond Условие поиска
     * @see https://github.com/Boolive/Boolive/issues/7
     * @example
     * $cond = array(
     *     'where' => array(                            // услвоия поиска объединенные логическим AND
     *         array('attr', 'uri', '=', '?'),          // сравнение атрибута
     *         array('not', array(                      // отрицание всех условий
     *             array('attr', 'value', '=', '%?%')
     *         )),
     *         array('any', array(                      // услвоия объединенные логическим OR
     *             array('child', array(                // проверка свойства искомого объекта
     *                 array('attr', 'value', '>', 10),
     *                 array('attr', 'value', '<', 100),
     *             ))
     *         )),
     *         array('is', '/Library/object')          // кем объект является? проверка наследования
     *     ),
     *     'order' => array(                           // сортировка
     *         array('uri', 'DESC'),                   // по атрибуту uri
     *         array('childname', 'value', 'ASC')      // по атрибуту value подчиненного с имененм childname
     *     ),
     *     'limit' => array(10, 15)                    // ограничение - выбирать с 10-го не более 15 объектов
     * );
     * @param null|string $keys Имя атрибута, значения которого использовать в качестве ключей массива результата
     * @param int $depth Глубина поиска
     * @return array
     */
    public function find($cond = array(), $keys = 'name', $depth = 1)
    {
        $all = (empty($cond) && $depth == 1);

        if ($all && $this->_all_children){
            if ($keys == 'name'){
                return $this->_children;
            }else
            if (empty($keys)){
                return array_values($this->_children);
            }
        }
        if ($this->isExist()){
            $cond['from'] = array($this, $depth);
            $result = Data::select($cond, $keys);
        }else
        if ($p = $this->proto()){
            $cond['from'] = array($p, $depth);
            $result = Data::select($cond, $keys);
            foreach ($result as $key => $obj){
                /** @var $obj Entity */
                $result[$key] = $obj->birth($this);
            }
        }else{
            return array();
        }
        // Если загружены все подчиенные, то запоминаем их для предотвращения повторных выборок
        if ($all){
            if ($keys == 'name'){
                $this->_children = $result;
            }else{
                foreach ($result as $obj){
                    $this->_children[$obj->name()] = $obj;
                }
            }
            $this->_all_children = true;
        }
        return $result;
    }

    /**
     * Список выгруженных подчиненных
     * @return array
     */
    public function children()
    {
        return $this->_children;
    }

    #################################################
    #                                               #
    #            Управление объектом                #
    #                                               #
    #################################################

    /**
     * Сохранение объекта в секции
     * @param bool $history Признак, создавать историю изменения объекта или нет?
     * @param bool $children Признак, сохранять подчиенных или нет?
     * @param null|Error $error Ошибки при сохранении объекта
     * @param bool $access Признак, проверять доступ на запись или нет?
     * @throws \Exception
     * @return bool
     */
    public function save($history = true, $children = true, &$error = null, $access = true)
    {
        if (!$this->_is_saved && $this->check($error, $children)){
            try{
                $this->_is_saved = true;
                // Сохранение родителя, если не сохранен или требует переименования
                if ($this->_parent){
                    if (!$this->_parent->isExist() || $this->_parent->_rename){
                        if (!$this->_parent->save(false, false, $parent_error)){
                            throw $parent_error;
                        }
                    }
                    $this->_attribs['parent'] = $this->_parent->id();
                }
                if ($this->_proto){
                    $this->_attribs['proto'] = $this->_proto->id();
                }
                if ($this->_owner){
                    $this->_attribs['owner'] = $this->_owner->id();
                }
                if ($this->_lang){
                    $this->_attribs['lang'] = $this->_lang->id();
                }

                // Если создаётся история, то нужна новая дата
                if ($history) $this->_attribs['date'] = time();
                // Сохранение себя
                if ($this->_changed && Data::write($this, $error, $access)){
                    if ($this->_rename){
                        $this->updateURI($this->_attribs['uri']);
                        $this->_rename = false;
                    }
                    $this->_changed = false;
                }
                // @todo Если было переименование из-за _rename, то нужно обновить uri подчиненных
                // Сохранение подчиененных
                if ($children){
                    $children = $this->children();
                    foreach ($children as $child){
                        /** @var \Boolive\data\Entity $child */
                        $child->save($history, true, $error_child, $access);
                    }
                }
                $this->_is_saved = false;
                return true;
            }catch (Exception $e){
                $this->_is_saved = false;
                throw $e;
            }
        }
        return false;
    }

    /**
     * @todo
     * Уничтожение объекта
     * Полностью удаляется объект и его подчиенные.
     */
    public function destroy()
    {

    }

    /**
     * Создание нового объекта прототипированием от себя
     * @param null|\Boolive\data\Entity $for Для кого создаётся новый объект?
     * @return \Boolive\data\Entity
     */
    public function birth($for = null)
    {
        $class = get_class($this);
        $attr = array(
            'name' => $this->name(),
            'order' => self::MAX_ORDER,
        );
        /** @var $obj Entity */
        $obj = new $class($attr);
        $obj->proto($this);
        $obj->owner($this->owner());
        $obj->lang($this->lang());
        $obj->isDefaultValue(true);
        $obj->isDefaultClass(true);
        $obj->isDefaultChildren(true);
        if ($this->isLink()) $this->_attribs['is_link'] = 1;
        if (isset($for)) $obj->parent($for);
        return $obj;
    }

    /**
     * Хранилище объекта
     * @return stores\MySQLStore|null
     */
    public function store()
    {
        $key = isset($this->_attribs['id']) ? $this->_attribs['id'] : $this->uri();
        return Data::getStore($key);
    }

    /**
     * Проверка корректности объекта по внутренним правилам объекта
     * Используется перед сохранением
     * @param null $errors Возвращаемый объект ошибки
     * @param bool $children Признак, проверять или нет подчиненных
     * @return bool Признак, корректен объект (true) или нет (false)
     */
    public function check(&$errors = null, $children = true)
    {
        // "Контейнер" для ошибок по атрибутам и подчиненным объектам
        $errors = new Error('Неверный объект', $this->uri());
        if ($this->_checked) return true;

        // Проверка и фильтр атрибутов
        $attribs = new Values($this->_attribs);
        $this->_attribs = array_replace($this->_attribs, $attribs->get($this->getRule(), $error));
        if ($error){
            $errors->_attribs->add($error->getAll());
        }
        // Проверка подчиненных
        if ($children){
            foreach ($this->_children as $child){
                $error = null;
                /** @var \Boolive\data\Entity $child */
                if (!$child->check($error)){
                    $errors->_children->add($error);
                }
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
         * if ($child->name() == 'bad_name'){
         *     // Так как ошибка из-за атрибута, то добавляем в $error->_attribs
         *     // Если бы проверяли подчиненного у $child, то ошибку записывали бы в $error->_children
         *	   $error->_attribs->name = new Error('Недопустимое имя', 'impossible');
         *     return false;
         * }
         */
        return true;
    }

    /**
     * Проверка объекта соответствию указанному условию
     * @param array $cond Условие как для поиска
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
                $value = $this{$cond[1]}();
                switch ($cond[2]){
                    case '=': return $value == $cond[3];
                    case '<': return $value < $cond[3];
                    case '>': return $value > $cond[3];
                    case '>=': return $value >= $cond[3];
                    case '<=': return $value <= $cond[3];
                    case '!=':
                    case '<>': return $value != $cond[3];
                    case 'like':
                        $pattern = strtr($cond[3], array('%' => '*', '_' => '?'));
                        return fnmatch($pattern, $value);
                    case 'in':
                        if (!is_array($cond[3])) $cond[3] = array($cond[3]);
                        return in_array($value, $cond[3]);
                }
                return false;
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
            // Является частью чего-то с учётом наследования
            // Например заголовок статьи story1 является его частью и частью эталона статьи
            // Пример из жизни: Ветка является частью дерева, но не конкретезируется, какого именно
            case 'of':
                if (!is_array($cond[1])) $cond[1] = array($cond[1]);
                foreach ($cond[1] as $obj){
                    if ($this->of($obj)) return true;
                }
                return false;
            // Неподдерживаемые условия
            default: return false;
        }
    }

    /**
     * Проверка, является ли подчиненным для указанного родителя?
	 * @param string|\Boolive\data\Entity $parent Экземпляр родителя или его идентификатор
     * @return bool
     */
    public function in($parent)
    {
        if (!$parent instanceof Entity){
            $parent = Data::read($parent);
        }
        return $parent->uri().'/' == mb_substr($this->uri(),0,mb_strlen($parent->uri())+1);
    }

    /**
     * Проверка, являектся наследником указанного прототипа?
     * @param string|\Boolive\data\Entity $proto Экземпляр прототипа или его идентификатор
     * @return bool
     */
    public function is($proto)
    {
        if (!$proto instanceof Entity){
            if ($proto == $this->uri() || $proto == $this->id()) return true;
            $proto = Data::read($proto);
        }
        if ($this->isEqual($proto)) return true;
        if (!$this->isExist()){
            return ($p = $this->proto()) ? $p->is($proto) : false;
        }
        return (bool)Data::select(array(
            'select' => 1,
            'from' => array($this, 0),
            'where' => array('is', $proto->key()),
            'limit' => array(0,1)
        ), null, null, false);
    }

    /**
     * Проверка, является ли частью указанного объекта.
     * Например, указанный объект $object может быть наследником для любого родителя проверяемого объекта $this
     * или, наоборот, указанный объект $object может быть родителем для любого прототипа проверяемого объекта $this.
     * Проверка объединяет в себе is() и in() с учётом рекурсивных отошений, образуемых между
     * родителями и прототипами всех родителей и прототипов объекта.
     * Пример из жизни: конкретный листок является частью дерева. Дерево абстрактно.
     * @param string|\Boolive\data\Entity $object Экземпляр или идентификатор
     * @return bool
     */
    public function of($object)
    {
        if (!$object instanceof Entity){
            if ($object == $this->uri() || $object == $this->id()) return true;
            $$object = Data::read($object);
        }
        if ($this->isEqual($object)) return true;
        if (!$this->isExist()){
            if (($p = $this->proto()) && $p->of($object)) return true;
            return ($p = $this->parent()) ? $p->of($object) : false;
        }
        return (bool)Data::select(array(
            'select' => 1,
            'from' => array($this, 0),
            'where' => array('of', $object->key()),
            'limit' => array(0,1)
        ), null, null, false);
    }

    /**
     * Сравнение объектов по uri
     * @param \Boolive\data\Entity $entity
     * @return bool
     */
    public function isEqual($entity)
    {
        if (!$entity instanceof Entity){
            return false;
        }
        return $this->key() == $entity->key()/* &&
               $this->_attribs['owner'] == $entity->_attribs['owner'] &&
               $this->_attribs['lang'] == $entity->_attribs['lang'] &&
               $this->date() == $entity->date()*/;
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
     * Признак, находится ли объект в процессе сохранения?
     * @return bool
     */
    public function isSaved()
    {
        return $this->_is_saved;
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
        return (string)$this->value();
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
    }

    /**
     * Клонирование объекта
     */
    public function __clone()
    {
        foreach ($this->_children as $name => $child){
            $this->_children[$name] = clone $child;
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
        $trace['_checked'] = $this->_checked;
        $trace['_rename'] = $this->_rename;
        //$trace['_proto'] = $this->_proto;
        //$trace['_parent'] = $this->_parent;
        $trace['_children'] = $this->_children;
        return $trace;
    }
}