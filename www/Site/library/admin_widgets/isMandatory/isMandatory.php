<?php
/**
 * Обязательный
 * Смена признака "свойство" у объектов
 * @version 1.0
 */
namespace Site\library\admin_widgets\isMandatory;

use Boolive\errors\Error;
use Boolive\values\Rule;
use Site\library\admin_widgets\ToggleAction\ToggleAction;

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
        /** @var \Boolive\data\Entity $object */
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
                    /** @var \Boolive\data\Entity $o */
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