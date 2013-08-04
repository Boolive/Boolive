<?php
/**
 * Сущность
 * Базовая логика для объектов модели данных.
 * @version 1.0
 * @link http://boolive.ru/createcms/data-and-entity
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\data;

use Exception,
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

class Entity implements ITrace
{
    /** @const int Максимальное порядковое значение */
    const MAX_ORDER = 4294967295;
    /** @const int Идентификатор сущности - эталона всех объектов */
    const ENTITY_ID = 4294967295;
    /** @const int Максимальная глубина для поиска */
    const MAX_DEPTH = 4294967295;
    /** @const int У объекта нет отличий */
    const DIFF_NO = 0;
    /** @const int Объекты отличаются атрибутами */
    const DIFF_CHANGE = 1;
    /** @const int Объет удален */
    const DIFF_DELETE = 2;
    /** @const int Объект добавлен */
    const DIFF_ADD = 3;

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
        'parent_cnt'   => 0,
        'proto'        => null,
        'proto_cnt'    => 0,
        'value'	 	   => '',
        'is_file'	   => 0,
        'is_history'   => 0,
        'is_delete'	   => 0,
        'is_hidden'	   => 0,
        'is_link'      => 0,
        'is_default_value' => 0,
        'is_default_class' => 0,
        'is_accessible' => 1,
        'is_exist' => 0,
        'update_step' => 0,
        'update_time' => 0,
        'diff' => 0,
        'diff_from' => 0
    );
    /** @var array Подчиненные объекты (выгруженные из бд или новые, не обязательно все существующие) */
    protected $_children = array();
    /** @var Rule Правило на атрибуты объекта */
    protected $_rule;
    /** @var Entity Экземпляр прототипа */
    protected $_proto = false;
    /** @var Entity Экземпляр родителя */
    protected $_parent = false;
    /** @var Entity Экземпляр владельца */
    protected $_owner = false;
    /** @var Entity Экземпляр языка */
    protected $_lang = false;
    /** @var Entity Экземпляр прототипа, на которого ссылается объект */
    protected $_link = false;
    /** @var Entity Экземпляр прототипа, от которого берется значение по умолчанию */
    protected $_default_value_proto = false;
    /** @var bool Принзнак, объект в процессе сохранения? */
    protected $_is_saved = false;
    /** @var bool Признак, изменены ли атрибуты объекта */
    protected $_changed = false;
    /** @var bool Признак, проверен ли объект или нет */
    protected $_checked = false;
    /** @var array Условие, которым был выбран объект */
    protected $_cond;
    /**
     * Признак, требуется ли подобрать уникальное имя перед сохранением или нет?
     * Также означает, что текущее имя (uri) объекта временное
     * Если строка, то определяет базовое имя, к кторому будут подбираться числа для уникальности
     * @var bool|string
     */
    protected $_autoname = false;

    /**
     * Конструктор
     * @param array $attribs Атрибуты объекта, а также атрибуты подчиенных объектов
     * @param int $children_depth До какой глубины (вложенности) создавать экземпляры подчиненных объектов
     */
    public function __construct($attribs = array(), $children_depth = 0)
    {
        if (!empty($attribs['id'])){
            $attribs['is_exist'] = true;
        }
        if (!isset($attribs['name']) && isset($attribs['uri'])){
            $names = F::splitRight('/', $attribs['uri'], true);
            $attribs['name'] = $names[1];
            if (!isset($attribs['parent'])){
                $attribs['parent'] = $names[0];
            }
        }
        if (isset($attribs['class'])) unset($attribs['class']);
        if (isset($attribs['cond'])){
            $this->_cond = $attribs['cond'];
            unset($attribs['cond']);
        }
        if (isset($attribs['children'])){
            if ($children_depth > 0){
                if ($children_depth != Entity::MAX_DEPTH) $children_depth--;
                foreach ($attribs['children'] as $name => $child){
                    $class = isset($child['class'])? $child['class'] : '\Boolive\data\Entity';
                    $child['cond'] = $this->_cond;
                    $this->_children[$name] = new $class($child, $children_depth);
                }
            }
            unset($attribs['children']);
        }

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
                'order'		   => Rule::int()->max(4294967295), // Порядковый номер. Уникален в рамках родителя
                'date'		   => Rule::int(), // Дата создания в секундах. Версия объекта
                'parent'       => Rule::uri(), // URI родителя
                'proto'        => Rule::uri(), // URI прототипа
                'value'	 	   => Rule::string(), // Значение любой длины
                'is_file'	   => Rule::bool()->int(), // Связан ли с файлом (value = имя файла, uri = директория)
                'is_history'   => Rule::bool()->int()->default(0)->required(), // В истории или нет?
                'is_delete'	   => Rule::int(), // Удаленный или нет с учётом признака родителя?
                'is_hidden'	   => Rule::int(), // Скрытый или нет с учётом признака родителя?
                'is_link'      => Rule::uri(), // Ссылка или нет?
                'is_default_value' => Rule::uri(), // Используется значение прототипа или оно переопределено?
                'is_default_class' => Rule::uri(), // Используется класс прототипа или свой?
                'diff'         => Rule::int(), // Код обнаруженных обновлений
                'diff_from'    => Rule::int(), // От куда обновления. 1 - от прототипа. 0 и меньше от info файла (кодируется относительное расположение файла)
                // Сведения о загружаемом файле. Не является атрибутом объекта
                'file'	=> Rule::arrays(array(
                        'tmp_name'	=> Rule::string(), // Путь на связываемый файл
                        'name'		=> Rule::lowercase()->ospatterns('*.*')->required()->ignore('lowercase'), // Имя файла, из которого будет взято расширение
                        'size'		=> Rule::int(), // Размер в байтах
                        'error'		=> Rule::int()->eq(0, true), // Код ошибки. Если 0, то ошибки нет
                        'type'      => Rule::string() // MIME тип файла
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
     * @param null $new_name Новое имя
     * @param bool $choose_unique Выбирать уникальное, если уже занято указанное?
     * @return string Имя объекта
     */
    public function name($new_name = null, $choose_unique = false)
    {
        if (!isset($new_name) && $choose_unique) $new_name = $this->_attribs['name'];
        // Смена имени
        if (isset($new_name) && ($this->_attribs['name'] != $new_name || $choose_unique)){
            if ($choose_unique){
                $this->_autoname = $new_name;
                //$new_name = uniqid($new_name);
            }
            $this->_attribs['name'] = $new_name;

//            if (isset($this->_attribs['uri'])){
//                $uri = F::splitRight('/',$this->_attribs['uri'], true);
//                $this->updateURI(is_null($uri[0])? $new_name : $uri[0].'/'.$new_name);
//            }else{
//                $this->updateURI(null);
//            }
            $this->_changed = true;
            $this->_checked = false;
        }
        return $this->_attribs['name'];
    }

    /**
     * URI объекта
     * @param bool $remake Признак, обновить URI? URI определяется по родителю и имени объекта
     * @param bool $encode Признак, кодировать спецсимволы в URI?
     * @return string
     */
    public function uri($remake = false, $encode = false)
    {
        if (!isset($this->_attribs['uri']) || $remake){
            if ($parent = $this->parent()){
               $this->_attribs['uri'] = $parent->uri().'/'./*($this->_autoname ? $this->_autoname : */$this->_attribs['name']/*)*/;
            }else{
                $this->_attribs['uri'] = /*$this->_autoname ? $this->_autoname : */$this->_attribs['name'];
            }
        }
        if ($encode){
            $uri = urlencode($this->_attribs['uri']);
            $uri = strtr($uri, array(
                         '%3A' => ':',
                         '%2F' => '/'
            ));
            return $uri;
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
     */
    protected function updateURI()
    {
        foreach ($this->_children as $child_name => $child){
            /* @var Entity $child */
            $child->_attribs['uri'] = $this->_attribs['uri'].'/'.$child_name;
            $child->updateURI();
        }
    }

    /**
     * Дата изменения или версия объекта.
     * Если история изменения объекта не ведется, то является датой создания объекта
     * @return mixed
     */
    public function date()
    {
        if (!isset($this->_attribs['date'])){
            $this->_attribs['date'] = time();
        }
        return (int)$this->_attribs['date'];
    }

    /**
     * Порядковое значение объекта
     * @param null $new_order Новое значение. Если с указыннм порядковым номером имеется объект, то он будет смещен
     * @return mixed
     */
    public function order($new_order = null)
    {
        if (isset($new_order) && (!isset($this->_attribs['order']) || $this->_attribs['order']!=$new_order)){
            $this->_attribs['order'] = $new_order;
            $this->_changed = true;
            $this->_checked = false;
        }
        return isset($this->_attribs['order'])? (int)$this->_attribs['order'] : null;
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
            $this->_attribs['is_file'] = false;
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
                $this->_attribs['is_file'] = 0;
            }else{
                if (is_string($new_file)){
                    $new_file = array(
                        'tmp_name'	=> $new_file,
                        'name' => basename($new_file),
                        'size' => @filesize($new_file),
                        'error'	=> is_file($new_file)? 0 : true
                    );
                }
                $this->_attribs['file'] = $new_file;
                $this->_attribs['is_file'] = 1;
            }
            $this->_attribs['is_default_value'] = 0;
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
        $dir = $this->uri();
        if (Data::isAbsoluteUri($dir)) return $dir.'/';
        if ($root){
            return DIR_SERVER_PROJECT.ltrim($dir.'/','/');
        }else{
            return DIR_WEB_PROJECT.ltrim($dir.'/','/');
        }
    }

    /**
     * Признак, является занчение файлом или нет?
     * @param null|bool $is_file Новое значение, если не null
     * @return bool
     */
    public function isFile($is_file = null)
    {
        if (isset($is_file) && (empty($this->_attribs['is_file']) == $is_file)){
            $this->_attribs['is_file'] = $is_file;
            $this->_changed = true;
            $this->_checked = false;
        }
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
     * @param bool $inherit_parent Учитывать или нет признак родителя. Если нет, то удаление родителя не влияет на данный объект
     * @return bool
     */
    public function isDelete($delete = null, $inherit_parent = true)
    {
        // Какой признак у родителя (чтобы его вычесть из своего)
        if ((!$inherit_parent || isset($delete)) && ($parent = $this->parent())){
            $p = $parent->_attribs['is_delete'];
        }else{
            $p = 0;
        }
        // Смена своего признака
        if (isset($delete) && ($this->_attribs['is_delete']-$p != ($delete = intval((bool)$delete)))){
            $this->_attribs['is_delete'] = $delete + $p;
            $this->_changed = true;
            $this->_checked = false;
        }
        return $inherit_parent ? !empty($this->_attribs['is_delete']) : ($this->_attribs['is_delete']-$p != 0);
    }

    /**
     * Признак, скрытый объект или нет?
     * @param null|bool $hide Новое значение, если не null
     * @param bool $inherit_parent Учитывать или нет признак родителя. Если нет, то скрытие родителя не влияет на данный объект
     * @return bool
     */
    public function isHidden($hide = null, $inherit_parent = true)
    {
        // Какой признак у родителя (чтобы его вычесть из своего)
        if ((!$inherit_parent || isset($hide)) && ($parent = $this->parent())){
            $p = $parent->_attribs['is_hidden'];
        }else{
            $p = 0;
        }
        // Смена своего признака
        if (isset($hide) && ($this->_attribs['is_hidden']-$p != ($hide = intval((bool)$hide)))){
            $this->_attribs['is_hidden'] = $hide + $p;
            $this->_changed = true;
            $this->_checked = false;
        }
        return $inherit_parent ? !empty($this->_attribs['is_hidden']) : ($this->_attribs['is_hidden']-$p != 0);
    }

    /**
     * Признак, объект является ссылкой или нет?
     * @param null|bool $is_link Новое значение, если не null
     * @param bool $return_link Признак, возвращать или нет объект, на которого ссылается данный
     * @return bool|Entity
     */
    public function isLink($is_link = null, $return_link = false)
    {
        if (isset($is_link)){
            $curr = $this->_attribs['is_link'];
            if ($is_link){
                // Поиск прототипа, от которого наследуется значение, чтобы взять его значение
                if (($proto = $this->proto())){
                    if ($p = $proto->isLink(null, true)) $proto = $p;
                }
                if (isset($proto) && $proto->isExist()){
                    if ($proto->store() != $this->store()){
                        $this->_attribs['is_link'] = $proto->uri();
                    }else{
                       $this->_attribs['is_link'] = $proto->key();
                    }
                }else{
                    $this->_attribs['is_link'] = self::ENTITY_ID;
                }
            }else{
                $this->_attribs['is_link'] = 0;
            }
            if ($curr !== $this->_attribs['is_link']){
                $this->_changed = true;
                $this->_checked = false;
            }
            if ($this->isDefaultClass()) $this->isDefaultClass(true);
        }
        // Возвращение признака или объекта, на которого ссылается данный объект
        if (!empty($this->_attribs['is_link']) && $return_link){
            if ($this->_link === false){
                $this->_link = Data::read(array(
                    'from' => $this,
                    'select' => 'link',
                    'depth' => array(0,0),
                    'owner' => $this->_attribs['owner'],
                    'lang' => $this->_attribs['lang'],
                    'comment' => 'read link',
                    'cache' => 2
                ));
            }
            return $this->_link;
        }else{
            return !empty($this->_attribs['is_link']);
        }
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
                return !IS_INSTALL || $this->verify(Auth::getUser()->getAccessCond($action));
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
        if (isset($is_default)){
            $curr = $this->_attribs['is_default_value'];
            if ($is_default){
                // Поиск прототипа, от котоого наследуется значение, чтобы взять его значение
                if (($proto = $this->proto())/* && $proto->isLink() == $this->isLink()*/){
                    if ($p = $proto->isDefaultValue(null, true)) $proto = $p;
                }
                if (isset($proto) && $proto->isExist()){
                    if ($proto->store() != $this->store()){
                        $this->_attribs['is_default_value'] = $proto->uri();
                    }else{
                       $this->_attribs['is_default_value'] = $proto->key();
                    }
                    $this->_attribs['value'] = $proto->value();
                    $this->_attribs['is_file'] = $proto->isFile();
                }else{
                    $this->_attribs['is_default_value'] = self::ENTITY_ID;
                    $this->_attribs['value'] = '';
                    $this->_attribs['is_file'] = 0;
                }
            }else{
                $this->_attribs['is_default_value'] = 0;
            }
            if ($curr !== $this->_attribs['is_default_value']){
                $this->_changed = true;
                $this->_checked = false;
            }
        }
        if (!empty($this->_attribs['is_default_value']) && $return_proto){
            if ($this->_default_value_proto === false){
                // Поиск прототипа, от которого наследуется значение, чтобы возратить его
                $this->_default_value_proto = Data::read(array(
                    'from' => $this,
                    'select' => 'default_value_proto',
                    'depth' => array(0,0),
                    'owner' => $this->_attribs['owner'],
                    'lang' => $this->_attribs['lang'],
                    'comment' => 'read default value',
                    'cache' => 2
//                    'from'=>$this->_attribs['is_default_value'],
//                    'owner'=>$this->_attribs['owner'],
//                    'lang'=>$this->_attribs['lang'],
//                    'comment'=>'read default value',
//                    'cache' => 2
                ));
            }
            return $this->_default_value_proto;
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
        if (isset($is_default)){
            $curr = $this->_attribs['is_default_class'];
            if ($is_default){
                // Поиск прототипа, от которого наследуется значение, чтобы взять его значение
                if (($proto = $this->proto()) && $proto->isLink() == $this->isLink()){
                    if ($p = $proto->isDefaultClass(null, true)) $proto = $p;
                }else{
                    $proto = null;
                }
                if (isset($proto) && $proto->isExist()){
                    if ($proto->store() != $this->store()){
                        $this->_attribs['is_default_class'] = $proto->uri();
                    }else{
                       $this->_attribs['is_default_class'] = $proto->key();
                    }
                }else{
                    $this->_attribs['is_default_class'] = self::ENTITY_ID;
                }
            }else{
                $this->_attribs['is_default_class'] = 0;
            }
            if ($curr !== $this->_attribs['is_default_class']){
                $this->_changed = true;
                $this->_checked = false;
            }
        }
        if (!empty($this->_attribs['is_default_class']) && $return_proto){
            // Поиск прототипа, от котоого наследуется значение, чтобы возратить его
            return Data::read(array(
                'from' => $this->_attribs['is_default_class'],
                'owner' => $this->_attribs['owner'],
                'lang' => $this->_attribs['lang'],
                'comment' => 'read default class',
                'cache' => 2
            ));
        }else{
            return !empty($this->_attribs['is_default_class']);
        }
    }

    /**
     * Найденные отличия в объекте
     * @param null|int $diff
     * @return int Код отличия
     */
    public function diff($diff = null)
    {
        if (isset($diff)){
            $this->_attribs['diff'] = $diff;
            $this->_changed = true;
            $this->_checked = false;
        }
        return $this->_attribs['diff'];
    }

    /**
     * От куда найдены отличия в объекте?
     * @param null|int $diff_from
     * @return int Код отличия
     */
    public function diff_from($diff_from = null)
    {
        if (isset($diff_from)){
            $this->_attribs['diff_from'] = $diff_from;
            $this->_changed = true;
            $this->_checked = false;
        }
        return $this->_attribs['diff_from'];
    }

    /**
     * Все атрибуты объекта
     * @return array
     */
    public function attributes()
    {
        return $this->_attribs;
    }

    /**
     * Атрибут объекта по имени
     * Необходимо учитывать, что некоторые атрибуты могут быть ещё не инициалироваными
     * @param $name Назавние возвращаемого атрибута
     * @return mixed Значение атрибута
     */
    public function attr($name)
    {
        return $this->_attribs[$name];
    }

    #################################################
    #                                               #
    #        Отношения с другими объектами          #
    #                                               #
    #################################################

    /**
     * Родитель объекта
     * @param null|Entity $new_parent Новый родитель. Чтобы удалить родителя, указывается false
     * @param bool $load Загрузить родителя из хранилща, если ещё не загружен?
     * @return Entity|null
     */
    public function parent($new_parent = null, $load = true)
    {
        if (is_string($new_parent)) $new_parent = Data::read($new_parent);
        // Смена родителя
        if (isset($new_parent) && (empty($new_parent)&&!empty($this->_attribs['parent']) || !$new_parent->isEqual($this->parent()) || $this->_attribs['parent_cnt']!=$new_parent->parentCount()+1)){
            $is_delete = $this->isDelete(null, false);
            $is_hidden = $this->isHidden(null, false);
            if (empty($new_parent)){
                // Удаление родителя
                $this->_attribs['parent'] = null;
                $this->_attribs['parent_cnt'] = 0;
                $this->_parent = null;
                //$this->updateURI($this->name());
            }else{
                // Смена родителя
                $this->_parent = $new_parent;
                $this->_attribs['parent'] = $new_parent->key();
                $this->_attribs['parent_cnt'] = $new_parent->parentCount() + 1;
                // Установка атрибутов, зависимых от прототипа
                //if ($new_parent->isLink() || !isset($this->_attribs['is_link'])) $this->_attribs['is_link'] = 1;
                // Обновление доступа
                if (!$new_parent->isAccessible() || !isset($this->_attribs['is_accessible'])) $this->_attribs['is_accessible'] = $new_parent->isAccessible();
               // $this->updateURI($new_parent->uri().'/'.$this->name());
            }
            // Обновление зависимых от родителя признаков
            $this->isDelete($is_delete);
            $this->isHidden($is_hidden);
            $this->_changed = true;
            $this->_checked = false;
        }
        // Возврат объекта-родителя
        if ($this->_parent === false && $load){
            if (isset($this->_attribs['parent'])){
                $this->_parent = Data::read(array(
                    'from' => $this->_attribs['parent'],
                    'owner' => $this->_attribs['owner'],
                    'lang' => $this->_attribs['lang'],
                    'comment' => 'read parent',
                    'cache' => 2
                ));
                if (!$this->_parent->isExist()){
                    $this->_parent = null;
                }
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
            $names = F::splitRight('/', $uri, true);
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
            $names = F::splitRight('/', $parent_uri, true);
            return $names[1];
        }
        return '';
    }

    /**
     * Прототип объекта
     * @param null|Entity $new_proto Новый прототип. Чтобы удалить прототип, указывается false
     * @param bool $load Загрузить прототип из хранилща, если ещё не загружен?
     * @return Entity|null|bool
     */
    public function proto($new_proto = null, $load = true)
    {
        if (is_string($new_proto)) $new_proto = Data::read($new_proto);
//        if ($new_proto instanceof Entity && !$new_proto->isExist()) $new_proto = null;
        // Смена прототипа
        if (isset($new_proto) && (empty($new_proto)&&!empty($this->_attribs['proto']) || !$new_proto->isEqual($this->proto()))){
            if (empty($new_proto)){
                // Удаление прототипа
                $this->_attribs['proto'] = null;
                $this->_attribs['proto_cnt'] = 0;
                $this->_attribs['is_default_value'] = 0;
                if ($this->_attribs['is_default_class'] != 0){
                    $this->_attribs['is_default_class'] = self::ENTITY_ID;
                }
                if ($this->_attribs['is_link'] != 0){
                    $this->_attribs['is_link'] = self::ENTITY_ID;
                }
                $this->_proto = null;
            }else{
                // Наследование значения
                if ($this->isDefaultValue()){
                    $this->_attribs['value'] = $new_proto->value();
                    $this->_attribs['is_file'] = $new_proto->isFile();
                    if ($vp = $new_proto->isDefaultValue(null, true)){
                        $this->_attribs['is_default_value'] = $vp->key();
                    }else{
                        $this->_attribs['is_default_value'] = $new_proto->key();
                    }
                }
                // Смена прототипа
                if ($new_proto->store() != $this->store()){
                    $this->_attribs['proto'] = $new_proto->uri();
                }else{
                   $this->_attribs['proto'] = $new_proto->key();
                }
                $this->_attribs['proto_cnt'] = $new_proto->protoCount() + 1;
                $this->_proto = $new_proto;

                // Если объект ссылка или новый прототип ссылка, то обновление ссылки
                if ($this->isLink() || $new_proto->isLink()){
                    $this->isLink(true); //также обновляется класс
                }else
                // Обновление наследуемого класса
                if ($this->isDefaultClass()){
                    $this->isDefaultClass(true);
                }
                // Обновление доступа
                if (!$new_proto->isAccessible() || !isset($this->_attribs['is_accessible'])) $this->_attribs['is_accessible'] = $new_proto->isAccessible();
            }
            $this->_changed = true;
            $this->_checked = false;
        }
        // Возврат объекта-прототипа
        if ($this->_proto === false && $load){
            if (isset($this->_attribs['proto'])){
                $this->_proto = Data::read(array(
                    'from' => $this->_attribs['proto'],
                    'owner' => $this->_attribs['owner'],
                    'lang' => $this->_attribs['lang'],
                    'comment' => 'read proto',
                    'cache' => 2
                ));
                if (!$this->_proto instanceof Entity){
                    throw new Exception('NO PROTO '.$this->_attribs['proto']);
                }
                if (!$this->_proto->isExist()){
                    $this->_proto = null;
                }
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
     * @param bool $load Загрузить владельца из хранилща, если ещё не загружен?
     * @return Entity|null
     */
    public function owner($new_owner = null, $load = true)
    {
        if (is_string($new_owner)) $new_owner = Data::read($new_owner);
        // Смена владельца
        if (isset($new_owner) && (empty($new_owner)&&!empty($this->_attribs['owner']) || !$new_owner->isEqual($this->owner()))){
            if (empty($new_owner)){
                // Удаление языка
                $this->_attribs['owner'] = null;
                $this->_owner = null;
            }else{
                if ($new_owner->store() != $this->store()){
                    $this->_attribs['owner'] = $new_owner->uri();
                }else{
                   $this->_attribs['owner'] = $new_owner->key();
                }
                $this->_owner = $new_owner;
            }
            $this->_changed = true;
            $this->_checked = false;
        }
        // Возврат объекта-владельца
        if ($this->_owner === false && $load){
            if (isset($this->_attribs['owner'])){
                $this->_owner = Data::read(array(
                    'from' => $this->_attribs['owner'],
                    'owner' => $this->_attribs['owner'],
                    'lang' => $this->_attribs['lang'],
                    'comment' => 'read owner',
                    'cache' => 2
                ));
            }else{
                $this->_owner = null;
            }
        }
        return $this->_owner;
    }

    /**
     * Язык объекта
     * @param null|Entity $new_lang Новый язык. Чтобы сделать общим, указывается false
     * @param bool $load Загрузить язык из хранилща, если ещё не загружен?
     * @return Entity|null
     */
    public function lang($new_lang = null, $load = true)
    {
        if (is_string($new_lang)) $new_lang = Data::read($new_lang);
        // Смена языка
        if (isset($new_lang) && (empty($new_lang)&&!empty($this->_attribs['lang']) || !$new_lang->isEqual($this->lang()))){
            if (empty($new_lang)){
                // Удаление языка
                $this->_attribs['lang'] = null;
                $this->_owner = null;
            }else{
                if ($new_lang->store() != $this->store()){
                    $this->_attribs['lang'] = $new_lang->uri();
                }else{
                   $this->_attribs['lang'] = $new_lang->key();
                }
                $this->_lang = $new_lang;
            }
            $this->_changed = true;
            $this->_checked = false;
            $this->_changed = true;
            $this->_checked = false;
        }
        // Возврат объекта-языка
        if ($this->_lang === false && $load){
            if (isset($this->_attribs['lang'])){
                $this->_lang = Data::read(array(
                    'from' => $this->_attribs['lang'],
                    'owner' => $this->_attribs['owner'],
                    'lang' => $this->_attribs['lang'],
                    'comment' => 'read lang',
                    'cache' => 2
                ));
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
     * @return Entity
     */
    public function linked($clone = false)
    {
        if (empty($this->_attribs['is_link'])) return $this;
        if ($link = $this->isLink(null, true)){
            if ($clone) $link = clone $link;
            return $link;
        }
        return $this;
    }

    /**
     * Следующий объект
     */
    public function next()
    {
        if ($next = $this->parent()->find(array(
                'where' => array(
                    array('attr', 'order', '>', $this->order()),
                ),
                'order' => array(
                    array('order', 'ASC')
                ),
                'limit' => array(0,1),
                'comment' => 'read next object'
            )
        )){
            return $next[0];
        }
        return null;
    }

    /**
     * Предыдущий объект
     */
    public function prev()
    {
        if ($prev = $this->parent()->find(array(
                'where' => array(
                    array('attr', 'order', '<', $this->order()),
                ),
                'order' => array(
                    array('order', 'DESC')
                ),
                'limit' => array(0,1),
                'comment' => 'read prev object'
            )
        )){
            return $prev[0];
        }
        return null;
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
     * @return Entity
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
                $obj = Data::read(array(
                    'from' => array($this, $name),
                    'owner' => $this->_attribs['owner'],
                    'lang' => $this->_attribs['lang'],
                    'comment' => 'read property by name'
                ));
            }
            if (!$obj instanceof Entity){
                throw new Exception($this->uri().'/'.$name);
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
     * @return Entity
     */
    public function __set($name, $value)
    {
        if ($value instanceof Entity){
            // Именование
            // Если имя неопределенно, то потрубуется подобрать уникальное автоматически при сохранении
            // Перед сохранением используется временное имя
            if (is_null($name)){
                $value->name($value->_attribs['name'], true);
                $name = uniqid($value->_attribs['name']);
            }else{
                if ($this->uri().'/'.$name != $value->uri()){
                    $value->name($name, true);
                }
            }
            // Установка себя в качетсве родителя
            $value->parent($this);
            // В список загруженный подчиенных
            $this->_children[$name] = $value;
            return $value;
        }else{
            if (empty($name)) $name = 'entity';
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
     * @return Entity
     */
    public function add($value){
        $obj = $this->__set(null, $value);
        $obj->_changed = true;
        return $obj;
    }

    /**
     * Поиск подчиненных объектов
     * <code>
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
     * </code>
     * @param array $cond Условие поиска
     * @param bool $load Признак, загрузить найденные объекты в список подчиненных. Чтобы обращаться к ним как к свойствам объекта
     * @param bool $index Признак, индексировать или нет данные?
     * @param bool $access
     * @see https://github.com/Boolive/Boolive/issues/7
     * @return array
     */
    public function find($cond = array(), $load = false, $index = true, $access = true)
    {
        $cond = Data::normalizeCond($cond, array('select' => array('children'), 'depth' => array(1,1)));
        if ($this->isExist()){
            $cond['from'] = $this;//->id();
            $result = Data::read($cond, $access, $index);
        }else
        if (isset($this->_attribs['uri'])){
            $cond['from'] = $this->_attribs['uri'];
            $result = Data::read($cond, $access, $index);
        }else
        if ($p = $this->proto()){
            $cond['from'] = $p;//->id();
            $result = Data::read($cond, $access, $index);
            foreach ($result as $key => $obj){
                /** @var $obj Entity */
                $result[$key] = $obj->birth($this);
            }
        }else{
            return array();
        }
        // Установка выбранных подчиенных в свойства объекта
        if ($load && (($cond['select'][0] == 'children' && $cond['depth'][1] == 1) || $cond['select'][0] == 'tree')
            && empty($cond['select'][1]) && $cond['depth'][0] == 1)
        {
            if (isset($cond['key']) && $cond['key'] == 'name'){
                $this->_children = $result;
            }else{
                foreach ($result as $obj){
                    $this->_children[$obj->name()] = $obj;
                }
            }
        }
        return $result;
    }

    /**
     * Список выгруженных подчиненных (свойства объекта)
     * @param string $key Название атрибута, который использовать в качестве ключей элементов массива
     * @param array $depth Глубина подчиенных. По умолчанию
     * @return array Массив подчененных
     */
    public function children($key = 'name', $depth = array(1,1))
    {
        $result = array();
        if ($depth[0] == 1){
            if ($key === 'name'){
                $result = $this->_children;
            }else
            if (empty($key)){
                $result = array_values($this->_children);
            }else{
                foreach ($this->_children as $child){
                    $result[$child->_attribs[$key]];
                }
            }
        }
        if ($depth[0] == 0){
            array_unshift($result, $this);
        }
        if ($depth[1] > 1){
            $depth[0]--;
            $depth[1]--;
            foreach ($this->_children as $child){
                $result = array_merge($result, $child->children($key, $depth));
            }
        }
        return $result;
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
     * @param bool $access Признак, проверять доступ на запись или нет?
     * @throws \Exception
     * @return bool
     */
    public function save($history = false, $children = true, $access = true)
    {
        if (!$this->_is_saved){
            try{
                $this->_is_saved = true;
                // Сохранение родителя, если не сохранен или требует переименования
                if ($this->_parent){
                    if (!$this->_parent->isExist() || $this->_parent->_autoname){
                        $this->_parent->save(false, false);
                    }
                    $this->_attribs['parent'] = $this->_parent->key();
                }
                if ($this->_proto){
                    $this->_attribs['proto'] = $this->_proto->key();
                }
                if ($this->_owner){
                    $this->_attribs['owner'] = $this->_owner->key();
                }
                if ($this->_lang){
                    $this->_attribs['lang'] = $this->_lang->key();
                }
                // Если создаётся история, то нужна новая дата
                if ($history || (empty($this->_attribs['date']) && !$this->isExist())) $this->_attribs['date'] = time();
                // Сохранение себя
                if ($this->_changed && Data::write($this, $access)){
                    $this->_changed = false;
                }
                // Сохранение подчиненных
                if ($children){
                    $children = $this->children();
                    // Ошибки свойств группируются
                    $errors = new Error('Неверный объект', $this->uri());
                    foreach ($children as $child){
                        /** @var Entity $child */
                        try{
                            $child->save($history, true, $access);
                        }catch (Error $e){
                            $errors->_children->add($e);
                        }
                    }
                    if ($errors->isExist()) throw $errors;
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
     * Уничтожение объекта
     * Полностью удаляется объект и его подчиненных.
     * @param bool $access Признак, проверять или нет наличие доступа на уничтожение объекта?
     * @param bool $integrity Признак, проверять целостность данных?
     * @return bool Были ли объекты уничтожены?
     */
    public function destroy($access = true, $integrity = true)
    {
        if ($this->isExist()){
            return Data::delete($this, $access, $integrity);
        }else{
            return false;
        }
    }

    /**
     * Создание нового объекта прототипированием от себя
     * @param null|Entity $for Для кого создаётся новый объект?
     * @return Entity
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
        $obj->name(null, true); // Уникальность имени
        $obj->proto($this);
        $obj->owner($this->owner());
        $obj->lang($this->lang());
        $obj->isHidden($this->isHidden());
        $obj->isDelete($this->isDelete());
        $obj->isDefaultValue(true);
        $obj->isDefaultClass(true);
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
                /** @var Entity $child */
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
     * @param Entity $child Проверяемый подчиненный
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
     * <code>
     * array(                                      // услвоия поиска объединенные логическим AND
     *    array('attr', 'uri', '=', '?'),          // сравнение атрибута
     *    array('not', array(                      // отрицание всех условий
     *         array('attr', 'value', '=', '%?%')
     *    )),
     *    array('any', array(                      // услвоия объединенные логическим OR
     *         array('child', array(               // проверка свойства искомого объекта
     *             array('attr', 'value', '>', 10),
     *             array('attr', 'value', '<', 100),
     *         ))
     *     )),
     *     array('is', '/Library/object')          // кем объект является? проверка наследования
     * )
     * @param array $cond Условие как для поиска
     * @return bool
     */
    public function verify($cond)
    {
        if (empty($cond)) return true;
        if (is_string($cond)) $cond = Data::parseCond($cond);
        if (is_array($cond[0])) $cond = array('all', $cond);
        switch ($cond[0]){
            case 'all':
                foreach ($cond[1] as $c){
                    if (!$this->verify($c)) return false;
                }
                return true;
            case 'any':
                foreach ($cond[1] as $c){
                    if ($this->verify($c)) return true;
                }
                return !count($cond[1]);
            case 'not':
                return !$this->verify($cond[1]);
            case 'attr':
                if (in_array($cond[1], array('is', 'name', 'uri', 'key', 'date', 'order', 'value', 'diff', 'diff_from'))){
                    $value = $this->{$cond[1]}();
                }else
                if ($cond[1] == 'is_hidden'){
                    $value = $this->isHidden(null, empty($cond[4]));
                }else
                if ($cond[1] == 'is_delete'){
                    $value = $this->isDelete(null, empty($cond[4]));
                }else
                if ($cond[1] == 'is_file'){
                    $value = $this->isFile();
                }else
                if ($cond[1] == 'is_link'){
                    $value = $this->isLink();
                }else
                if ($cond[1] == 'is_history'){
                    $value = $this->isHistory();
                }
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
            case 'child':
                $child = $this->{$cond[1]};
                if ($child->isExist()){
                    if (isset($cond[2])){
                        return $child->verify($cond[2]);
                    }
                    return true;
                }
                return false;
            case 'in':
                if (!is_array($cond[1])) $cond[1] = array($cond[1]);
                foreach ($cond[1] as $parent){
                    if ($this->in($parent)) return true;
                }
                return false;
                break;
            case 'is':
                if (!is_array($cond[1])) $cond[1] = array($cond[1]);
                foreach ($cond[1] as $proto){
                    if ($this->is($proto)) return true;
                }
                return false;
            case 'of':
                if (!is_array($cond[1])) $cond[1] = array($cond[1]);
                foreach ($cond[1] as $obj){
                    if ($this->of($obj)) return true;
                }
                return false;
            default: return false;
        }
    }

    /**
     * Проверка, является ли подчиненным для указанного родителя?
	 * @param string|Entity $parent Экземпляр родителя или его идентификатор
     * @return bool
     */
    public function in($parent)
    {
        if ($parent instanceof Entity){
            $parent = $parent->uri();
        }else
        if (Data::isShortUri($parent)){
            $parent = Data::read($parent.'&cache=2')->uri();
        }
        return $parent.'/' == mb_substr($this->uri(),0,mb_strlen($parent)+1);
    }

    /**
     * Проверка, являектся наследником указанного прототипа?
     * @param string|Entity $proto Экземпляр прототипа или его идентификатор
     * @return bool
     */
    public function is($proto)
    {
        if ($proto == 'all') return true;
        if ($this->isEqual($proto)) return true;
        return ($p = $this->proto()) ? $p->is($proto) : false;
    }

    /**
     * Проверка, является подчиенным или наследником для указанного объекта
     * @param string|Entity $object Объект или идентификатор объекта, с котоым проверяется наследство или родительство
     * @return bool
     */
    public function of($object)
    {
        return $this->in($object) || $this->is($object);
    }

    /**
     * Сравнение объектов по uri
     * @param Entity $entity
     * @return bool
     */
    public function isEqual($entity)
    {
        if ($entity instanceof Entity){
            return $this->key() === $entity->key() &&
               $this->_attribs['owner'] == $entity->_attribs['owner'] &&
               $this->_attribs['lang'] == $entity->_attribs['lang']/* &&
               $this->date() == $entity->date()**/;
        }
        return isset($entity) && ($this->_attribs['id'] === $entity || $this->uri() === $entity);
    }

    /**
     * Признак, изменены атрибуты объекта или нет
     * @param null|bool $is_change Установка признака, если не null
     * @return bool
     */
    public function isChenged($is_change = null)
    {
        if (isset($is_change)){
            $this->_changed = $is_change;
            $this->_checked = false;
        }
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
     * Экпорт объекта в массив и сохранение в файл info в формате JSON
     * Экспортирует атрибуты объекта и свойства, названия которых возвращает Entity::exportedProperties()
     * @param bool $save_to_file Признак, сохранять в файл?
     * @param bool $more_info Признак, экспортировать дополнительную информацию об объекте
     * @param bool $properties Признак, экспортирвоать свойсвта или нет?
     * @return array
     */
    public function export($save_to_file = true, $more_info = false, $properties = true)
    {
        $export = array();
        if ($this->isDefaultValue()) $export['is_default_value'] = true;
        $export['value'] = $this->value();
        if ($this->isFile()) $export['is_file'] = true;
        if ($this->owner()) $export['owner'] = $this->owner()->uri();
        if ($this->lang()) $export['lang'] = $this->lang()->uri();
        if ($this->proto()) $export['proto'] = $this->proto()->uri();
        if ($this->isLink()) $export['is_link'] = true;
        if (!$this->isDefaultClass()) $export['is_default_class'] = false;
        if ($this->isHidden(null, false)) $export['is_hidden'] = true;
        if ($this->isDelete(null, false)) $export['is_delete'] = true;
        $export['date'] = $this->date();
        $export['order'] = $this->order();
        // Расширенный импорт
        if ($more_info){
            $export['id'] = $this->id();
            $export['uri'] = $this->uri();
            $export['name'] = $this->name();
            $export['proto_cnt'] = $this->protoCount();
            if ($this->parent()) $export['parent'] = $this->parent()->uri();
            if (!$this->isHidden()) $export['is_hidden'] = false;
            if (!$this->isDelete()) $export['is_delete'] = false;
            $export['is_history'] = $this->isHistory();
            if ($p = $this->isLink(null, true)) $export['is_link'] = $p->uri();
            if ($p = $this->isDefaultValue(null, true)) $export['is_default_value'] = $p->uri();
            if ($p = $this->isDefaultClass(null, true)) $export['is_default_class'] = $p->uri();
            if (!$this->isAccessible()) $export['is_accessible'] = false;
            if (!$this->isExist()) $export['is_exist'] = false;
        }
        // Свойства (подчиненные) объекта
        if ($properties){
            $export['children'] = array();
            // Названия подчиненных, которые экспортировать вместе с объектом
            $children = $this->exportedProperties();
            // Выбор подчиненных
            if ($children === true){
                $children = $this->find(array(
                    'where' => array(
                        array('attr', 'is_delete', '>=', 0)
                    ),
                    'comment' => 'read children for export'
                ), false, false);
            }else
            if (!empty($children) && is_array($children)){
                $children = $this->find(array(
                    'where' => array(
                        array('attr', 'name', 'in',  $children),
                        array('attr', 'is_delete', '>=', 0)
                    ),
                    'comment' => 'read children by names for export'
                ), false, false);
            }else{
                $children = array();
            }
            if (is_array($children)){
                foreach ($children as $child){
                    if ($child->isExist()){
                        $export['children'][$child->name()] = $child->export(false, $more_info);
                    }
                }
            }
            if (empty($export['children'])) unset($export['children']);
        }
        // Сохранение в info файл
        if ($save_to_file){
            $content = F::toJSON($export);
            $name = $this->name();
            if ($this->uri()=='') $name = 'Site';
            $file = $this->dir(true).$name.'.info';
            File::create($content, $file);
        }
        return $export;
    }

    /**
     * Названия свойств, которые экспортировать вместе с объектом
     * @return array
     */
    public function exportedProperties()
    {
        return array('title', 'description');
    }

    /**
     * Импортирование атрибутов и подчиенных из массива
     * Формат массива как в info файле.
     * @param $info
     */
    public function import($info)
    {
        // Имя и родитель
        if (isset($info['uri'])){
            $uri = F::splitRight('/', $info['uri'], true);
            $this->name($uri[1]);
            $this->parent($uri[0]);
        }
        // Значение
        if (!empty($info['is_default_value'])){
            $this->isDefaultValue(true);
        }else
        if (isset($info['value'])){
            $this->value($info['value']);
        }
        if (!empty($info['is_file'])) $this->isFile(true);
        // Владец, язык, прототип
        if (isset($info['owner'])) $this->owner($info['owner']);
        if (isset($info['lang'])) $this->lang($info['lang']);
        if (isset($info['proto'])) $this->proto($info['proto']);
        // Признаки
        if (!empty($info['is_link'])) $this->isLink(true);
        if (!empty($info['is_hidden'])) $this->isHidden(true);
        if (!empty($info['is_delete'])) $this->isDelete(true);
        // Свой класс?
        if (isset($info['is_default_class']) && empty($info['is_default_class'])){
            $this->isDefaultClass(false);
        }else{
            $this->isDefaultClass(true);
        }
        // Порядковое значение
        if (isset($info['order'])) $this->order($info['order']);
        $info['index_depth'] = 1;
        // Подчиненные объекты
        if (!empty($info['children']) && is_array($info['children'])){
            foreach ($info['children'] as $name => $child){
                //$child['uri'] = $this->uri().'/'.$name;
                if ($this->diff() == Entity::DIFF_ADD) $this->{$name}->diff(Entity::DIFF_ADD);
                if ($this->diff_from() < 1) $this->{$name}->diff_from($this->diff_from()-1);
                $this->{$name}->import($child);
            }
        }
    }

    /**
     * Условие, которым выбран объект
     * Устанавливается хранилищем после выборки объекта
     * Может быть не установленным
     * @param mixed $cond
     * @return mixed
     */
    public function cond($cond = null)
    {
        if (isset($cond)){
            $this->_cond = $cond;
        }
        return $this->_cond;
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
        return false;
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
     * Значения внутренных свойств объекта для трасировки
     * @return array
     */
    public function trace()
    {
        //$trace['hash'] = spl_object_hash($this);
        $trace['_attribs'] = $this->_attribs;
        $trace['_changed'] = $this->_changed;
        $trace['_checked'] = $this->_checked;
        $trace['_autoname'] = $this->_autoname;
        //$trace['_proto'] = $this->_proto;
        //$trace['_parent'] = $this->_parent;
        $trace['_cond'] = $this->_cond;
        $trace['_children'] = $this->_children;
        return $trace;
    }
}