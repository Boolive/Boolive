<?php
/**
 * Список свойств
 * Отображает списком свойства объекта
 * @version 1.0
 */
namespace site\library\content_widgets\PropertyList;

use site\library\views\Widget\Widget;

class PropertyList extends Widget
{
    function show($v = array(), $commands, $input)
    {
        $obj = $this->_input['REQUEST']['object'];
        $v['title'] = $obj->title->value();
        $list = $obj->find(array(
            'where' => array('is_property','=',0)
        ));
        $v['list'] = array();
        foreach ($list as $item){
            $v['list'][] = $item->value();
        }
        if (empty($v['list'])) return false;
        return parent::show($v,$commands, $input);
    }
}