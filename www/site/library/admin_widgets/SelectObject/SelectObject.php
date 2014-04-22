<?php
/**
 * Виджет выбора объекта
 * Представляет собой обозреватель объектов с функцией выбора объекта.
 * @version 1.0
 */
namespace site\library\admin_widgets\SelectObject;

use site\library\views\Widget\Widget,
    boolive\values\Rule;

class SelectObject extends Widget
{
    /**
     * Возвращает правило на входящие данные
     * @return null|\boolive\values\Rule
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
                'is_link' => Rule::bool()->default(false)->required(),
                'select' => Rule::string()->default('structure')->required()
            ))
        ));
    }

    function work()
    {
        if ($this->_input['REQUEST']['call'] == 'selected' && isset($this->_input['REQUEST']['selected'])){
            return $this->selected();
        }
        return parent::work();
    }

    function show($v = array(), $commands, $input)
    {
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
