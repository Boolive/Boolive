<?php
/**
 * Текст
 * Отображение текста без форматирования
 * @version 1.0
 */
namespace Site\library\content_widgets\TextWidget;

use Site\library\views\Widget\Widget;

class TextWidget extends Widget
{
    function show($v = array(), $commands, $input)
    {
        $v['value'] = $this->_input['REQUEST']['object']->value();
        return parent::show($v,$commands, $input);
    }
}