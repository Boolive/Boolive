<?php
/**
 * Бефер данных
 * Содержит используемые экземпляры объектов для оптимизации повтороного обращения к ним
 * Может использоваться для буферирования любых данных
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\data;

class Buffer
{
    private static $list = array();

    /**
     * Запись в буфер данных
     * @param string $key Ключ данных
     * @param mixed $data Данные
     * @return mixed
     */
    public static function set($key, $data)
    {
        return self::$list[$key] = $data;
    }

    /**
     * Выбор данных из буфера
     * @param $key Ключ данных
     * @return mixed
     */
    public static function get($key)
    {
        return self::isExist($key)? self::$list[$key] : null;
    }

    /**
     * Удаление данных из буфера
     * @param $key Ключ данных
     */
    public static function remove($key)
    {
        if (array_key_exists($key, self::$list)){
            unset(self::$list[$key]);
        }
    }

    /**
     * Очистка буфера
     */
    public static function clear()
    {
        self::$list = array();
    }

    /**
     * Проверка существования ключа
     * @param $key Ключ данных
     * @return bool
     */
    public static function isExist($key)
    {
        return array_key_exists($key, self::$list);
    }
}