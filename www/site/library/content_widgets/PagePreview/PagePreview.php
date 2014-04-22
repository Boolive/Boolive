<?php
/**
 * Виджет анонса страницы
 * @author: polinа Putrolaynen
 * @date: 24.04.13
 *
 */
namespace site\library\content_widgets\PagePreview;

use boolive\input\Input;
use site\library\views\AutoWidgetList2\AutoWidgetList2;

class PagePreview extends AutoWidgetList2{

    function show($v = array(), $commands, $input)
    {
        $v['href'] = Input::url($this->_input['REQUEST']['object']->uri());
        return parent::show($v, $commands, $input);
    }
}