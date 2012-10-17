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
        $v['title'] = $this->title->getValue();
        $v['message'] = 'Выделите объект или откройте его';
        $v['submit_title'] = 'Выбрать';
        $v['cancel_title'] = 'Отмена';
        return parent::work($v);
    }
}
