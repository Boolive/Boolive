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
namespace Library\access\Allow;

use Boolive\data\Entity;

class Allow extends Entity
{
    public function getAccessCond($action_kind, $parent = '', $depth = null)
    {
        $ids = array();
        $objects = $this->find();
        foreach ($objects as $o){
            /** @var $o Entity */
            $ids[] = $o->linked()->id();
        }
        if ($ids){
            return array('of', $ids);
        }
        return null;
    }
}