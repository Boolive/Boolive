<?php
/**
 * Автоматический список виджетов
 * Отображает все свойства объекта в соответсвии с установленными вараинтами отображения.
 * Имеет настройки фильтра, какие свойства объекта отображать. (@todo)
 * @version 1.0
 */
namespace Library\views\AutoWidgetList2;

use Boolive\data\Data,
    Library\views\Widget\Widget;

class AutoWidgetList2 extends Widget
{
    public function work($v = array())
    {
        $list = $this->getList();
        $v['views'] = array();
        if (is_array($list)){
            foreach ($list as $object){
                $this->_input_child['REQUEST']['object'] = $object;
                if ($result = $this->startChild('views')){
                    $v['views'][$object->name()] = $result;
                }
            }
        }
        $this->_input_child['REQUEST']['object'] = $this->_input['REQUEST']['object'];
        return parent::work($v);
    }

    protected function getList($cond = array())
    {
        $cond['comment'] = 'read list of objects in the AutoWidgetList2';
        return $this->_input['REQUEST']['object']->find($cond, true);
    }
}