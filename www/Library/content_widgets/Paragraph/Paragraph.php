<?php
/**
 * Виджет для абзаца текста
 *
 * @version 1.0
 */
namespace Library\content_widgets\Paragraph;

use Library\views\Widget\Widget;

class Paragraph extends Widget
{
    public function work($v = array())
    {
        $v['value'] = $this->_input['GET']['object']->getValue();
        return parent::work($v);
    }
}
