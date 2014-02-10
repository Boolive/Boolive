<?php
/**
 * Добавить
 * Выберите объекты, которые хотите добавить
 * @version 1.0
 */
namespace Site\library\admin_widgets\Add;

use Boolive\values\Rule;
use Site\library\admin_widgets\SelectObject\SelectObject;

class Add extends SelectObject
{
    function startRule()
    {
        $rule = parent::startRule();
        $rule->arrays[0]['REQUEST']->arrays[0]['select'] = Rule::in(null, 'structure', 'property')->required();
        $rule->arrays[0]['REQUEST']->arrays[0]['have_selection'] = Rule::not('true')->required();
        return $rule;
    }

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
                if ($this->_input['REQUEST']['is_link']){
                    $obj->isLink(true);
                }
                if ($this->_input['REQUEST']['select'] == 'property'){
                    $obj->isProperty(true);
                }
                // @todo Обрабатывать ошибки
                $obj->save(false);
                $result['changes'][$obj->uri()] = array(
                    'uri' => $obj->uri()
                );
            }
        }
        return $result;
    }
}