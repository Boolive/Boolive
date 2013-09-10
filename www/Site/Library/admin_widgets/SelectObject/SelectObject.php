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
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::entity($this->object_rule->value())->required(),
                        'call' => Rule::string()->default('')->required(),
                        'selected' => Rule::any(
                            Rule::arrays(Rule::entity()),
                            Rule::entity()
                        )
                    )
                )
            )
        );
    }

    protected function initInputChild($input)
    {
        parent::initInputChild($input);
    }

    public function work($v = array())
    {
        if ($this->_input['REQUEST']['call'] == 'selected' && isset($this->_input['REQUEST']['selected'])){
            return $this->selected();
        }
        $v['title'] = $this->title->value();
        $v['submit_title'] = $this->submit_title->value();
        $v['cancel_title'] = $this->cancel_title->value();
        $v['message'] = 'Выделите объект или откройте его и нажмите "'.$v['submit_title'].'" для подтверждения выбора.';
        $v['message2'] = 'Для закрытия диалога нажмите "'.$v['cancel_title'].'".';
        return parent::work($v);
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
