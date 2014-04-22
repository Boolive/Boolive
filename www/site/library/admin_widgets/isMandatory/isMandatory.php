<?php
/**
 * Обязательный
 * Смена признака "свойство" у объектов
 * @version 1.0
 */
namespace site\library\admin_widgets\isMandatory;

use boolive\errors\Error;
use boolive\values\Rule;
use site\library\admin_widgets\ToggleAction\ToggleAction;

class isMandatory extends ToggleAction
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
        $this->_state = $object->isMandatory();
    }

    function toggle()
    {
        $result = array();
        // Изменение признака is_mandatory
        $objects = is_array($this->_input['REQUEST']['object'])? $this->_input['REQUEST']['object'] : array($this->_input['REQUEST']['object']);
        if ($first = reset($objects)){
            $result['changes'] = array();
            $prop = !$first->isMandatory();
            foreach ($objects as $o){
                try{
                    /** @var \boolive\data\Entity $o */
                    $o->isMandatory($prop);
                    // @todo Обрабатывать ошибки
                    $o->save();
                    $result['changes'][$o->uri()] = array(
                        'is_mandatory' => $o->isMandatory()
                    );
                }catch (Error $e){
                    $result['errors'][$o->uri()] = $e->getUserMessage(true);
                }
            }
            $result['state'] = $first->isMandatory();
        }
        return $result;
    }
}