<?php
/**
 * Автоматический виджет
 * Отображает любой объект в соответсвии с установленными вараинтами
 * @version 1.0
 */
namespace Library\views\AutoWidget2;

use Library\views\Widget\Widget;

class AutoWidget2 extends Widget
{
    protected function initInputChild($input)
    {
        parent::initInputChild($input);
        $this->_input_child['REQUEST']['object'] = $this->_input['REQUEST']['object'];
    }

    public function work($v = array())
    {
        $v['views'] = $this->startChild('views');
        return parent::work($v);
    }
}