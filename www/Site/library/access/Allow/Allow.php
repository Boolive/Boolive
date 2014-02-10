<?php
/**
 * Допуск
 *
 * Право доступа к объектам
 *
 * @version 1.0
 * @date 29.12.2012
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Site\library\access\Allow;

use Boolive\data\Entity;

class Allow extends Entity
{
    function getAccessCond($action_kind, $object = null)
    {
        $ids = array();
        $objects = $this->find(array(
            'where' => array(
                array('attr', 'is_link', '>', 0)
            ),
            'comment' => 'read allow linked children'
        ));
        foreach ($objects as $o){
            /** @var $o Entity */
            $ids[] = $o->proto()->id();
        }
        $kind = $this->value();
        if (!in_array($kind, array('is', 'in', 'of', 'eq', 'childOf', 'heirOf'))){
            $kind = 'eq';
        }
        if ($ids){
            return array($kind, $ids);
        }
        return null;
    }
}