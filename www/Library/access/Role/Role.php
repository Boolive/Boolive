<?php
/**
 * Роль
 * Сгруппированные права доустапа
 *
 * @version 1.0
 * @date 30.12.2012
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\access\Role;

use Boolive\data\Entity;

class Role extends Entity
{
    public function getAccessCond($action_kind, $parent = '', $depth = null)
    {
        $roles = $this->$action_kind->find(array('comment' => 'read role action conditions'));
        $curr = null;
        $prev = null;
        $cond = null;
        // Объединяем права в общий список
        foreach ($roles as $r){
            if (($c = $r->getAccessCond($action_kind, $parent, $depth)) && is_array($c)){
                $need = ($c[0] == 'not')?'all':'any';
                if (is_null($cond)){
                    $cond = array($need, array($c));
                }else
                if ($curr!=$need){
                    if (sizeof($cond[1]) == 1) $cond = $cond[1][0];
                    $cond = array($need, array($cond, $c));
                }else{
                    // Оптимизация any of
                    if ($need == 'any' && $c[0] == 'of' && $prev[0] == 'of'){
                        $cond[1][sizeof($cond[1])-1][1][] = $c[1][0];
                    }else
                    // Оптимизаци all not of
                    if ($need == 'all' && $c[0] == 'not' && sizeof($c[1]) == 2 && $c[1][0] == 'of' && $prev[0] == 'not' && sizeof($prev[1]) == 2 && $prev[1][0] == 'of'){
                        $cond[1][sizeof($cond[1])-1][1][1][] = $c[1][1][0];
                    }
                    else{
                        $cond[1][] = $c;
                    }
                }
                $curr = $need;
                $prev = $c;
            }
        }
        if (isset($cond) && sizeof($cond[1]) == 1) $cond = $cond[1][0];
        return $cond;
    }
}