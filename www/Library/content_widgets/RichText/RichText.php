<?php
/**
 * Виджет форматированного текста
 *
 * @version 1.0
 */
namespace Library\content_widgets\RichText;

use Library\views\AutoWidgetList\AutoWidgetList;

class RichText extends AutoWidgetList
{
    public function work($v = array())
    {
        $v['style'] = $this->_input['REQUEST']['object']->style->getStyle();
        return parent::work($v);
    }
}
