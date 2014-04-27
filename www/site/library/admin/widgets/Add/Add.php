<?php
/**
 * Добавить
 * Выберите объекты, которые хотите добавить
 * @version 1.0
 */
namespace site\library\admin\widgets\Add;

use boolive\values\Rule;
use site\library\admin\widgets\SelectObject\SelectObject;

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
        /** @var $parent \boolive\data\Entity */
        $parent = $this->_input['REQUEST']['object'];
        $protos = is_array($this->_input['REQUEST']['selected'])? $this->_input['REQUEST']['selected'] : array($this->_input['REQUEST']['selected']);
        if ($protos){
            foreach ($protos as $proto){
                /** @var $proto \boolive\data\Entity */
                $obj = $proto->birth($parent);
                if ($proto->parent()->eq($parent->proto())){
                    $obj->order($proto->order());
                }
                $obj->isDraft(false);
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