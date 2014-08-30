<?php
/**
 * Бефер сущностей
 *
 * @version 2.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace boolive\data;

class Buffer2
{
    static private $list_id = array();
    static private $list_uri = array();

    static function activate()
    {

    }


    static function get($key)
    {
        if (isset(self::$list_id[$key])){
            return self::$list_id[$key];
        }else
        if (isset(self::$list_uri[$key])){
            return self::$list_uri[$key];
        }
        return null;
    }

    /**
     * @param Entity $entity
     */
    static function set($entity)
    {
        self::$list_id[$entity->uri()] = $entity;
        if ($id = $entity->id()){
            self::$list_id[$id] = &self::$list_id[$entity->uri()];
        }
    }

    static function remove($key)
    {
        if ($obj = self::get($key)){
            if (isset(self::$list_id[$obj->id()])) unset(self::$list_id[$obj->id()]);
            if (isset(self::$list_uri[$obj->uri()])) unset(self::$list_uri[$obj->uri()]);
        }
    }

    static function isExists($key)
    {
        return isset($key) && (isset(self::$list_id[$key]) || isset(self::$list_uri[$key]));
    }
}
 