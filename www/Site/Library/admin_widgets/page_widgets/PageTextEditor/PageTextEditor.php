<?php
/**
 *Виджет для редактирования текста в редакторе страницы
 * @author: polinа Putrolaynen
 * @date: 21.04.13
 *
 */
namespace Library\admin_widgets\page_widgets\PageTextEditor;

use Library\content_widgets\RichTextPreview\RichTextPreview;

class PageTextEditor extends RichTextPreview{

    public function work($v = array())
    {
        $v['object'] = $this->_input['REQUEST']['object']->uri();
        $v['title'] = $this->_input['REQUEST']['object']->title->value();
        return parent::work($v);
    }
}