<?php
/**
 * Выбор из списка
 * Выбор объекта, на которого ссылаться
 * @version 1.0
 */
namespace site\library\forms\fields\SelectField;

use boolive\data\Data2;
use site\library\forms\fields\Field\Field;

class SelectField extends Field
{
    function show($v = array(), $commands, $input)
    {
        $list = $this->list->linked()->find(array('where'=>array('is_property','=',0), 'group'=>true));
        $v['options'] = array();
        $active = $this->_input['REQUEST']['object']->linked();
        foreach ($list as $item){
            $item = $item->linked();
            $v['options'][] = array(
                'title' => $item->title->inner()->value(),
                'value' => $item->id(),
                'selected' => $active->eq($item)
            );
        }
        $v['title'] = $this->title->inner()->value();
        return parent::show($v,$commands, $input);
    }

    function processCheck()
    {
        if (isset($this->_input['REQUEST']['value'])){
            $obj = $this->_input['REQUEST']['object'];
            $proto = Data2::read($this->_input['REQUEST']['value']);
            if ($proto->isExist()){
                $obj->proto($proto);
            }else{
                $obj->errors()->_attribs->proto = 'Выбранный объект отсутствует на сайте';
            }
            if (!$obj->check()){
                return array('error' => $obj->errors()->getUserMessage(true));
            }
        }
        return true;
    }
}