<?php
/**
 * Укороченный Текст
 * Отображает фрагмент текста вместо всего
 * @version 1.0
 */
namespace Library\content_widgets\TextPreview;

use Library\views\Widget\Widget;

class TextPreview extends Widget
{
    function show($v = array(), $commands, $input)
    {
        $text = $this->_input['REQUEST']['object']->value();
        $length = $this->length->value();
        if ($length < mb_strlen($text)){
            $v['value'] = mb_substr($text, 0, $length);
        }else{
            $v['value'] = $text;
        }
        return parent::show($v,$commands, $input);
    }
}