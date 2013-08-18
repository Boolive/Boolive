<?php
/**
 * Виджет выбора объекта
 * Представляет собой обозреватель объектов с функцией выбора объекта.
 * @version 1.0
 */
namespace Library\admin_widgets\SelectObject;

use Library\views\Widget\Widget;

class SelectObject extends Widget
{
    protected function initInputChild($input)
    {
        parent::initInputChild($input);
//        $this->_input_child['REQUEST']['view_name'] = "Explorer";
    }

    public function work($v = array())
    {
        $v['title'] = $this->title->value();
        $v['submit_title'] = $this->submit_title->value();
        $v['cancel_title'] = $this->cancel_title->value();
        $v['message'] = 'Выделите объект или откройте его и нажмите "'.$v['submit_title'].'" для подтверждения выбора';
        $v['message2'] = 'Для закрытия диалога выбора нажмите "'.$v['cancel_title'].'"';
        return parent::work($v);
    }
}
