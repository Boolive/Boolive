<?php
/**
 * Модуль кэширования
 *
 * @version 1.0
 * @date 13.06.2013
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\cache;

use Boolive\functions\F;

class Cache
{
    /** @const  Файл конфигурации модулей кэширования */
    const CONFIG_FILE = 'config.cache.php';
    /** @var array Конфигурация кэш-хранилищ */
    private static $config_stores;
    /** @var array Экземпляры кэш-хранилищ */
    private static $stores;

    static function activate()
    {
        self::$config_stores = F::loadConfig(DIR_SERVER.self::CONFIG_FILE, 'stores');
    }

    static function get($key, $time = 0)
    {
        // Определение хранилища по URI
        if ($store = self::getStore($key)){
            return $store->get($key, $time);
        }
        return null;
    }

    static function set($key, $value)
    {
        // Определение хранилища по URI
        if ($store = self::getStore($key)){
            return $store->set($key, $value);
        }
        return false;
    }

    static function delete($key)
    {
        // Определение хранилища по URI
        if ($store = self::getStore($key)){
            return $store->delete($key);
        }
        return false;
    }

    /**
     * Взвращает экземпляр кэша
     * @param $key Путь на объект, для которого определяется хранилище
     * @return \Boolive\data\stores\MySQLStore|null Экземпляр хранилища, если имеется или null, если нет
     */
    static function getStore($key)
    {
        if (is_array($key)) $key = reset($key);
        foreach (self::$config_stores as $ckey => $config){
            if ($ckey == '' || mb_strpos($key, $ckey) === 0){
                if (!isset(self::$stores[$ckey])){
                    self::$stores[$ckey] = new $config['class']($ckey, $config['connect']);
                }
                return self::$stores[$ckey];
            }
        }
        return null;
    }
}