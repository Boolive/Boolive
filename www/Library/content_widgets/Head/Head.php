<?php
/**
 * Виджет заголовков в тексте
 *
 * @version 1.0
 */
namespace Library\content_widgets\Head;

use Library\views\Widget\Widget;

class Head extends Widget
{
    public function work($v = array())
    {
        $v['value'] = $this->_input['GET']['object']->getValue();
        return parent::work($v);
    }
}
