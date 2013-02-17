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
    Boolive\errors\Error,
    Boolive\develop\Trace;

class Data
{
    /** @const  Файл конфигурации хранилищ */
    const CONFIG_FILE_STORES = 'config.stores.php';
    /** @var array Конфигурация хранилищ */
    private static $config_stores;
    /** @var array Экземпляры хранилищ */
    private static $stores;

    static function activate(){
        // Конфиг хранилищ
        self::$config_stores = F::loadConfig(DIR_SERVER.self::CONFIG_FILE_STORES);
    }

    /**
     * Выбор объекта по ключу.
     * @param string|array $key Ключ объекта. Ключём может быть URI, сокращенный URI или массив из объекта-родителя и имени выбираемого подчиненного
     * @param null|Entity $owner Владелец объекта
     * @param null|Entity $lang Язык (локаль) объекта
     * @param int $date Дата создани объекта. Используется в качестве версии
     * @param bool $access Признак, проверять или нет наличие доступа к объекту?
     * @return Entity|null Найденный объект
     */
    static function read($key = '', $owner = null, $lang = null, $date = 0, $access = true)
    {
        $key = self::encodeKey($key);
        // Если $key массив, то $key[0] - родитель, $key[1] - имя подчиненного
        $skey = is_array($key)? ($key[0] instanceof Entity ? $key[0]->key() : $key[0]) : $key;
        // Опредление хранилища по URI
        if ($store = self::getStore($skey)){
            // Выбор объекта
            return $store->read($key, $owner, $lang, $date, $access);
        }
        return null;
    }


    /**
     * Сохранение объекта
     * @param Entity $object Сохраняемый объект
     * @param \Boolive\errors\Error $error Контейнер для ошибок при сохранении
     * @param bool $access Признак, проверять или нет наличие доступа на запись объекта?
     * @return bool Признак, сохранен или нет объект?
     */
    static function write($object, &$error, $access = true)
    {
        if (!$access || ($object->isAccessible() && Auth::getUser()->checkAccess('write', $object))){
            if ($store = self::getStore($object->key())){
                return $store->write($object);
            }else{
                $error->section = new Error('Не определена секция объекта', 'not-exist');
            }
        }else{
            $error->access = new Error('Нет доступа на запись', 'write');
        }
        return false;
    }

    /**
     * Поиск объектов
     * @param array $cond Условие поиска в виде многомерного массива.     *
     * @param string $keys Название атрибута, который использовать для ключей массива результата
     * @param null|Entity $owner Владелец искомых объектов
     * @param null|Entity $lang Язык (локаль) искомых объектов
     * @param bool $access Признак, проверять или нет наличие доступа к объекту?
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
    static function select($cond, $keys = 'name', $owner = null, $lang = null, $access = true)
    {
        // Где искать?
        if (!isset($cond['from'][0])) $cond['from'][0] = '';
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
        }
        // Определяем индекс и ищем в нём
        if (isset($cond['from'][0]) && ($store = self::getStore($cond['from'][0]))){
            return $store->select($cond, $keys, $owner, $lang, $access);
        }else{
            return null;
        }
    }

    /**
     * Проверка, является ли URI сокращенным
     * Если да, то возвращается массив из двух элементов, иначе false
     * Сокращенные URI используются в хранилищах для более оптимального хранения и поиска объектов
     * @param $uri Проверяемый URI
     * @return array|bool
     */
    static function isShortUri($uri)
    {
        $info = F::splitRight('//', $uri);
        return isset($info[0])? $info : false;
    }

    /**
     * Если URI состоит из короткой части и дополнительных параметров, например "//2345/title",
     * тогда возвращается массив из экземпляра сущности с id = 2345 и пути на подчиненного title
     * @param $key
     * @return array[0=>Entity, 1=>string]|string
     */
    static function encodeKey($key)
    {
        if (is_string($key) && ($info1 = self::isShortUri($key))){
            $info2 = explode('/',$info1[1], 2);
            if (sizeof($info2) == 2){
                $info2[0] = Data::read($info1[0].'//'.$info2[0]);
                return $info2;
            }
        }
        return $key;
    }

    /**
     * Взвращает экземпляр хранилища
     * @param $uri Путь на объект, для которого определяется хранилище
     * @return \Boolive\data\stores\MySQLStore|null Экземпляр хранилища, если имеется или null, если нет
     */
    static function getStore($uri)
    {
        foreach (self::$config_stores as $key => $config){
            if ($key == '' || mb_strpos($uri, $key) === 0){
                if (!isset(self::$stores[$key])){
                    self::$stores[$key] = new $config['class']($key, $config['connect']);
                }
                return self::$stores[$key];
            }
        }
        return null;
    }
}