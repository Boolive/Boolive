<?php
/**
 * Действие-переключатель признака is_draft
 * Действие, которое отменяется повторным вызовом. Например, смена признака у объекта
 * @version 1.0
 */
namespace site\library\admin_widgets\Draft;

use boolive\errors\Error;
use boolive\values\Rule;
use site\library\admin_widgets\ToggleAction\ToggleAction;

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
        $this->_state = $object->isDraft(null, false);
    }

    function toggle()
    {
        $result = array();
        // Изменение признака is_draft
        $objects = is_array($this->_input['REQUEST']['object'])? $this->_input['REQUEST']['object'] : array($this->_input['REQUEST']['object']);
        if ($first = reset($objects)){
            $result['changes'] = array();
            $draft = !$first->isDraft(null, false);
            foreach ($objects as $o){
                try{
                    /** @var \boolive\data\Entity $o */
                    $o->isDraft($draft);
                    // @todo Обрабатывать ошибки
                    $o->save();
                    $result['changes'][$o->uri()] = array(
                        'is_draft' => $o->isDraft(null, false)
                    );
                }catch (Error $e){
                    $result['errors'][$o->uri()] = $e->getUserMessage(true);
                }
            }
            $result['state'] = $first->isDraft(null, false);
        }
        return $result;
    }
}