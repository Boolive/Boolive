<?php
/**
 * Запрет
 * Запрет доступа к объектам
 *
 * @version 1.0
 * @date 29.12.2012
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\access\Deny;

use Boolive\data\Entity;

class Deny extends Entity
{
    public function getAccessCond($action_kind, $object = null)
    {
        $ids = array();
        $objects = $this->find(array(
            'where' => array(
                array('attr', 'is_link', '>', 0)
            ),
            'comment' => 'read deny linked objects'
        ));
        foreach ($objects as $o){
            $ids[] = $o->proto()->id();
        }
        $kind = $this->value();
        if (!in_array($kind, array('is', 'in', 'of', 'eq', 'childOf', 'heirOf'))){
            $kind = 'eq';
        }
        if ($ids){
            return array('not', array($kind, $ids));
        }
        return null;
    }
}