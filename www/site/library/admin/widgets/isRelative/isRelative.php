<?php
/**
 * Относительный
 * Смена признака относительности прототипа.
 * Если создавать новые объекты от объектов с относительным прототипом, то у нового объекта прототип будет автоматически
 * найдет относительно расположения нового объекта. Относительность позволяет создавать наследуемые циклические связи.
 * @version 1.0
 */
namespace site\library\admin\widgets\isRelative;

use boolive\values\Rule;
use site\library\admin\widgets\ToggleAction\ToggleAction;

class isRelative extends ToggleAction
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
        $this->_state = $object->isRelative();
    }

    function toggle()
    {
        $result = array();
        // Изменение признака is_mandatory
        $objects = is_array($this->_input['REQUEST']['object'])? $this->_input['REQUEST']['object'] : array($this->_input['REQUEST']['object']);
        if ($first = reset($objects)){
            $result['changes'] = array();
            $prop = !$first->isRelative();
            foreach ($objects as $o){
                try{
                    /** @var \boolive\data\Entity $o */
                    $o->isRelative($prop);
                    // @todo Обрабатывать ошибки
                    $o->save();
                    $result['changes'][$o->uri()] = array(
                        'is_relative' => $o->isRelative()
                    );
                }catch (Error $e){
                    $result['errors'][$o->uri()] = $e->getUserMessage(true);
                }
            }
            $result['state'] = $first->isRelative();
        }
        return $result;
    }
}