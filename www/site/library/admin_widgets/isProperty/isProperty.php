<?php
/**
 * Свойство
 * Смена признака "свойство" у выделенных объектов в админке
 * @version 1.0
 */
namespace Site\library\admin_widgets\isProperty;

use Boolive\values\Rule;
use Site\library\admin_widgets\ToggleAction\ToggleAction;

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
        /** @var \Boolive\data\Entity $object */
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
                try{
                    /** @var \Boolive\data\Entity $o */
                    $o->isProperty($prop);
                    // @todo Обрабатывать ошибки
                    $o->save();
                    $result['changes'][$o->uri()] = array(
                        'is_relative' => $o->isProperty()
                    );
                }catch (Error $e){
                    $result['errors'][$o->uri()] = $e->getUserMessage(true);
                }
            }
            $result['state'] = $first->isProperty();
        }
        return $result;
    }
}