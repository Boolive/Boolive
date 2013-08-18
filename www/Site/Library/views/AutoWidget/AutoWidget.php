<?php
/**
 * Автоматический виджет
 * Отображает любой объект в соответсвии с установленными вараинтами
 * @version 1.0
 */
namespace Library\views\AutoWidget;

use Library\views\Widget\Widget;

class AutoWidget extends Widget
{
    protected function initInputChild($input)
    {
        parent::initInputChild($input);
        $this->_input_child['REQUEST']['object'] = $this->_input['REQUEST']['object'];
    }

    public function work($v = array())
    {
        $v['view'] = $this->startChild('switch_views');
        return parent::work($v);
    }
}