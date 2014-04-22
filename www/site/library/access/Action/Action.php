<?php
/**
 * Действие
 * Действие, на которое устанавливается правило доступа или запрета. Имя объекта соответствует названию действия.
 * @version 1.0
 */
namespace Site\library\access\Action;

use Boolive\data\Entity;

class Action extends Entity
{

    function getAccessCond($action_kind, $object = null)
    {
        $action = explode('/', $action_kind, 2);
        if (!isset($action[1])) $action[1] = '';
        $rights = $this->find(array('comment' => 'read role action conditions'), false, true, false);
        $curr = null;
        $prev = null;
        $cond = null;
        // Объединяем права в общий список
        foreach ($rights as $r){
            if ((!$r instanceof Action) || $r->name()==$action[0]){
                if (($c = $r->linked()->getAccessCond($action[1], $object)) && is_array($c)){
                    $need = ($c[0] == 'not')?'all':'any';
                    if (is_null($cond)){
                        $cond = array($need, array($c));
                    }else
                    if ($curr!=$need){
                        if (count($cond[1]) == 1) $cond = $cond[1][0];
                        $cond = array($need, array($cond, $c));
                    }else{
                        $support = array('eq','is','in','of','childOf','heirOf');
                        // Оптимизация any of
                        if ($need == 'any' && $c[0] == $prev[0] && in_array($c[0], $support)){
                            $cond[1][count($cond[1])-1][1][] = $c[1][0];
                        }else
                        // Оптимизаци all not of
                        if ($need == 'all' && $c[0] == 'not' && count($c[1]) == 2 && count($prev[1]) == 2 && $prev[0] == 'not' && $c[1][0] == $prev[1][0] && in_array($c[1][0], $support)){
                            $cond[1][count($cond[1])-1][1][1][] = $c[1][1][0];
                        }
                        else{
                            $cond[1][] = $c;
                        }
                    }
                    $curr = $need;
                    $prev = $c;
                }
            }
        }
        if (isset($cond) && count($cond[1]) == 1) $cond = $cond[1][0];
        return $cond;
    }
}