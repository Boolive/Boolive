<?php
/**
 * Отображение объекта в виде пункта списка
 *
 * @version 1.0
 */
namespace Library\admin_widgets\ObjectItem;

use Library\views\Widget\Widget;

class ObjectItem extends Widget
{
    public function work($v = array())
    {
        $obj = $this->_input['REQUEST']['object'];
        $v['name'] = $obj->{'title'}->getValue();
        if (empty($v['name'])) $v['name'] = $obj->getName();
        //$v['value'] = (string)$obj->getValue();
        $v['uri'] = $obj['uri'];
        return parent::work($v);
    }
}