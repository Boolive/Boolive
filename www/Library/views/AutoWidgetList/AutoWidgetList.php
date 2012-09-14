<?php
/**
 * Автоматический список виджетов
 * Отображает все свойства объекта в соответсвии с установленными вараинтами отображения.
 * Имеет настройки фильтра, какие свойства объекта отображать. (@todo)
 * @version 1.0
 */
namespace Library\views\AutoWidgetList;

use Library\views\Widget\Widget;

class AutoWidgetList extends Widget
{
    public function work($v = array())
    {
        $list = $this->getList();
        $v['view'] = array();
        foreach ($list as $object){
            $this->_input_child['GET']['object'] = $object;
            $v['view'][$object->getName()] = $this->startChild('switch_views');
        }
        $this->_input_child['GET']['object'] = $this->_input['GET']['object'];
        return parent::work($v);
    }

    protected function getList(){
        // @todo Сделать настраиваемый фильтр
        return $this->_input['GET']['object']->findAll(array('order' =>'`order` ASC'));
    }
}