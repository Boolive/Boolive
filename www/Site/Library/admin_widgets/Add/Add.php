<?php
/**
 * Добавить2
 * Выберите объекты, которые хотите добавить
 * @version 1.0
 */
namespace Library\admin_widgets\Add;

use Library\admin_widgets\SelectObject\SelectObject;

class Add extends SelectObject
{
    protected function selected()
    {
        $result = array();
        /** @var $parent \Boolive\data\Entity */
        $parent = $this->_input['REQUEST']['object'];
        $protos = is_array($this->_input['REQUEST']['selected'])? $this->_input['REQUEST']['selected'] : array($this->_input['REQUEST']['selected']);
        if ($protos){
            foreach ($protos as $proto){
                /** @var $proto \Boolive\data\Entity */
                $obj = $proto->birth($parent);
                if ($proto->uri() == '/Library/basic/Object'){
                    $obj->proto(false);
                }
                // @todo Обрабатывать ошибки
                $obj->save(false, false);
                $result['changes'][$obj->uri()] = array(
                    'uri' => $obj->uri()
                );
            }
        }
        return $result;
    }
}