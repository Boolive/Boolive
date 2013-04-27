<?php
/**
 * Автоматический список виджетов
 * Отображает все свойства объекта в соответсвии с установленными вараинтами отображения.
 * Имеет настройки фильтра, какие свойства объекта отображать. (@todo)
 * @version 1.0
 */
namespace Library\views\AutoWidgetList;

use Library\views\SwitchCase\SwitchCase;
use Library\views\Widget\Widget;

class AutoWidgetList extends Widget
{
    public function work($v = array())
    {
        $list = $this->getList();
        $v['view'] = array();
        foreach ($list as $object){
            $this->_input_child['REQUEST']['object'] = $object;
            if ($result = $this->startChild('switch_views')){
                $v['view'][$object->name()] = $result;
            }
        }
        $this->_input_child['REQUEST']['object'] = $this->_input['REQUEST']['object'];
        return parent::work($v);
    }

    protected function getList($cond = array())
    {
        $cases = $this->linked(true)->switch_views->linked(true)->getCases();
        $cnt = sizeof($cases);
        $protos = array();
        while ($cnt > 0){
            $cnt--;
            if ($cases[$cnt] instanceof SwitchCase){
                if ($cases[$cnt]->value() == 'all'){
                    $protos = array();
                    $cnt = 0;
                }else{
                    $protos[] = $cases[$cnt]->value();
                }
            }
        }
        if (empty($cond['where'])){
            $cond['where'] = array('is', $protos);
        }else
        if (is_array($cond['where'][0])){
            $cond['where'][] = array('is', $protos);
        }else
        if ($cond['where'][0] == 'all'){
            $cond['where'][] = array('is', $protos);
        }else{
            $cond['where'][] = array(
                $cond['where'],
                array('is', $protos)
            );
        }
        return $this->_input['REQUEST']['object']->find($cond, 'name', true);
    }
}