<?php
/**
 * Модуль данных
 *
 * @link http://boolive.ru/createcms/data-and-entity
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\data;

use Boolive\Boolive,
    Boolive\functions\F,
    Boolive\auth\Auth,
    Boolive\errors\Error;

class Data
{
    /** @const  Файл конфигурации секци */
    const CONFIG_FILE_SECTIONS = 'config.sections.php';
    /** @const  Файл конфигурации индексирования */
    const CONFIG_FILE_INDEXES = 'config.indexes.php';
    /** @var array Конфигурация секционирования */
    private static $config_sections;
    /** @var array Конфигурация индексирования */
    private static $config_indexes;
    /** @var array Экземпляры используемых секций */
    private static $sections;
    /** @var array Экземпляры индексов */
    private static $indexes;
    /** @var array Загруженные объекты */
    public static $buffer = array();

    static function activate(){
        // Конфиг секций
        self::$config_sections = F::loadConfig(DIR_SERVER.self::CONFIG_FILE_SECTIONS);
        // Конфиг индексов
        self::$config_indexes = F::loadConfig(DIR_SERVER.self::CONFIG_FILE_INDEXES);
    }

    /**
     * Выбор объекта по его URI
     * Выполняет поиск реального объекта (сохраненного в секции).
     * Если объекта нет, то создаётся виртуальный.
     * @param string $uri URI объекта
     * @param string $lang Код языка из 3 символов. Если не указан, то выбирается общий
     * @param int $owner Код владельца. Если не указан, то выбирается общий
     * @param null|int $date Дата создания (версия). Если не указана, то выбирается актуальная
     * @param null|bool $is_history Объект в истории (true) или нет (false) или без разницы (null). Если указана дата, то всегда null
     * @param bool $access Признак, проверять или нет доступ
     * @throws \ErrorException
     * @return Entity|null Экземпляр объекта, если найден или null, если не найден
     */
    static function read($uri = '', $lang = '', $owner = 0, $date = null, $is_history = false, $access = true)
    {
        $key = $uri;//.' '.$lang.' '.$owner.' '.$date.' '.$is_history;
        if (Buffer::isExist($key)){
            $object = Buffer::get($key);
        }else{
            $object = null;
            if (is_string($uri)){
                if ($uri === '') return self::makeObject(array('uri' => '', 'value' => null, 'is_logic' => is_file(DIR_SERVER_PROJECT.'Site.php')), true);
                // Определение секции объекта и поиск объекта в секции
                if ($s = self::getSection($uri, true)){
                    $object = $s->read($uri, $lang, $owner, $date, $is_history);
                }
                // Если объект не найден и нужно искать виртуального, то ищем
                if (!isset($object)){
                    // Выбор родителя
                    $names = F::splitRight('/', $uri);
                    if ($parent = self::read($names[0])){
                        // Прототип родителя
                        if (isset($parent['proto'])){
                            $info = Data::getURIInfo($parent['proto']);
                            $proto = Data::read($info['uri'], $info['lang'], $info['owner']);
                        }else{
                            $proto = null;
                        }
                        // Прототип для объекта
                        if ($proto/* = $parent->proto()*/){
                            $proto = Data::read($proto['uri'].'/'.$names[1], (string)$proto['lang'], (int)$proto['owner']);
                            //$proto = $proto->{$names[1]};
                        }
                        if ($proto){
                            $object = $proto->birth();
                            $object['uri'] = $uri;
                            $object['order'] = $proto['order'];
                            $object['lang'] = $lang;
                            $object['owner'] = $owner;
                        }else{
                            $object = new Entity(array('uri' => $uri, 'lang' => $lang, 'owner' => $owner), true, false);
                        }
                        // Объект ссылка?
                        if (!empty($parent['is_link']) || !empty($proto['is_link'])){
                            $object['is_link'] = 1;
                        }
                    }
                }
            }
            Buffer::set($key, $object);
        }
        if ($access && $object){
            if (!Auth::getUser()->checkAccess('read', $object)){
                $object = new Entity(array('uri' => $uri, 'lang' => $lang, 'owner' => $owner), true, false, false);
            }
        };
        return $object;
    }

    /**
     * Поиск объектов
     * Поиск выполняется по индексу
     * @param array $cond Условие поиска в виде многомерного массива.     *
     * @param string $keys Название атрибута, который использовать для ключей массива результата
     * @param bool $access
     * @see https://github.com/Boolive/Boolive/issues/7
     * @example
     * $cond = array(
     *     'from' => array('/Interfaces', 3),           // выбор объектов из /Interfaces в глубину до 3 уровней
     *     'where' => array(                            // услвоия выборки объединенные логическим AND
     *         array('attr', 'uri', '=', '?'),          // сравнение атрибута
     *         array('not', array(                      // отрицание всех условий
     *             array('attr', 'value', '=', '%?%')
     *         )),
     *         array('any', array(                      // услвоия объединенные логическим OR
     *             array('parent', '/Interfaces/html'), // проверка родителя
     *             array('child', array(                // или подчиненного
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
     * @return mixed|array Массив объектов или результат расчета, например, количество объектов
     */
    static function select($cond, $keys = 'name', $access = true)
    {
        // Где искать?
        if (!isset($cond['from'][0])) $cond['from'][0] = '';
        if (!isset($cond['from'][1]) || $cond['from'][1] < 1) $cond['from'][1] = 1;
        if ($access){
            $acond = Auth::getUser()->getAccessCond('read', $cond['from'][0], $cond['from'][1]);
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
            //trace($cond);
        }

        // Определяем индекс и ищем в нём
        if (isset($cond['from'][0]) && ($index = self::getIndex($cond['from'][0]))){
            return $index->select($cond, $keys);
        }else{
            return null;
        }
    }

    /**
     * Сохранение объекта
     * @param Entity $object
     * @param \Boolive\errors\Error $error
     * @return bool
     */
    static function write($object, &$error)
    {
        if ($object->isAccessible() && Auth::getUser()->checkAccess('write', $object)){
            if ($s = self::getSection($object['uri'], true)){
                return $s->write($object);
            }else{
                $error->section = new Error('Не определена секция объекта', 'not-exist');
            }
        }else{
            $error->access = new Error('Нет доступа на запись', 'write');
        }
        return false;
    }





















    /**
     * Выбор объекта по его URI
     * Выборка выполняется по индексу, в котором имеются реальные и виртуальные объекты
     * @param string $uri URI объекта
     * @param string $lang Код языка из 3 символов. Если не указан, то выбирается общий
     * @param int $owner Код владельца. Если не указан, то выбирается общий
     * @param null|int $date Дата создания (версия). Если не указана, то выбирается актуальная
     * @param null|bool $is_history Объект в истории (true) или нет (false) или без разницы (null). Если указана дата, то всегда null
     * @return Entity|null Экземпляр объекта, если найден или null, если не найден
     */
    static function read_from_index($uri = '', $lang = '', $owner = 0, $date = null, $is_history = false)
    {
        return self::getObject($uri, $lang, $owner, $date, $is_history);
        if (is_string($uri)){
            // Корневой объект
            if ($uri === ''){
                return self::makeObject(array('uri' => '', 'value' => null, 'is_logic' => is_file(DIR_SERVER_PROJECT.'Site.php')), true);
            }
            // Поиск объекта в индексе
            if ($index = self::getIndex($uri)){
                return $index->read($uri, $lang, $owner, $date, $is_history);
            }
        }
        return null;
    }



    /**
     * Взвращает экземпляр секции, назначенную для подчиненных объекта
     * Если секции на указнный объект не назначена, то возвращается null
     * @param $uri Путь на объект
     * @param $self Признак, искать секцию объекта (true) или его подчиненных (false)?
     * @param bool $strong Признак, искать точное указание на uri (true) или учитывать подчиенность (false)
     * @return Section|null Экземпляр секции, если имеется или null, если нет
     */
    static function getSection($uri, $self, $strong = false)
    {
        if ($self) $uri = mb_substr($uri, 0, mb_strrpos($uri, '/'));
        if (empty(self::$sections[$uri])){
            self::$sections[$uri] = null;
            $find_uri = $uri;
            while (!isset(self::$config_sections[$find_uri]) && !$strong && !empty($find_uri)){
                $find_uri = mb_substr($find_uri, 0, mb_strrpos($find_uri, '/'));
            }
            if (isset(self::$config_sections[$find_uri])){
                $config = &self::$config_sections[$find_uri];
                if (empty(self::$sections[$find_uri])){
                    // Наследование конфигурации
                    if (isset($config['extends'])){
                        $config = array_replace(self::$config_sections[$config['extends']], $config);
                        unset($config['extends']);
                    }
                    // Создание экземпляара секции
                    if (isset($config['class']) && Boolive::isExist(trim($config['class'],'\\'))){
                        self::$sections[$find_uri] = new $config['class']($config);
                    }
                }
                self::$sections[$uri] = self::$sections[$find_uri];
            }
        }
        return self::$sections[$uri];
    }

    /**
     * Взвращает экземпляр системы индексирования, назначенную для ветки объектов
     * @param $uri Путь на объект
     * @return Index|null Экземпляр секции, если имеется или null, если нет
     */
    static function getIndex($uri)
    {
        if (empty(self::$indexes[$uri])){
            self::$indexes[$uri] = null;
            $find_uri = $uri;
            while (!isset(self::$config_indexes[$find_uri]) && !empty($find_uri)){
                $find_uri = mb_substr($find_uri, 0, mb_strrpos($find_uri, '/'));
            }
            if (isset(self::$config_indexes[$find_uri])){
                $config = &self::$config_indexes[$find_uri];
                if (empty(self::$indexes[$find_uri])){
                    // Наследование конфигурации
                    if (isset($config['extends'])){
                        $config = array_replace(self::$config_indexes[$config['extends']], $config);
                        unset($config['extends']);
                    }
                    // Создание экземпляара секции
                    if (isset($config['class']) && Boolive::isExist(trim($config['class'],'\\'))){
                        self::$indexes[$find_uri] = new $config['class']($config);
                    }
                }
                self::$indexes[$uri] = self::$indexes[$find_uri];
            }
        }
        return self::$indexes[$uri];
    }




    /**
     * Создание объекта данных из атрибутов
     * @param $attribs
     * @param bool $virtual
     * @param bool $exist
     * @throws \ErrorException
     * @return Entity
     */
    static function makeObject($attribs, $virtual = true, $exist = false)
    {
        if (isset($attribs['uri'])){
            if ($attribs['uri']===''){
                $exist = true;
                $virtual = true;
            }
            if (!empty($attribs['is_logic'])){
                try{
                    // Имеется свой класс?
                    if ($attribs['uri']===''){
                        $class = 'Site';
                    }else{
                        $names = F::splitRight('/', $attribs['uri']);
                        $class = str_replace('/', '\\', trim($attribs['uri'],'/')) . '\\' . $names[1];
                    }
                    return new $class($attribs, $virtual, $exist);
                }catch(\ErrorException $e){
                    // Если файл не найден, то будет использовать класс прототипа или Entity
                    if ($e->getCode() != 2) throw $e;
                }
            }
        }
        if (!empty($attribs['proto']) && ($proto = self::read($attribs['proto'], '', 0, null, false, false))){
            // Класс прототипа
            $class = get_class($proto);
            $obj = new $class($attribs, $virtual, $exist);
        }else{
            // Базовый класс
            $obj = new Entity($attribs, $virtual, $exist);
        }
        return $obj;
    }

    /**
     * Парсинг URI.
     * Возвращается чистый URI, язык и код владельца
     * @param string $uri URI, в котором содержится язык и код владельца
     * @return array
     */
    static function getURIInfo($uri)
    {
        $uri = F::splitRight('/', $uri);
        $names = F::explode('@', $uri[1], -3);
        return array(
            'uri' => $uri[0].'/'.$names[0],
            'owner' => isset($names[1]) && is_numeric($names[1]) ? $names[1] : 0,
            'lang' => isset($names[2])? $names[2]: (isset($names[1]) && !is_numeric($names[1])? $names[1] : '')
        );
    }

    /**
     * Создание полного URI, в котором содержится сам uri, язык и код владельца
     * @param $path Чистый URI
     * @param string $lang Код языка из 3 символов
     * @param int $owner Код владельцы числом
     * @return string
     */
    static function makeURI($path, $lang = '', $owner = 0)
    {
        if ($owner) $path.='@'.$owner;
        if ($lang) $path.='@'.$lang;
        return $path;
    }

    /**
     * Проверка объекта в буфере
     * @param $key
     * @return bool
     */
    public static function bufferExist($key)
    {
        return isset(self::$buffer[$key]) || array_key_exists($key, self::$buffer);
    }

    /**
     * Добавление объекта в буфер
     * @param $key
     * @param $object
     */
    public static function bufferAdd($key, $object)
    {
        self::$buffer[$key] = $object;
    }

    /**
     * Выбор объекта из буфера
     * @param $key
     * @return mixed
     */
    public static function bufferGet($key)
    {
        return  self::$buffer[$key];
    }

    /**
     * Удаление объекта из буфера
     * @param $key
     */
    public static function bufferRemove($key)
    {
        unset(self::$buffer[$key]);
    }
}