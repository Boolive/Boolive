<?php
/**
 * Виджет для редактирования описания страниц
 * @author: polinа Putrolaynen
 * @date: 16.04.13
 *
 */
namespace Library\admin_widgets\page_widgets\PageDescriptionEditor;

use Library\views\Widget\Widget;

class PageDescriptionEditor extends Widget {

    function show($v = array(), $commands, $input)
    {
        $v['object'] = $this->_input['REQUEST']['object']->uri();
        $v['value'] = $this->_input['REQUEST']['object']->value();
        $v['title'] = $this->_input['REQUEST']['object']->title->value();
        return parent::show($v, $commands, $input);
    }
}