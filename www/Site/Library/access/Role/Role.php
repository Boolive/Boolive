<?php
/**
 * Роль
 * Сгруппированные права доустапа
 *
 * @version 1.0
 * @date 30.12.2012
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Site\Library\access\Role;

use Boolive\data\Entity;

class Role extends Entity
{
    function getAccessCond($action_kind, $object = null)
    {
        $action = explode('/', $action_kind, 2);
        if (!isset($action[1])) $action[1] = '';
        return $this->{$action[0]}->linked()->getAccessCond($action[1], $object);
    }
}