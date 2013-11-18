<?php
/**
 * Действие-переключатель признака is_draft
 * Действие, которое отменяется повторным вызовом. Например, смена признака у объекта
 * @version 1.0
 */
namespace Library\admin_widgets\Draft;

use Boolive\errors\Error;
use Library\admin_widgets\ToggleAction\ToggleAction;

class Draft extends ToggleAction
{
    protected function initState()
    {
        /** @var \Boolive\data\Entity $object */
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
                    /** @var \Boolive\data\Entity $o */
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