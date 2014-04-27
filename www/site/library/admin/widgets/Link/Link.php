<?php
/**
 * Действие-переключатель призанка is_link
 * @version 1.0
 */
namespace site\library\admin\widgets\Link;

use boolive\values\Rule;
use site\library\admin\widgets\ToggleAction\ToggleAction;

class Link extends ToggleAction
{
    function startRule()
    {
        $rule = parent::startRule();
        $rule->arrays[0]['REQUEST']->arrays[0]['select'] = Rule::in('protos')->required();
        return $rule;
    }
    
    protected function initState()
    {
        /** @var \boolive\data\Entity $object */
        $object = is_array($this->_input['REQUEST']['object'])? reset($this->_input['REQUEST']['object']) : $this->_input['REQUEST']['object'];
        $this->_state = $object->isLink();
    }

    function toggle()
    {
        $result = array();
        // Изменение признака is_link
        $objects = is_array($this->_input['REQUEST']['object'])? $this->_input['REQUEST']['object'] : array($this->_input['REQUEST']['object']);
        if ($first = reset($objects)){
            $result['changes'] = array();
            $link = !$first->isLink();
            foreach ($objects as $o){
                /** @var \boolive\data\Entity $o */
                $o->isLink($link);
                // @todo Обрабатывать ошибки
                $o->save();
                $result['changes'][$o->uri()] = array(
                    'is_link' => $o->isLink()
                );
            }
            $result['state'] = $first->isLink();
        }
        return $result;
    }
}