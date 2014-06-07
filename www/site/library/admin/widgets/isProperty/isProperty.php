<?php
/**
 * Свойство
 * Смена признака "свойство" у выделенных объектов в админке
 * @version 1.0
 */
namespace site\library\admin\widgets\isProperty;

use boolive\values\Rule;
use site\library\admin\widgets\ToggleAction\ToggleAction;

class isProperty extends ToggleAction
{
    function startRule()
    {
        $rule = parent::startRule();
        $rule->arrays[0]['REQUEST']->arrays[0]['select'] = Rule::in(null, 'structure', 'property', 'heirs', 'protos')->required();
        return $rule;
    }

    protected function initState()
    {
        /** @var \boolive\data\Entity $object */
        $object = is_array($this->_input['REQUEST']['object'])? reset($this->_input['REQUEST']['object']) : $this->_input['REQUEST']['object'];
        $this->_state = $object->isProperty();
    }

    function toggle()
    {
        $result = array();
        // Изменение признака is_mandatory
        $objects = is_array($this->_input['REQUEST']['object'])? $this->_input['REQUEST']['object'] : array($this->_input['REQUEST']['object']);
        if ($first = reset($objects)){
            $result['changes'] = array();
            $prop = !$first->isProperty();
            foreach ($objects as $o){
//                try{
                    /** @var \boolive\data\Entity $o */
                    $o->isProperty($prop);
                    // @todo Обрабатывать ошибки
                    if ($o->save()){
                        $result['changes'][$o->uri()] = array(
                            'is_relative' => $o->isProperty()
                        );
                    }else{
                        $result['errors'][$o->uri()] = $o->errors()->getUserMessage(true);
                    }
//                }catch (Error $e){

//                }
            }
            $result['state'] = $first->isProperty();
        }
        return $result;
    }
}