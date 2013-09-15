<?php
/**
 * Виджет редактирования заголовка страницы
 * @author: polinа Putrolaynen
 * @version 1.0
 */
namespace Library\admin_widgets\page_widgets\PageTitleEditor;

use Boolive\errors\Error,
    Library\views\Widget\Widget,
    Boolive\values\Rule;

class PageTitleEditor extends Widget
{
    public function work($v = array())
    {
        $v['object'] = $this->_input['REQUEST']['object']->uri();
        $v['value'] = $this->_input['REQUEST']['object']->value();
        $v['title'] = $this->_input['REQUEST']['object']->title->value();
        return parent::work($v);
    }
}