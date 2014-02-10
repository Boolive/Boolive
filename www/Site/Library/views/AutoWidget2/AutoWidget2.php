<?php
/**
 * Автоматический виджет
 * Отображает любой объект в соответсвии с установленными вараинтами
 * @version 1.0
 */
namespace Site\Library\views\AutoWidget2;

use Site\Library\views\Widget\Widget;

class AutoWidget2 extends Widget
{
    function startInitChild($input)
    {
        parent::startInitChild($input);
        $this->_input_child['REQUEST']['object'] = $this->_input['REQUEST']['object'];
    }

    function show($v = array(), $commands, $input)
    {
        $v['views'] = $this->startChild('views');
        return parent::show($v, $commands, $input);
    }
}