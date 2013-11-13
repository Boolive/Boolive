<?php
/**
 * Виджет выбора объекта
 * Представляет собой обозреватель объектов с функцией выбора объекта.
 * @version 1.0
 */
namespace Library\admin_widgets\SelectObject;

use Library\views\Widget\Widget,
    Boolive\values\Rule;

class SelectObject extends Widget
{
    /**
     * Возвращает правило на входящие данные
     * @return null|\Boolive\values\Rule
     */
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::entity($this->object_rule->value())->required(),
                'call' => Rule::string()->default('')->required(),
                'selected' => Rule::any(
                    Rule::arrays(Rule::entity()),
                    Rule::entity()
                ),
                'is_link' => Rule::bool()->default(false)->required()
            ))
        ));
    }

    function startInitChild($input)
    {
        parent::startInitChild($input);
    }

    function show($v = array(), $commands, $input)
    {
        if ($this->_input['REQUEST']['call'] == 'selected' && isset($this->_input['REQUEST']['selected'])){
            return $this->selected();
        }
        $v['title'] = $this->title->value();
        $v['submit_title'] = $this->submit_title->value();
        $v['cancel_title'] = $this->cancel_title->value();
        $v['message'] = 'Выделите объект или откройте его и нажмите "'.$v['submit_title'].'" для подтверждения выбора.';
        $v['message2'] = 'Для закрытия диалога нажмите "'.$v['cancel_title'].'".';
        return parent::show($v, $commands, $input);
    }

    /**
     * Выполнение действия с выбранными объектами
     * @return bool
     */
    protected function selected()
    {
        return false;
    }
}
