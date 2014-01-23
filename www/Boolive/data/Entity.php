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
    Boolive\errors\Error,
    Boolive\file\File,
    Boolive\develop\ITrace,
    Boolive\values\Rule,
    Boolive\functions\F,
    Boolive\auth\Auth,
    Boolive\Boolive;

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

    /** @const int Автоматический выбор типа значения */
    const VALUE_AUTO = 0;
    /** @const int Простой тип. Строка до 255 символов */
    const VALUE_SIMPLE = 1;
    /** @const int Текстовый тип длиной до 64Кб с возможностью полнотекстового поиска */
    const VALUE_TEXT = 2;
    /** @const int Объект связан с файлом. Значением объекта является имя файла. */
    const VALUE_FILE = 3;

    /** @var array Атрибуты */
    public $_attribs = array(
        'uri'          => null,
        'id'           => null,
        'name'         => 'entity',
        'order'		   => 0,
        'date'		   => 0,
        'parent'       => null,
        'parent_cnt'   => 0,
        'proto'        => null,
        'proto_cnt'    => 0,
        'value'	 	   => '',
        'value_type'   => Entity::VALUE_AUTO,
        'is_draft'	   => 0,
        'is_hidden'	   => 0,
        'is_link'      => 0,
        'is_mandatory' => 0,
        'is_property'  => 0,
        'is_relative'  => 0,
        'is_default_value' => null, // по умолчанию равен id
        'is_default_class' => 0,
        'is_accessible'    => 1,
        'is_exist'     => 0,
        'author'	   => null,
        'update_step'  => 0,
        'update_time'  => 0,
        'diff'         => Entity::DIFF_NO,
        'diff_from'    => 0
    );
    /** @var array Подчиненные объекты (выгруженные из бд или новые, не обязательно все существующие) */
    protected $_children = array();
    /** @var Entity Экземпляр прототипа */
    protected $_proto = false;
    /** @var Entity Экземпляр родителя */
    protected $_parent = false;
    /** @var Entity Экземпляр автора */
    protected $_author = false;
    /** @var Entity Экземпляр прототипа, на которого ссылается объект */
    protected $_link = false;
    /** @var Entity Экземпляр прототипа, от которого берется значение по умолчанию */
    protected $_default_value_proto = false;
    /** @var bool Признак, свойство внутренне или нет */
    protected $_is_inner = false;
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
    public $_autoname = false;
    private $_current_name;

    /**
     * Конструктор
     * @param array $attribs Атрибуты объекта, а также атрибуты подчиенных объектов
     * @param int $children_depth До какой глубины (вложенности) создавать экземпляры подчиненных объектов
     */
    function __construct($attribs = array(), $children_depth = 0)
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
        if (isset($attribs['class_name'])) unset($attribs['class_name']);
        if (isset($attribs['cond'])){
            $this->_cond = $attribs['cond'];
            unset($attribs['cond']);
        }
        if (isset($attribs['children'])){
            if ($children_depth > 0){
                if ($children_depth != Entity::MAX_DEPTH) $children_depth--;
                foreach ($attribs['children'] as $name => $child){
                    $class = isset($child['class_name'])? $child['class_name'] : '\Boolive\data\Entity';
                    $child['cond'] = $this->_cond;
                    $this->_children[$name] = new $class($child, $children_depth);
                }
            }
            unset($attribs['children']);
        }

        $this->_attribs = array_replace($this->_attribs, $attribs);
    }

    /**
     * Правило на атрибуты
     * @return Rule
     */
    protected function rule()
    {
        return Rule::arrays(array(
            'id'           => Rule::uri(), // Сокращенный или полный URI
            'name'         => Rule::string()->regexp('|^[^/@:#\\\\]*$|')->min(IS_INSTALL?1:0)->max(50)->required(), // Имя объекта без символов /@:#\
            'order'		   => Rule::int()->max(Entity::MAX_ORDER), // Порядковый номер. Уникален в рамках родителя
            'date'		   => Rule::int(), // Дата создания в секундах
            'parent'       => Rule::uri(), // URI родителя
            'proto'        => Rule::uri(), // URI прототипа
            'value'	 	   => Rule::string()->max(65535), // Значение до 65535 сиволов
            'value_type'   => Rule::int()->min(0)->max(4), // Код типа значения. Определяет способ хранения (0=авто, 1=простое, 2=текст, 3=файл)
            'is_draft'	   => Rule::int(), // В черновике или нет с учётом признака родителя (сумма)?
            'is_hidden'	   => Rule::int(), // Скрытый или нет с учётом признака родителя (сумма)?
            'is_link'      => Rule::uri(), // Ссылка или нет?
            'is_mandatory' => Rule::bool()->int(), // Признак, обязательный или дополненый?
            'is_property'  => Rule::bool()->int(), // Признак, свойство или самостоятельный объект?
            'is_relative'  => Rule::bool()->int(), // Прототип относительный или нет?
            'is_default_value' => Rule::any(Rule::null(), Rule::uri()), // Используется значение прототипа или своё?
            'is_default_class' => Rule::uri(), // Используется класс прототипа или свой?
            'author'	   => Rule::uri(), // @todo Автор (идентификатор объекта-пользователя)
            'diff'         => Rule::int()->min(0)->max(3), // Код обнаруженных обновлений
            'diff_from'    => Rule::int(), // От куда обновления. 1 - от прототипа. 0 и меньше от info файла (кодируется относительное расположение файла)
            // Сведения о загружаемом файле. Не является атрибутом объекта, но используется в общей обработке
            'file'	=> Rule::arrays(array(
                'tmp_name'	=> Rule::string(), // Путь на связываемый файл
                'name'		=> Rule::lowercase()->ospatterns('*.*')->ignore('lowercase')->required(), // Имя файла, из которого будет взято расширение
                'size'		=> Rule::int(), // Размер в байтах
                'error'		=> Rule::int()->eq(0, true), // Код ошибки. Если 0, то ошибки нет
                'type'      => Rule::string(), // MIME тип файла
                'content'   => Rule::string()
            )),
            // Сведения о классе объекта (загружаемый файл или программный код). Не является атрибутом объекта
            'class' => Rule::arrays(array(
                'content'   => Rule::string(), // Программный код класса
                'tmp_name'	=> Rule::string(), // Путь на файл, если класс загржается в виде файла
                'size'		=> Rule::int(), // Размер в байтах
                'error'		=> Rule::int()->eq(0, true), // Код ошибки. Если 0, то ошибки нет
                'type'      => Rule::string() // MIME тип файла
            ))
        ));
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
    function id()
    {
        return isset($this->_attribs['id'])? $this->_attribs['id'] : null;
    }

    /**
     * Имя объекта
     * @param null $new_name Новое имя
     * @param bool $choose_unique Выбирать уникальное, если уже занято указанное?
     * @return string Имя объекта
     */
    function name($new_name = null, $choose_unique = false)
    {
        if (!isset($new_name) && $choose_unique) $new_name = $this->_attribs['name'];
        // Смена имени
        if (isset($new_name) && ($this->_attribs['name'] != $new_name || $choose_unique)){
            if (!isset($this->_current_name)) $this->_current_name = $this->_attribs['name'];
            //if ($choose_unique){
                $this->_autoname = $new_name;
            //}
            $this->_attribs['name'] = $new_name;
            $this->_changed = true;
            $this->_checked = false;
        }
        return $this->_attribs['name'];
    }

    /**
     * Текущее имя
     * Если установлено новое имя, но объекто ещё не сохранен, то возвращается старое имя объекта
     * @return mixed
     */
    function currentName()
    {
        if (isset($this->_current_name)) return $this->_current_name;
        return $this->_attribs['name'];
    }

    /**
     * URI объекта
     * @param bool $remake Признак, обновить URI? URI определяется по родителю и имени объекта
     * @param bool $encode Признак, кодировать спецсимволы в URI?
     * @return string
     */
    function uri($remake = false, $encode = false)
    {
        if (!isset($this->_attribs['uri']) || $remake){
            if ($parent = $this->parent()){
               $this->_attribs['uri'] = $parent->uri().'/'.$this->_attribs['name'];
            }else{
                $this->_attribs['uri'] = $this->_attribs['name'];
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
    function key()
    {
        return isset($this->_attribs['id']) ? $this->_attribs['id'] : $this->uri();
    }

    /**
     * Каскадное обновление URI подчиненных на основании своего uri
     * Обновляются uri только выгруженных/присоединенных на данный момент подчиенных
     */
    function updateURI()
    {
        foreach ($this->_children as $child_name => $child){
            /* @var Entity $child */
            $child->_attribs['uri'] = $this->_attribs['uri'].'/'.$child_name;
            $child->updateURI();
        }
    }

    /**
     * Дата изменения
     * @return mixed
     */
    function date($of_default = false)
    {
        if (!isset($this->_attribs['date'])){
            $this->_attribs['date'] = time();
        }
        if ($of_default && ($p = $this->isDefaultValue(null, true))){
            return (int)$p->_attribs['date'];
        }else{
            return (int)$this->_attribs['date'];
        }
    }

    /**
     * Порядковое значение объекта
     * @param null $new_order Новое значение. Если с указыннм порядковым номером имеется объект, то он будет смещен
     * @return mixed
     */
    function order($new_order = null)
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
    function value($new_value = null)
    {
        // Установка значения
        if (isset($new_value) && (!isset($this->_attribs['value']) || $this->_attribs['value']!==$new_value)){
            $this->_attribs['value'] = $new_value;
            $this->_attribs['value_type'] = Entity::VALUE_AUTO;
            $this->_attribs['is_default_value'] = $this->_attribs['id'];
            $this->_changed = true;
            $this->_checked = false;
        }
        // Возврат значения
        return $this->_attribs['value'];
    }

    /**
     * Тип значения (коды типов определены константами Entity::VALUE_*)
     * Определяет способ хранения значения
     * @param null|integer $new_type Новые тип значения, если не null
     * @return integer Текущий тип значения
     */
    function valueType($new_type = null)
    {
        if (isset($new_type) && $this->_attribs['value_type']!=$new_type){
            $this->_attribs['value_type'] = $new_type;
            $this->_changed = true;
            $this->_checked = false;
        }
        return $this->_attribs['value_type'];
    }

    /**
     * Файл, ассоциированный с объектом
     * @param null|array|string $new_file Информация о новом файле. Полный путь к новому файлу или сведения из $_FILES
     * @param bool $root Возвращать полный путь или от директории сайта
     * @param bool $cache_remote Если файл внешний, то сохранить его к себе на сервер и возвратить путь на него
     * @return null|string
     */
    function file($new_file = null, $root = false, $cache_remote = true)
    {
        // Установка нового файла
        if (isset($new_file)){
            if (empty($new_file)){
                unset($this->_attribs['file']);
                $this->_attribs['value_type'] = Entity::VALUE_AUTO;
            }else{
                if (is_string($new_file)){
                    $new_file = array(
                        'tmp_name'	=> $new_file,
                        'name' => basename($new_file),
                        'size' => @filesize($new_file),
                        'error'	=> is_file($new_file)? 0 : true
                    );
                }
                if (empty($new_file['name']) && $this->isFile()){
                    $new_file['name'] = $this->name().'.'.File::fileExtention($this->file());
                }
                $this->_attribs['file'] = $new_file;
                $this->_attribs['value_type'] = Entity::VALUE_FILE;
            }
            $this->_attribs['is_default_value'] = $this->_attribs['id'];
            $this->_changed = true;
            $this->_checked = false;
        }
        // Возврат пути к текущему файлу, если есть
        if ($this->_attribs['value_type'] == Entity::VALUE_FILE){
            if (($proto = $this->isDefaultValue(null, true)) && $proto->isExist()){
                $file = $proto->file(null, $root);
                if ($cache_remote && Data::isAbsoluteUri($file) && $this->diff()!=Entity::DIFF_ADD){
                    $file_path = Data::convertAbsoluteToLocal($file, false);
                    if (!is_file($f = DIR_SERVER_REMOTE.$file_path)){
                        // Загрзка файла с сервера
                        $uri = F::splitRight('/', $file);
                        $file_content = Data::read($uri[0].'&file_content=1')->fileContent(false, true);
                        if (isset($file_content['content'])){
                            $content = base64_decode($file_content['content']);
                            \Boolive\file\File::create($content, $f);
                        }
                    }
                    if ($root){
                        $file = $f;
                    }else{
                        $file = DIR_WEB_REMOTE.$file_path;
                    }
                }
                return $file;
            }else{
                $file = $this->dir($root);
                return $file.$this->_attribs['value'];
            }
        }
        return null;
    }

    /**
     * Путь на файл используемого класса (логики)
     * @param null $new_logic Установка своего класса. Сведения о загружаемом файле или его программный код
     * @param bool $root Возвращать полный путь или от директории сайта?
     * @return string @todo При соответствующих опциях возвращать название и программный код класса вместо пути на файл
     */
    function logic($new_logic = null, $root = false)
    {
        if (isset($new_logic)){
            if (is_string($new_logic)){
                $new_logic = array(
                    'tmp_name'	=> $new_logic,
                    'size' => @filesize($new_logic),
                    'error'	=> is_file($new_logic)? 0 : true
                );
            }
            $this->_attribs['class'] = $new_logic;
            $this->_attribs['is_default_class'] = 0;
            $this->_changed = true;
            $this->_checked = false;
        }
        if ($this->_attribs['is_default_class'] == Entity::ENTITY_ID){
            $path = ($root ? DIR_SERVER_ENGINE : DIR_WEB_ENGINE).'data/Entity.php';
        }else
        if ($proto = $this->isDefaultClass(null, true)){
            $path = $proto->logic(null, $root);
        }else{
            $path = $this->dir($root).$this->name().'.php';
        }
        return $path;
    }

    /**
     * Директория объекта
     * @param bool $root Признак, возвращать путь от корня сервера или от web директории (www)
     * @return string
     */
    function dir($root = false)
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
     * Содержимое файла и информация о нём.
     * Если у объекта значение не файл, то возвращается false
     * @param bool $only_hash Возвращать hash файла без его содержимого?
     * @param bool $base64 Признак, кодировать содержимое файла в base64
     * @return array|bool
     */
    function fileContent($only_hash = false, $base64 = true)
    {
        if (!isset($this->_attribs['file_content'])){
            // Для внешних объектов отдельные запросы на получение файлов не делаются.
            if ($this->isFile() && !$this->isRemote()){
                $f = $this->file(null, true);
                $c = file_get_contents($f);
                $this->_attribs['file_content'] = array(
                    'name' => $this->value(),
                    'hash' => md5($c),
                    'base64' => $base64,
                    'content' => $only_hash? null : ($base64 ? base64_encode($c) : $c)
                );
            }else{
                $this->_attribs['file_content'] = false;
            }
        }
        return $this->_attribs['file_content'];
    }

    /**
     * Содержимое (код) класса объекта с hash значением
     * @param bool $base64 Признак, кодировать содержимое файла в base64
     * @param bool $only_hash Возвращать hash файла без его содержимого?
     * @return array
     */
    function classContent($only_hash = false, $base64 = true)
    {
        if (!isset($this->_attribs['class_content'])){
            if ($this->isRemote()){
                $this->_attribs['class_content'] = false;
            }else{
                $class = get_class($this);
                if ($class != 'Boolive\data\Entity'){
                    $f = Boolive::getClassFile($class);
                    $c = file_get_contents($f);
                    $this->_attribs['class_content'] = array(
                        'name' => $class,
                        'hash' => md5($c),
                        'base64' => $base64,
                        'content' => $only_hash? null : ($base64 ? base64_encode($c) : $c)
                    );
                }else{
                    $this->_attribs['class_content'] = false;
                }
            }
        }
        return $this->_attribs['class_content'];
    }

    /**
     * Признак, является значение файлом или нет?
     * @param null|bool $is_file Новое значение, если не null
     * @return bool
     */
    function isFile($is_file = null)
    {
        $new_type = $is_file ? Entity::VALUE_FILE : Entity::VALUE_AUTO;
        if (isset($is_file) && $this->_attribs['value_type'] != $new_type){
            $this->_attribs['value_type'] = $new_type;
            $this->_changed = true;
            $this->_checked = false;
        }
        return $this->_attribs['value_type'] == Entity::VALUE_FILE;
    }

    /**
     * Признак, объект в черновике или нет?
     * @param null|bool $draft Новое значение, если не null
     * @param bool $inherit_parent Учитывать или нет признак родителя. Если нет, то черновик родителя не влияет на данный объект
     * @return bool
     */
    function isDraft($draft = null, $inherit_parent = true)
    {
        // Какой признак у родителя (чтобы его вычесть из своего)
        if ((!$inherit_parent || isset($draft)) && ($parent = $this->parent())){
            $p = $parent->_attribs['is_draft'];
        }else{
            $p = 0;
        }
        // Смена своего признака
        if (isset($draft) && ($this->_attribs['is_draft']-$p != ($draft = intval((bool)$draft)))){
            $this->_attribs['is_draft'] = $draft + $p;
            $this->_changed = true;
            $this->_checked = false;
        }
        return $inherit_parent ? !empty($this->_attribs['is_draft']) : ($this->_attribs['is_draft']-$p != 0);
    }

    /**
     * Признак, скрытый объект или нет?
     * @param null|bool $hide Новое значение, если не null
     * @param bool $inherit_parent Учитывать или нет признак родителя. Если нет, то скрытие родителя не влияет на данный объект
     * @return bool
     */
    function isHidden($hide = null, $inherit_parent = true)
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
    function isLink($is_link = null, $return_link = false)
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
     * Признак, объект является обязательным для родителя (true) или дополненым (false)?
     * @param null|bool $is_mandatory Новое значение, если не null
     * @return bool
     */
    function isMandatory($is_mandatory = null)
    {
        if (isset($is_mandatory) && (empty($this->_attribs['is_mandatory']) == $is_mandatory)){
            $this->_attribs['is_mandatory'] = $is_mandatory;
            $this->_changed = true;
            $this->_checked = false;
        }
        return !empty($this->_attribs['is_mandatory']);
    }

    /**
     * Признак, объект является свойством для родителя или самостоятельным.
     * Признак используется для оптимизации выборок и удобства предствления объектов в админке
     * @param null|bool $is_property  Новое значение, если не null
     * @return bool
     */
    function isProperty($is_property = null)
    {
        if (isset($is_property) && (empty($this->_attribs['is_property']) == $is_property)){
            $this->_attribs['is_property'] = $is_property;
            $this->_changed = true;
            $this->_checked = false;
        }
        return !empty($this->_attribs['is_property']);
    }

    /**
     * Признак, прототип относительный или нет?
     * @param null|bool $is_relative Новое значение, если не null
     * @return bool
     */
    function isRelative($is_relative = null)
    {
        if (isset($is_relative) && (empty($this->_attribs['is_relative']) == $is_relative)){
            $this->_attribs['is_relative'] = $is_relative;
            $this->_changed = true;
            $this->_checked = false;
        }
        return !empty($this->_attribs['is_relative']);
    }

    /**
     * Признак, сущесвтует объект или нет?
     * @return bool
     */
    function isExist()
    {
        return !empty($this->_attribs['is_exist']);
    }

    /**
     * Признак, доступен объект или нет для совершения указываемого действия над ним?
     * Доступность проверяется для текущего пользователя
     * @param string $action Название действия. По умолчанию дейсвте чтения объекта.
     * @return bool
     */
    function isAccessible($action = 'read')
    {
        if (!empty($this->_attribs['is_accessible'])){
            if ($action != 'read'){
                return !IS_INSTALL || $this->verify(Auth::getUser()->getAccessCond($action, $this));
            }
            return true;
        }
        return false;
    }

    /**
     * Признак, наследуется ли значение от прототипа и от кого именно?
     * @param null $is_default Новое значение признака. Для отмены значения по умолчанию необходимое изменить само значение.
     * @param $return_proto Если значение по умолчанию, то возвращать прототип, чьё значение наследуется или true?
     * @return bool|Entity
     */
    function isDefaultValue($is_default = null, $return_proto = false)
    {
        if (isset($is_default)){
            $curr = $this->_attribs['is_default_value'];
            if ($is_default){
                // Поиск прототипа, от котоого наследуется значение, чтобы взять его значение
                if (($proto = $this->proto(null, true, true))/* && $proto->isLink() == $this->isLink()*/){
                    if ($p = $proto->isDefaultValue(null, true)) $proto = $p;
                }
                if ($proto instanceof Entity && $proto->isExist()){
                    if ($proto->store() != $this->store()){
                        $this->_attribs['is_default_value'] = $proto->uri();
                    }else{
                        $this->_attribs['is_default_value'] = $proto->key();
                    }
                    $this->_attribs['value'] = $proto->value();
                    $this->_attribs['value_type'] = $proto->valueType();
                }else{
                    if (!isset($this->_attribs['is_default_value'])){
                        $this->_attribs['is_default_value'] = Entity::ENTITY_ID;
                        $this->_attribs['value'] = '';
                        $this->_attribs['value_type'] = Entity::VALUE_AUTO;
                    }
                }
            }else{
                if (IS_INSTALL && $this->_attribs['is_default_value']!=$this->_attribs['id'] && ($proto = $this->isDefaultValue(null, true)) && $proto->isFile()){
                    $content = $this->fileTemplate();
                    if (is_null($content)){
                        $this->file($proto->file(null, true));
                    }else{
                        $this->file(array(
                            'name' => File::changeName(File::fileName($proto->file()), $this->name()),
                            'content' => $content
                        ));
                    }
                }
                $this->_attribs['is_default_value'] = $this->_attribs['id'];
            }
            if ($curr !== $this->_attribs['is_default_value']){
                $this->_changed = true;
                $this->_checked = false;
            }
        }
        if ($this->_attribs['is_default_value'] != $this->_attribs['id'] && $return_proto){
            if ($this->_default_value_proto === false){
                // Поиск прототипа, от которого наследуется значение, чтобы возратить его
                $this->_default_value_proto = Data::read(array(
                    'from' => $this,
                    'select' => 'default_value_proto',
                    'depth' => array(0,0),
                    'comment' => 'read default value',
                    'cache' => 2
//                    'from'=>$this->_attribs['is_default_value'],
//                    'comment'=>'read default value',
//                    'cache' => 2
                ), false);
            }
            return $this->_default_value_proto;
        }else{
            return $this->_attribs['is_default_value'] != $this->_attribs['id'];
        }
    }

    /**
     * Признак, используется класс прототипа или свой?
     * @param null $is_default
     * @param bool $return_proto
     * @return bool | Entity
     */
    function isDefaultClass($is_default = null, $return_proto = false)
    {
        if (isset($is_default)){
            $curr = $this->_attribs['is_default_class'];
            if ($is_default){
                // Поиск прототипа, от которого наследуется значение, чтобы взять его значение
                if (($proto = $this->proto(null, true, true)) && $proto->isLink() == $this->isLink()){
                    if ($p = $proto->isDefaultClass(null, true)) $proto = $p;
                }else{
                    $proto = null;
                }
                if ($proto instanceof Entity && $proto->isExist()){
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
                // Если файла класса нет, то создаём его программный код
                if (IS_INSTALL && !is_file($this->dir(true).($this->currentName()===''?'Site':$this->currentName()).'.php')){
                    $this->logic(array(
                        'content' => $this->classTemplate()
                    ));
                }
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
    function diff($diff = null)
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
     * @return int Код, от куда обновления
     */
    function diff_from($diff_from = null)
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
    function attributes()
    {
        return $this->_attribs;
    }

    /**
     * Атрибут объекта по имени
     * Необходимо учитывать, что некоторые атрибуты могут быть ещё не инициалироваными
     * @param $name Назавние возвращаемого атрибута
     * @return mixed Значение атрибута
     */
    function attr($name)
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
    function parent($new_parent = null, $load = true)
    {
        if (is_string($new_parent)) $new_parent = Data::read($new_parent);
        // Смена родителя
        if (isset($new_parent) && (empty($new_parent)&&!empty($this->_attribs['parent']) || !$new_parent->eq($this->parent()) || $this->_attribs['parent_cnt']!=$new_parent->parentCount()+1)){
            $is_draft = $this->isDraft(null, false);
            $is_hidden = $this->isHidden(null, false);
            if (empty($new_parent)){
                // Удаление родителя
                $this->_attribs['parent'] = null;
                $this->_attribs['parent_cnt'] = 0;
                $this->_parent = null;
                //$this->updateURI($this->name());
            }else{
                // Новый родитель не должен быть свойстовм объекта
                if ($new_parent->in($this)){
                    $errors = new Error('Неверный объект', $this->uri());
                    if ($new_parent->eq($this)){
                        $errors->_attribs->parent = 'Объект не может сам для себя стать родителем';
                    }else{
                        $errors->_attribs->parent = 'Свойство не может стать родителем для объекта';
                    }
                    throw $errors;
                }
                // Смена родителя
                $this->_parent = $new_parent;
                $this->_attribs['parent'] = $new_parent->key();
                $this->_attribs['parent_cnt'] = $new_parent->parentCount() + 1;
                $this->_attribs['order'] = Entity::MAX_ORDER;
                // Установка атрибутов, зависимых от прототипа
                //if ($new_parent->isLink() || !isset($this->_attribs['is_link'])) $this->_attribs['is_link'] = 1;
                // Обновление доступа
                if (!$new_parent->isAccessible() || !isset($this->_attribs['is_accessible'])) $this->_attribs['is_accessible'] = $new_parent->isAccessible();
               // $this->updateURI($new_parent->uri().'/'.$this->name());
            }
            if ($this->isExist()) $this->name(null, true);
            // Обновление зависимых от родителя признаков
            $this->isDraft($is_draft);
            $this->isHidden($is_hidden);
            $this->_changed = true;
            $this->_checked = false;
        }
        // Возврат объекта-родителя
        if ($this->_parent === false && $load){
            if (isset($this->_attribs['parent'])){
                $this->_parent = Data::read(array(
                    'from' => $this->_attribs['parent'],
                    'comment' => 'read parent',
                    'cache' => 2
                ), false);
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
    function parentCount()
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
    function parentUri()
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
     * Имя родителя определяется без загрузки и обращения к родителю
     * @return string
     */
    function parentName()
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
     * @throws \Exception
     * @return Entity|null|bool
     */
    function proto($new_proto = null, $load = true, $reload = false)
    {
        if (is_string($new_proto)) $new_proto = Data::read($new_proto);
        // Смена прототипа
        if (isset($new_proto) && ((empty($new_proto)&&!empty($this->_attribs['proto'])) || !$new_proto->eq($this->proto()))){
            if (empty($new_proto)){
                // Удаление прототипа
                $this->_attribs['proto'] = null;
                $this->_attribs['proto_cnt'] = 0;
                $this->_attribs['is_default_value'] = $this->_attribs['id'];
                if ($this->_attribs['is_default_class'] != 0){
                    $this->_attribs['is_default_class'] = self::ENTITY_ID;
                }
                if ($this->_attribs['is_link'] != 0){
                    $this->_attribs['is_link'] = self::ENTITY_ID;
                }
                $this->_proto = null;
            }else{
                // Новый родитель не должен быть свойстовм объекта
                if ($this->isExist() && $new_proto->is($this)){
                    $errors = new Error('Неверный объект', $this->uri());
                    if ($new_proto->eq($this)){
                        $errors->_attribs->proto = 'Объект не может сам для себя стать прототипом';
                    }else{
                        $errors->_attribs->proto = 'Наследник не может стать прототипом для объекта';
                    }
                    throw $errors;
                }
                // Наследование значения
                if ($this->isDefaultValue()){
                    $this->_attribs['value'] = $new_proto->value();
                    $this->_attribs['value_type'] = $new_proto->valueType();
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
        $reload = $reload && $this->_proto instanceof Entity && !$this->_proto->isExist() && isset($this->_attribs['proto']);
        if (($this->_proto === false && $load) || $reload){
            if (isset($this->_attribs['proto'])){
                $this->_proto = Data::read(array(
                    'from' => $this->_attribs['proto'],
                    'comment' => 'read proto',
                    'cache' => $reload ? 0 : 2
                ));
                if (!$this->_proto instanceof Entity){
                    throw new Exception('NO PROTO '.$this->_attribs['proto']);
                }
                $this->_attribs['proto_cnt'] = null;
//                if (!$this->_proto->isExist()){
//                    $this->_proto = null;
//                }
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
    function protoCount()
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
     * Автор объекта
     * @param null|Entity $new_author Новый автор. Чтобы сделать без автора, указывается false
     * @param bool $load Загрузить автора из хранилща, если ещё не загружен?
     * @return Entity|null
     */
    function author($new_author = null, $load = true)
    {
        if (is_string($new_author)) $new_author = Data::read($new_author);
        // Смена автора
        if (isset($new_author) && (empty($new_author)&&!empty($this->_attribs['author']) || !$new_author->eq($this->author()))){
            if (empty($new_author)){
                // Удаление автора
                $this->_attribs['author'] = null;
                $this->_author = null;
            }else{
                if ($new_author->store() != $this->store()){
                    $this->_attribs['author'] = $new_author->uri();
                }else{
                   $this->_attribs['author'] = $new_author->key();
                }
                $this->_author = $new_author;
            }
            $this->_changed = true;
            $this->_checked = false;
        }
        // Возврат объекта-автора
        if ($this->_author === false && $load){
            if (isset($this->_attribs['author'])){
                $this->_author = Data::read(array(
                    'from' => $this->_attribs['author'],
                    'author' => $this->_attribs['author'],
                    'comment' => 'read author',
                    'cache' => 2
                ));
            }else{
                $this->_author = null;
            }
        }
        return $this->_author;
    }

    /**
     * Объект, на которого ссылется данный, если является ссылкой
     * Если данный объект не является ссылкой, то возарщается $this,
     * иначе возвращается первый из прототипов, не являющейся ссылкой
     * @param bool $clone Клонировать, если объект является ссылкой?
     * @return Entity
     */
    function linked($clone = false)
    {
        if (empty($this->_attribs['is_link'])) return $this;
        if ($link = $this->isLink(null, true)){
            if ($clone) $link = clone $link;
            return $link;
        }
        return $this;
    }

    /**
     * Внутреннй.
     * Доступ к внутренему объекту, который скрыт в (одном из) прототипе родителя
     * Объеты не создаются автоматически из-за скрытости их прототипов, но к ним можно получить доступ.
     * @return $this | Entity Текущий или новый объект, если текущий не существует, но у него есть скрытый прототип
     */
    function inner()
    {
        if (!$this->isExist() && !$this->_attribs['proto'] && ($p = $this->parent())){
            // У прототипов родителя найти свойство с именем $this->name()
            $find = false;
            $name = $this->name();
            $protos = array($this);
            $parents = array($p);
            while (($p = $p->proto()) && !$find){
                $propertry = $p->{$name};
                $find = $propertry->isExist();
                $protos[] = $propertry;
                $parents[] = $p;
            }
            for ($i = sizeof($protos)-1; $i>0; $i--){
                $protos[$i-1] = $protos[$i]->birth($parents[$i-1], false);
                $protos[$i-1]->_is_inner = true;
            }
            return $protos[0];
        }
        return $this;
    }

    /**
     * Следующий объект
     */
    function next()
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
    function prev()
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
     * Получение подчиненного объекта по имени
     * @example $sub = $obj->sub;
     * @param string $name Имя подчиенного объекта
     * @throws \Exception
     * @return Entity
     */
    function __get($name)
    {
        if (isset($this->_children[$name])){
            return $this->_children[$name];
        }else{
            if (!$this->isExist()){
                if (($p = $this->proto()) && $p->{$name}->isExist()){
                    $obj = $p->{$name}->birth($this);
                    $obj->isDraft(false);
                }else{
                    $obj = new Entity();
                }
                // Не подбирать уникальное имя, так как при сохранении родителя он проиндексируется и его свойства нужно будет обновить текущим, а не создавать новое
                $obj->_autoname = false;
            }else{
                $obj = Data::read(array(
                    'from' => array($this, $name),
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
            if (!$obj->isExist()){
                $obj->_changed = false;
            }
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
    function __set($name, $value)
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
    function __isset($name)
    {
        return isset($this->_children[$name]);
    }

    /**
     * Удаление подчиненного с именем $name из списка выгруженных
     * @example unset($object->sub);
     * @param $name Имя подчиненного объекта
     */
    function __unset($name)
    {
        unset($this->_children[$name]);
    }

    /**
     * Добавление подчиненного с автоматическим именованием
     * @param Entity|mixed $value
     * @return Entity
     */
    function add($value){
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
    function find($cond = array(), $load = false, $index = true, $access = true)
    {
        $cond = Data::normalizeCond($cond, array('select' => array('children'), 'depth' => array(1,1)));
        if ($this->isExist()){
            $cond['from'] = $this;//->id();
            $result = Data::read($cond, $access, $index);
        }else
//        if (isset($this->_attribs['uri'])){
//            $cond['from'] = $this->_attribs['uri'];
//            $result = Data::read($cond, $access, $index);
//        }else
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
    function children($key = 'name', $depth = array(1,1))
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
     * Сохранение объекта
     * @param bool $children Признак, сохранять подчиенных или нет?
     * @param bool $access Признак, проверять доступ на запись или нет?
     * @throws \Exception
     * @return bool
     */
    function save($children = true, $access = true)
    {
        if (!$this->_is_saved){
            try{
                $this->_is_saved = true;
                if ($this->_changed){
                    // Сохранение родителя, если не сохранен или требует переименования
                    if ($this->_parent){
                        if (!$this->_parent->isExist() || $this->_parent->_autoname){
                            $this->_parent->save(false, $access);
                        }
                        $this->_attribs['parent'] = $this->_parent->key();
                    }
                    if ($this->_proto){
                        if (!$this->_proto->isExist()){
                            $this->_proto->save(false, $access);
                        }
                        $this->_attribs['proto'] = $this->_proto->key();
                    }
                     // Если создаётся история, то нужна новая дата
                    if (empty($this->_attribs['date']) && !$this->isExist()) $this->_attribs['date'] = time();

//                    if ($this->_attribs['uri'] == '/Library/layouts/Admin/Bookmarks/item_view/views/page_item'){
                    if ($this->_proto instanceof Entity && !$this->_proto->isExist()){
                        if ($this->_attribs['is_default_class'] == Entity::ENTITY_ID){
                            $this->isDefaultClass(true);
                        }
                        if ($this->isDefaultValue()){
                            $this->isDefaultValue(true);
                        }
                        if ($this->isLink()){
                            $this->isLink(true);
                        }
                    }

                    // Сохранение себя
                    if (Data::write($this, $access)){
                        $this->_changed = false;
                    }
                    $this->_current_name = null;
                }
                // Сохранение подчиненных
                if ($children){
                    $children = $this->children();
                    // Ошибки свойств группируются
                    $errors = new Error('Неверный объект', $this->uri());
                    foreach ($children as $child){
                        /** @var Entity $child */
                        try{
                            $child->save(true, $access);
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
     * Полностью удаляется объект и его подчиненные.
     * @param bool $access Признак, проверять или нет наличие доступа на уничтожение объекта?
     * @param bool $integrity Признак, проверять целостность данных?
     * @return bool Были ли объекты уничтожены?
     */
    function destroy($access = true, $integrity = true)
    {
        if ($this->isExist()){
            return Data::delete($this, $access, $integrity);
        }else{
            return false;
        }
    }

    /**
     * Создание нового объекта прототипированием от себя
     * @param null|Entity $for Для кого создаётся новый объект (будущий родитель)?
     * @param bool $draft Признак, создавать черновик?
     * @return Entity
     */
    function birth($for = null, $draft = true)
    {
        $class = get_class($this);
        $attr = array(
            'name' => $this->name(),
            'order' => self::MAX_ORDER,
            'is_default_value' => self::ENTITY_ID,
            'is_default_class' => self::ENTITY_ID
        );
        /** @var $obj Entity */
        $obj = new $class($attr);
        $obj->name(null, true); // Уникальность имени
        if (isset($for)) $obj->parent($for);
        $obj->proto($this);
        $obj->isHidden($this->isHidden());
        $obj->isDraft($draft || $this->isDraft(null, false));
        $obj->isDefaultValue(true);
        $obj->isDefaultClass(true);
        if ($this->isLink()) $this->_attribs['is_link'] = 1;
        return $obj;
    }

    /**
     * Хранилище объекта
     * Где объект сохранен или будет сохранен?
     * @return stores\MySQLStore|null
     */
    function store()
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
    function check(&$errors = null, $children = true)
    {
        // "Контейнер" для ошибок по атрибутам и подчиненным объектам
        $errors = new Error('Неверный объект', $this->uri());
        if ($this->_checked) return true;

        // Проверка и фильтр атрибутов
        $attribs = new Values($this->_attribs);
        $this->_attribs = array_replace($this->_attribs, $attribs->get($this->rule(), $error));
        /** @var $error Error */
        if ($error){
            $errors->_attribs->add($error->children());
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
     * @param array|string $cond Условие как для поиска
     * @throws \Exception
     * @return bool
     */
    function verify($cond)
    {
        if (empty($cond)) return true;
        if (is_string($cond)) $cond = Data::parseCond($cond);
        if (is_array($cond[0])) $cond = array('all', $cond);
        switch (strtolower($cond[0])){
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
                if ($cond[1] == 'is_draft'){
                    $value = $this->isDraft(null, empty($cond[4]));
                }else
                if ($cond[1] == 'is_file'){
                    $value = $this->isFile();
                }else
                if ($cond[1] == 'value_type'){
                    $value = $this->valueType();
                }else
                if ($cond[1] == 'is_link'){
                    $value = $this->isLink();
                }else
                if ($cond[1] == 'is_relative'){
                    $value = $this->isRelative();
                }else
                if ($cond[1] == 'is_mandatory'){
                    $value = $this->isMandatory();
                }else
                if ($cond[1] == 'is_property'){
                    $value = $this->isProperty();
                }else
                if ($cond[1] == 'parent_cnt'){
                    $value = $this->parentCount();
                }else
                if ($cond[1] == 'proto_cnt'){
                    $value = $this->protoCount();
                }else{
                    $value = null;
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
            case 'match':
                throw new Exception('Expression "match" for fulltext search is not supported in function verify()');
                break;
            case 'child':
                $child = $this->{$cond[1]};
                if ($child->isExist()){
                    if (isset($cond[2])){
                        return $child->verify($cond[2]);
                    }
                    return true;
                }
                return false;
            case 'heir':
                $heir = $this->{$cond[1]};
                if ($heir->isExist()){
                    if (isset($cond[2])){
                        return $heir->verify($cond[2]);
                    }
                    return true;
                }
                return false;
            case 'eq':
                if (is_array($cond[1])){
                    $cond = $cond[1];
                }else{
                    unset($cond[0]);
                }
                foreach ($cond as $proto){
                    if ($this->eq($proto)) return true;
                }
                return false;
            case 'in':
                if (is_array($cond[1])){
                    $cond = $cond[1];
                }else{
                    unset($cond[0]);
                }
                foreach ($cond as $parent){
                    if ($this->in($parent)) return true;
                }
                return false;
                break;
            case 'is':
                if (is_array($cond[1])){
                    $cond = $cond[1];
                }else{
                    unset($cond[0]);
                }
                foreach ($cond as $proto){
                    if ($this->is($proto)) return true;
                }
                return false;
            case 'of':
                if (is_array($cond[1])){
                    $cond = $cond[1];
                }else{
                    unset($cond[0]);
                }
                foreach ($cond as $obj){
                    if ($this->of($obj)) return true;
                }
                return false;
            case 'childof':
                if (is_array($cond[1])){
                    $cond = $cond[1];
                }else{
                    unset($cond[0]);
                }
                foreach ($cond as $parent){
                    if ($this->childOf($parent)) return true;
                }
                return false;
                break;
            case 'heirof':
                if (is_array($cond[1])){
                    $cond = $cond[1];
                }else{
                    unset($cond[0]);
                }
                foreach ($cond as $proto){
                    if ($this->heirOf($proto)) return true;
                }
                return false;
            case 'access':
                return $this->isAccessible($cond[1]);
            default: return false;
        }
    }

    /**
     * Проверка, является ли подчиненным для указанного родителя?
	 * @param string|Entity $parent Экземпляр родителя или его идентификатор
     * @return bool
     */
    function in($parent)
    {
        if ($this->eq($parent)) return true;
        if ($parent->_attribs['uri'] == '' && !$parent->isExist()) return false;
        return $this->childOf($parent);
    }

    /**
     * Проверка, являектся наследником указанного прототипа?
     * @param string|Entity $proto Экземпляр прототипа или его идентификатор
     * @return bool
     */
    function is($proto)
    {
        if ($proto == 'all') return true;
        if ($this->eq($proto)) return true;
        return $this->heirOf($proto);
    }

    /**
     * Проверка, является подчиенным или наследником для указанного объекта?
     * @param string|Entity $object Объект или идентификатор объекта, с котоым проверяется наследство или родительство
     * @return bool
     */
    function of($object)
    {
        return $this->in($object) || $this->is($object);
    }

    /**
     * Сравнение с дргуим объектом (экземпляром) по uri
     * @param Entity $object
     * @return bool
     */
    function eq($object)
    {
        if ($object instanceof Entity){
            return $this->key() === $object->key();
        }
        return isset($object) && ($this->_attribs['id'] === $object || $this->uri() === $object);
    }

    /**
     * Провкра, является ли подчиненным для указанного объекта?
     * @param $object
     * @return bool
     */
    function childOf($object)
    {
        if ($object instanceof Entity){
            $object = $object->uri();
        }else
        if (Data::isShortUri($object)){
            $object = Data::read($object.'&cache=2')->uri();
        }
        return $object.'/' == mb_substr($this->uri(),0,mb_strlen($object)+1);
    }

    /**
     * Провкра, является ли наследником для указанного объекта?
     * @param $object
     * @return bool
     */
    function heirOf($object)
    {
        return ($p = $this->proto()) ? $p->is($object) : false;
    }

    /**
     * Экпорт объекта в массив с возможностью сохраненить в файл .info в формате JSON в директории объекта
     * Экспортирует атрибуты объекта и свойства
     * @param bool $save_to_file Признак, сохранять в файл?
     * @param bool $more_info Признак, экспортировать дополнительную информацию об объекте
     * @param bool $export_properties Признак, экспортирвоать свойсвта или нет?
     * @param int $export_file Экпортировать или нет файл объекта? 0 - нет, 1 - hash файла, 2 - hash и содержимое в base64
     * @param int $export_class Экпортировать или нет класс объекта (его php код)? 0 - нет, 1 - hash файла, 2 - hash и содержимое в base64
     * @return array
     */
    function export($save_to_file = true, $more_info = false, $export_properties = true, $export_file = 0, $export_class = 0)
    {
        $export = array();
        if ($this->isDefaultValue()){
            $export['is_default_value'] = true;
        }//else{
            /*if ($this->value() !== '') */$export['value'] = $this->value();
        //}
        if ($this->valueType() > Entity::VALUE_SIMPLE) $export['value_type'] = $this->valueType();
        if ($this->proto()) $export['proto'] = $this->proto()->uri();
        if ($this->isLink()) $export['is_link'] = true;
        if (!$this->isDefaultClass()) $export['is_default_class'] = false;
        if ($this->isHidden(null, false)) $export['is_hidden'] = true;
        if ($this->isDraft(null, false)) $export['is_draft'] = true;
        if ($this->isMandatory()) $export['is_mandatory'] = true;
        if ($this->isRelative()) $export['is_relative'] = true;
        if ($this->isProperty()) $export['is_property'] = true;
        $export['order'] = $this->order();
        // Расширенный импорт
        if ($more_info){
            $export['id'] = $this->id();
            $export['uri'] = $this->uri();
            $export['name'] = $this->name();
            $export['date'] = $this->date();
            $export['proto_cnt'] = $this->protoCount();
            if ($this->isFile()) $export['file'] = $this->file();
            if ($this->parent()) $export['parent'] = $this->parent()->uri();
            if (!$this->isHidden()) $export['is_hidden'] = false;
            if (!$this->isDraft()) $export['is_draft'] = false;
            if (!$this->isRelative()) $export['is_relative'] = false;
            if (!$this->isMandatory()) $export['is_mandatory'] = false;
            if (!$this->isProperty()) $export['is_property'] = false;
            if ($p = $this->isLink(null, true)) $export['is_link'] = $p->uri();
            if ($p = $this->isDefaultValue(null, true)) $export['is_default_value'] = $p->uri();
            if ($p = $this->isDefaultClass(null, true)) $export['is_default_class'] = $p->uri();
            if (!$this->isAccessible()) $export['is_accessible'] = false;
            if (!$this->isExist()) $export['is_exist'] = false;
        }
        // Свойства (подчиненные) объекта
        if ($export_properties){
            $export['children'] = array();
            $children = $this->find(array(
                'where' => array(
                    array('attr', 'is_draft', '>=', 0),
                    array('attr', 'is_hidden', '>=', 0),
                    array('attr', 'is_property', '=', 1)
                ),
                'comment' => 'read property for export'
            ), false, false);
            if (is_array($children)){
                foreach ($children as $child){
                    /** @var $child Entity */
                    if ($child->isExist()){
                        $export['children'][$child->name()] = $child->export(true, $more_info);
                        if ($save_to_file){
                            $info_file = $child->dir(true).$child->name().'.info';
                            File::delete($info_file);
                            File::deleteEmtyDir($child->dir(true));
                        }
                    }
                }
            }
            if (empty($export['children'])) unset($export['children']);
        }
        // файл
        if ($export_file) $export['file_content'] = $this->fileContent($export_file!=2);
        // класс
        if ($export_class) $export['class_content'] = $this->classContent($export_class!=2);

        // Сохранение в info файл
        if ($save_to_file && !$this->isProperty()){
            $content = F::toJSON($export);
            $name = $this->name();
            if ($this->uri() === '') $name = 'Site';
            $file = $this->dir(true).$name.'.info';
            File::create($content, $file);
        }
        return $export;
    }

    /**
     * Импортирование атрибутов и подчиенных из массива
     * Формат массива как в Entity->export()
     * @param $info
     */
    function import($info)
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
            if (isset($info['value']) && empty($this->_attribs['value'])) $this->_attribs['value'] = $info['value'];
        }else
        if (isset($info['value'])){
            $this->value($info['value']);
        }
        if (!empty($info['value_type'])) $this->valueType($info['value_type']);
        // Прототип
        if (isset($info['proto'])) $this->proto($info['proto']);
        // Признаки
        $this->isLink(!empty($info['is_link']));
        if (!empty($info['is_hidden'])) $this->isHidden(true);
        if (!empty($info['is_draft'])) $this->isDraft(true);
        if (!empty($info['is_relative'])) $this->isRelative(true);
        if (!empty($info['is_mandatory'])) $this->isMandatory(true);
        if (!empty($info['is_property'])) $this->isProperty(true);
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
                $this->{$name}->isDraft(!empty($child['is_draft']));
            }
        }
    }

    /**
     * Признак, изменены атрибуты объекта или нет
     * @param null|bool $is_change Установка признака, если не null
     * @return bool
     */
    function isChenged($is_change = null)
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
    function isSaved()
    {
        return $this->_is_saved;
    }

    /**
     * Признак, объект внутренний или нет?
     * Объект является внутренним, если не существует, но был получен прототипированием от свойств прототипа родителя
     * @param null|bool $is_inner Новое значение, если не null
     * @return bool|null
     */
    function isInner($is_inner = null)
    {
        if (isset($is_inner) && (empty($this->_is_inner) == $is_inner)){
            $this->_is_inner = $is_inner;
            $this->_changed = true;
            $this->_checked = false;
        }
        return $this->_is_inner;
    }

    /**
     * Признак, является ли объект внешним?
     * @return bool
     */
    function isRemote()
    {
        return Data::isAbsoluteUri($this->uri());
    }

    /**
     * Шаблон своего класса
     * Для автоматического создания php файла с класом данного объекта.
     * @param array $methods Код предопределяемых методов
     * @param array $use Используемые дополнительные классы (может понадобиться, если определяются методы)
     * @return string Программный код класса
     */
    function classTemplate($methods = array(), $use = array())
    {
        // phpDoc
        $title = $this->title->value();
        $description = $this->description->value();
        // Название
        $name = $this->name();
        if ($name === '') $name = 'Site';
        $namespace = str_replace('/','\\', trim($this->dir(false),'/'));
        if (mb_substr($namespace, 0, 5) == 'Site\\') $namespace = mb_substr($namespace, 5);
        // Наследуемый класс
        if ($proto = $this->proto()){
            $extends = get_class($proto);
        }else{
            $extends = 'Boolive\\data\\Entity';
        }
        array_unshift($use, $extends);
        $use = array_unique($use);
        // Используемые классы
        $use_plain = '';
        $shorts = array($name);
        foreach ($use as $u){
            if (!empty($use_plain)) $use_plain.=",\n    ";
            $use_plain.=$u;
            $short = F::splitRight('\\', $u);
            if (in_array($short[1], $shorts)){
                $short = $short[1].'_'.count($shorts);
                $use_plain.=' as '.$short;
            }else{
                $short = $short[1];
            }
            $shorts[] = $short;
        }
        // Методы
        $methods = implode("\n", $methods);
        return "<?php
/**
 * $title
 * $description
 * @version 1.0
 */
namespace $namespace;

use $use_plain;

class $name extends $shorts[1]
{
$methods
}";
    }

    /**
     * Шаблон своего файла
     * Для автоматического создания файла при переопредлении файла прототипа
     * Если возвращается null, то полностью копируется файл прототипа
     * @return null|string
     */
    function fileTemplate()
    {
        return null;
    }

    /**
     * Условие, которым выбран объект
     * Устанавливается хранилищем после выборки объекта
     * Может быть неустановленным
     * @param mixed $cond
     * @return mixed
     */
    function cond($cond = null)
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
    function __toString()
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
    function __call($method, $args)
    {
        return false;
    }

    /**
     * Клонирование объекта
     */
    function __clone()
    {
        foreach ($this->_children as $name => $child){
            $this->_children[$name] = clone $child;
        }
    }

    /**
     * Значения внутренных свойств объекта для трасировки
     * @return array
     */
    function trace()
    {
        //$trace['hash'] = spl_object_hash($this);
        $trace['_attribs'] = $this->_attribs;
        $trace['_changed'] = $this->_changed;
        $trace['_checked'] = $this->_checked;
        $trace['_autoname'] = $this->_autoname;
        $trace['_proto'] = $this->_proto;
        $trace['_parent'] = $this->_parent;
//        $trace['_cond'] = $this->_cond;
        $trace['_children'] = $this->_children;
        return $trace;
    }
}