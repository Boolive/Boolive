<?php
/**
 * Автоматический список виджетов
 * Отображает все свойства объекта в соответсвии с установленными вараинтами отображения.
 * Имеет настройки фильтра, какие свойства объекта отображать. (@todo)
 * @version 1.0
 */
namespace site\library\views\AutoWidgetList2;

use site\library\views\Widget\Widget;

class AutoWidgetList2 extends Widget
{
    function show($v = array(), $commands, $input)
    {
        $list = $this->getList();
        $i = 1;
        $v['views'] = array();
        $v['props'] = array();
        $v['children'] = array();
        if (is_array($list)){
            foreach ($list as $key => $object){
                $result = $this->showObject($object, $i);
                if ($result !== false){
                    $v['views'][$key] = $result;
                    if ($object->isProperty()){
                        $v['props'][$key] = $result;
                    }else{
                        $v['children'][$key] = $result;
                    }
                    $i++;
                }
            }
        }
        $this->_input_child['REQUEST']['object'] = $this->_input['REQUEST']['object'];
        return parent::show($v, $commands, $input);
    }

    function showObject($object, $number = 1)
    {
        $this->_input_child['REQUEST']['object'] = $object;
        $this->_input_child['REQUEST']['number'] = $number;
        return $this->startChild('views');
    }

    protected function getList($cond = array())
    {
        if (!isset($cond['key'])){
            $cond['key'] = 'name';
        }
        //$cond['comment'] = 'read list of objects in the AutoWidgetList2';
        return $this->_input['REQUEST']['object']->find($cond, true);
    }
}