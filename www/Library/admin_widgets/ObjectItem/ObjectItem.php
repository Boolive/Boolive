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
        /** @var $obj \Boolive\data\Entity */
        $obj = $this->_input['REQUEST']['object'];
        $v['name'] = $obj->name();
        $v['title'] = $obj->title->value();
        if (empty($v['title'])) $v['title'] = $obj->name();
        //$v['value'] = (string)$obj->value();
        $v['uri'] = $obj->uri();
        $v['is_virtual'] = $obj->isVirtual();
        $v['is_hidden'] = $obj->isHidden();
        $v['is_delete'] = $obj->isDelete();
        $v['is_file'] = $obj->isFile();
        $v['value_full'] = $obj->value();
        $v['value'] = mb_substr($v['value_full'], 0, 50);
        if ($v['value']!=$v['value_full']) $v['value'].='...';

        $v['is_default_value'] = $obj->isDefaultValue();
        return parent::work($v);
    }
}