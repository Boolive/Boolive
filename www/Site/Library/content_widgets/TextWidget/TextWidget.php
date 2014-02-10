<?php
/**
 * Текст
 * Отображение текста без форматирования
 * @version 1.0
 */
namespace Site\Library\content_widgets\TextWidget;

use Site\Library\views\Widget\Widget;

class TextWidget extends Widget
{
    function show($v = array(), $commands, $input)
    {
        $v['value'] = $this->_input['REQUEST']['object']->value();
        return parent::show($v,$commands, $input);
    }
}