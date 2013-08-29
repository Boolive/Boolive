<?php
/**
 * Виджет анонса страницы
 * @author: polinа Putrolaynen
 * @date: 24.04.13
 *
 */
namespace Library\content_widgets\PagePreview;

use Library\views\AutoWidgetList2\AutoWidgetList2;

class PagePreview extends AutoWidgetList2{

    public function work($v = array())
    {
        $v['href'] = $this->_input['REQUEST']['object']->uri();
        if (substr($v['href'], 0, 10) == '/Contents/'){
            $v['href'] = substr($v['href'], 10);
        }
        return parent::work($v);
    }
}