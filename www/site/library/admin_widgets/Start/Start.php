<?php
/**
 * Быстрый старт
 * 
 * @version 1.0
 */
namespace site\library\admin_widgets\Start;

use boolive\values\Rule;
use site\library\views\Widget\Widget;

class Start extends Widget
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::entity($this->object_rule->value())->required(),
                'call' => Rule::string()->default('')->required(),
                'add' => Rule::arrays(array(
                    'parent' =>  Rule::entity(),
                    'proto' => Rule::entity(),
                    'is_link' => Rule::bool()->default(false)->required()
                )),
                'select' => Rule::in(null, 'structure')
            ))
        ));
    }

    function work()
    {
        if ($this->_input['REQUEST']['call'] == 'add' && isset($this->_input['REQUEST']['add'])){
            return $this->addObject($this->_input['REQUEST']['add']['parent'], $this->_input['REQUEST']['add']['proto'], $this->_input['REQUEST']['add']['is_link']);
        }
        return parent::work();
    }

    function addObject($parent, $proto, $is_link = false)
    {
        $result = array();
        /** @var $proto \boolive\data\Entity */
        $obj = $proto->birth($parent);
        if ($is_link){
            $obj->isLink(true);
        }
        // @todo Обрабатывать ошибки
        $obj->save(false);
        $result = array(
            'uri' => $obj->uri()
        );
        return $result;
    }
}