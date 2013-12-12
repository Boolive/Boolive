<?php
/**
 * Пункт объекта
 * 
 * @version 1.0
 */
namespace Library\admin_widgets\BaseExplorer\views\Item;

use Boolive\data\Entity;
use Library\views\Widget\Widget;

class Item extends Widget
{
    function show($v = array(), $commands, $input)
    {
        /** @var $obj \Boolive\data\Entity */
        $obj = $this->_input['REQUEST']['object'];
        // Заголовк и описание объекта
        $v['title'] = $obj->title->inner()->value();
        if (empty($v['title'])) $v['title'] = $obj->name();
        $v['description'] = $obj->description->inner()->value();

        // Атрибуты
        $v['name'] = $obj->name();
        $v['value'] = (string)$obj->value();
        $v['uri'] = $obj->uri(false, true);
        $v['id'] = $obj->key();
        $v['is_hidden'] = $obj->isHidden(null, false);
        $v['is_draft'] = $obj->isDraft(null, false);
        $v['is_file'] = $obj->isFile();
        $v['is_link'] = $obj->isLink();
        $v['is_mandatory'] = $obj->isMandatory();
        $v['is_default_value'] = $obj->isDefaultValue();
        $v['link'] = $obj->linked()->uri(false, true);
        if ($p = $obj->proto()){
            $v['newlink'] = $obj->proto()->linked()->uri();
        }else{
            $v['newlink'] = Entity::ENTITY_ID;
        }
        $v['value'] = $obj->value();
        $v['value_short'] = mb_substr($v['value'], 0, 50);
        if ($v['value_short'] != $v['value']){
            $v['value_short'].= '...';
        }
        $v['diff'] = $obj->diff();
        return parent::show($v,$commands, $input);
    }
}