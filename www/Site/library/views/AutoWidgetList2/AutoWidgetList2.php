<?php
/**
 * Автоматический список виджетов
 * Отображает все свойства объекта в соответсвии с установленными вараинтами отображения.
 * Имеет настройки фильтра, какие свойства объекта отображать. (@todo)
 * @version 1.0
 */
namespace Site\library\views\AutoWidgetList2;

use Site\library\views\Widget\Widget;

class AutoWidgetList2 extends Widget
{
    function show($v = array(), $commands, $input)
    {
        $list = $this->getList();
        $i = 1;
        $v['views'] = array();
        if (is_array($list)){
            foreach ($list as $object){
                $this->_input_child['REQUEST']['object'] = $object;
                $this->_input_child['REQUEST']['number'] = $i;
                $result = $this->startChild('views');
                if ($result !== false){
                    $v['views'][] = $result;
                    $i++;
                }
            }
        }
        $this->_input_child['REQUEST']['object'] = $this->_input['REQUEST']['object'];
        return parent::show($v, $commands, $input);
    }

    protected function getList($cond = array())
    {
        $cond['comment'] = 'read list of objects in the AutoWidgetList2';
        return $this->_input['REQUEST']['object']->find($cond, true);
    }
}