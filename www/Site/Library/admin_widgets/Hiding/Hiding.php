<?php
/**
 * Действие скрытия/отмена скрытия объектов
 * @version 1.0
 */
namespace Site\Library\admin_widgets\Hiding;

use Boolive\values\Rule;
use Site\Library\admin_widgets\ToggleAction\ToggleAction;

class Hiding extends ToggleAction
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
        $this->_state = $object->isHidden(null, false);
    }

    function toggle()
    {
        $result = array();
        // Изменение признака is_hidden
        $objects = is_array($this->_input['REQUEST']['object'])? $this->_input['REQUEST']['object'] : array($this->_input['REQUEST']['object']);
        if ($first = reset($objects)){
            $result['changes'] = array();
            $hide = !$first->isHidden(null, false);
            foreach ($objects as $o){
                /** @var \Boolive\data\Entity $o */
                $o->isHidden($hide);
                // @todo Обрабатывать ошибки
                $o->save();
                $result['changes'][$o->uri()] = array(
                    'is_hidden' => $o->isHidden(null, false)
                );
            }
            $result['state'] = $first->isHidden(null, false);
        }
        return $result;
    }
}