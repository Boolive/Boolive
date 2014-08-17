<?php
/**
 * Значение
 * Отображает значение объекта
 * @version 1.0
 */
namespace site\library\content_widgets\SimpleValue;

use site\library\views\Widget\Widget;

class SimpleValue extends Widget
{
    function show($v = array(), $commands, $input)
    {
        $v['title'] = $this->_input['REQUEST']['object']->title->inner()->value();
        $v['value'] = $this->_input['REQUEST']['object']->value();
        return parent::show($v,$commands, $input);
    }
}