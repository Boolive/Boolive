<?php
/**
 * Действие-переключатель признака is_draft
 * Действие, которое отменяется повторным вызовом. Например, смена признака у объекта
 * @version 1.0
 */
namespace site\library\admin\widgets\Draft;

use boolive\errors\Error;
use boolive\values\Rule;
use site\library\admin\widgets\ToggleAction\ToggleAction;

class Draft extends ToggleAction
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
        $this->_state = $object->isDraft();
    }

    function toggle()
    {
        $result = array();
        // Изменение признака is_draft
        $objects = is_array($this->_input['REQUEST']['object'])? $this->_input['REQUEST']['object'] : array($this->_input['REQUEST']['object']);
        if ($first = reset($objects)){
            $result['changes'] = array();
            $draft = !$first->isDraft();
            foreach ($objects as $o){
//                try{
                    /** @var \boolive\data\Entity $o */
                    $o->isDraft($draft);
                    // @todo Обрабатывать ошибки
                    if ($o->save()){
                        $result['changes'][$o->uri()] = array(
                            'is_draft' => $o->isDraft()
                        );
                    }else{
                        $result['errors'][$o->uri()] = $o->errors()->getUserMessage(true);
                    }
//                }catch (Error $e){
//                    $result['errors'][$o->uri()] = $e->getUserMessage(true);
//                }
            }
            $result['state'] = $first->isDraft();
        }
        return $result;
    }
}