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
                $v['view'][$object->name()] = $result;
            }
        }
        $this->_input_child['REQUEST']['object'] = $this->_input['REQUEST']['object'];
        return parent::work($v);
    }

    protected function getList()
    {
        $cases = $this->linked(true)->switch_views->getCases();
        $cnt = sizeof($cases);
        $protos = array();
        while ($cnt > 0){
            $cnt--;
            if ($cases[$cnt]->value() == 'all'){
                $protos = array();
                $cnt = 0;
            }else{
                $protos[] = $cases[$cnt]->value();
            }
        }
        // @todo Сделать настраиваемый фильтр
        return $this->_input['REQUEST']['object']->find(array(
            'where' => array('is', $protos)
        ), 'name', true);
    }
}