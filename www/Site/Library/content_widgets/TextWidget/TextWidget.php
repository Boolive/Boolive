<?php
/**
 * Текст
 * Отображение текста без форматирования
 * @version 1.0
 */
namespace Library\content_widgets\TextWidget;

use Library\views\Widget\Widget;

class TextWidget extends Widget
{
    function show($v = array(), $commands, $input)
    {
        $v['value'] = $this->_input['REQUEST']['object']->value();
        return parent::show($v,$commands, $input);
    }
}