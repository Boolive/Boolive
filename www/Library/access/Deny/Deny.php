<?php
/**
 * Запрет
 *
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
    public function getAccessCond($action_kind, $parent = '', $depth = null)
    {
        $ids = array();
        $objects = $this->find(array(
            'where' => array(
                array('attr', 'is_link', '=', 1)
            )
        ));
        foreach ($objects as $o){
            $ids[] = $o->linked()->id();
        }
        if ($ids){
            return array('not', array('of', $ids));
        }
        return null;
    }
}