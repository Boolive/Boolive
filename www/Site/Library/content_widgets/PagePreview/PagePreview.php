<?php
/**
 * Виджет анонса страницы
 * @author: polinа Putrolaynen
 * @date: 24.04.13
 *
 */
namespace Library\content_widgets\PagePreview;

use Library\views\AutoWidgetList\AutoWidgetList;

class PagePreview extends AutoWidgetList{

    public function work($v = array())
    {
        $v['object'] = $this->_input['REQUEST']['object']->uri();
        if (substr($v['object'], 0, 10) == '/Contents/'){
            $v['object_uri'] = substr($v['object'], 10);
        }else{
            $v['object_uri'] = $v['object'];
        }
        return parent::work($v);
    }
}