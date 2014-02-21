<?php
/**
 * Значение
 * Отображает значение объекта
 * @version 1.0
 */
namespace Site\library\content_widgets\SimpleValue;

use Site\library\views\Widget\Widget;

class SimpleValue extends Widget
{
    function show($v = array(), $commands, $input)
    {
        $v['value'] = $this->_input['REQUEST']['object']->value();
        return parent::show($v,$commands, $input);
    }
}