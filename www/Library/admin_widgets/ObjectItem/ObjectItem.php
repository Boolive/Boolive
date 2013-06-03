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
        // Заголовк объекта
        if ($obj->title->isExist()){
            $v['title'] = $obj->title->value();
        }else
        if ($obj->isLink()){
            $v['title'] = $obj->linked()->title->value();
        }
        if (empty($v['title'])){
            $v['title'] = $obj->name();
        }
        // Описание объекта
        if ($obj->description->isExist()){
            $v['description'] = $obj->description->value();
        }else
        if ($obj->isLink()){
            $v['description'] = $obj->linked()->description->value();
        }else{
            $v['description'] = '';
        }
        // Атрибуты
        $v['name'] = $obj->name();
        $v['value'] = (string)$obj->value();
        $v['uri'] = $obj->uri(false, true);
        $v['is_virtual'] = $obj->isVirtual();
        $v['is_hidden'] = $obj->isHidden(null, false);
        $v['is_delete'] = $obj->isDelete(null, false);
        $v['is_file'] = $obj->isFile();
        $v['is_link'] = $obj->isLink();
        $v['is_default_value'] = $obj->isDefaultValue();
        $v['link'] = $obj->linked()->uri(false, true);
        if ($v['is_link']){
            $v['alt'] = 'Ссылка: '.$v['link'];
        }else{
            $v['alt'] = 'Объект: '.$v['link'];
        }
        $v['value_full'] = $obj->value();
        $v['value'] = mb_substr($v['value_full'], 0, 50);
        if ($v['value']!=$v['value_full']){
            $v['value'].='...';
        }
        return parent::work($v);
    }
}