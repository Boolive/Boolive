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
            $this->_input_child['REQUEST']['object'] = $object;
            if ($result = $this->startChild('switch_views')){
                $v['view'][$object->getName()] = $result;
            }
        }
        $this->_input_child['REQUEST']['object'] = $this->_input['REQUEST']['object'];
        return parent::work($v);
    }

    protected function getList(){
        // @todo Сделать настраиваемый фильтр
        return $this->_input['REQUEST']['object']->findAll2(array(
                'where' => array(
                    array('attr', 'is_history', '=', 0),
                    array('attr', 'is_delete', '=', 0),
                    array('attr', 'is_hidden', '=', 0),
                ),
                'order' => array(
                    array('order', 'ASC')
                )
            ), true);

            //findAll(array('where' => 'is_history=0 and is_delete=0 and is_hidden=0', 'order' =>'`order` ASC'), true);
    }
}