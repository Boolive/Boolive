<?php
/**
 * Дата
 * Отображение даны
 * @version 1.0
 */
namespace site\library\content_widgets\Date;

use site\library\views\Widget\Widget;

class Date extends Widget
{
    function show($v = array(), $commands, $input)
    {
        $v['value'] = date($this->format->inner(), $this->_input['REQUEST']['object']->linked()->value());
        return parent::show($v,$commands, $input);
    }
}