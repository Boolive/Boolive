<?php
/**
 * Дата
 * Отображение даны
 * @version 1.0
 */
namespace Site\library\content_widgets\Date;

use Site\library\views\Widget\Widget;

class Date extends Widget
{
    function show($v = array(), $commands, $input)
    {
        $v['value'] = date($this->format->inner(), $this->_input['REQUEST']['object']->linked()->value());
        return parent::show($v,$commands, $input);
    }
}