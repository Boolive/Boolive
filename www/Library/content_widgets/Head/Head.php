<?php
/**
 * Виджет заголовков в тексте
 *
 * @version 1.0
 */
namespace Library\content_widgets\Head;

use Library\basic\widgets\Widget\Widget,
    Boolive\values\Rule;

class Head extends Widget
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
        $v['value'] = $this->_input['GET']['object']->getValue();
        return parent::work($v);
    }
}
