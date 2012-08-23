<?php
/**
 * Поле обратного адреса
 *
 * @version 1.0
 */
namespace Library\content_widgets\Feedback\option_message\MessageField;

use Library\basic\widgets\Widget\Widget,
    Boolive\values\Rule;

class MessageField extends Widget
{
    public function getInputRule()
    {
        return Rule::arrays(array(
            'GET' => Rule::arrays(array(
                'object' => Rule::entity()->required(), // объект, который отображать
                ), Rule::any() // не удалять другие элементы
            )), Rule::any() // не удалять другие элементы
        );
    }

    public function work($v = array())
    {
        $v['title'] = $this->_input['GET']['object']->title->getValue();
        $v['value'] = $this->_input['GET']['object']->getValue();
        $v['name'] = $this->getName();
        $v['id'] = $this['uri'];
        return parent::work($v);
    }
}
