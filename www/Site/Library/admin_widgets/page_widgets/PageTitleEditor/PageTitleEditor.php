<?php
/**
 * Виджет редактирования заголовка страницы
 * @author: polinа Putrolaynen
 * @version 1.0
 */
namespace Library\admin_widgets\page_widgets\PageTitleEditor;

use Library\views\Widget\Widget;

class PageTitleEditor extends Widget
{
    function show($v = array(), $commands, $input)
    {
        $v['object'] = $this->_input['REQUEST']['object']->uri();
        $v['value'] = $this->_input['REQUEST']['object']->value();
        $v['title'] = $this->_input['REQUEST']['object']->title->inner()->value();
        return parent::show($v, $commands, $input);
    }
}