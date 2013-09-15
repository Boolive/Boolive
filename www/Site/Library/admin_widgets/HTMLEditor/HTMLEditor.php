<?php
/**
 * CKEditor
 * Текстовое поле для HTML с визуальным редактором CKEditor
 * @version 1.0
 */
namespace Library\admin_widgets\HTMLEditor;

use Library\views\Widget\Widget;

class HTMLEditor extends Widget
{

    public function work($v = array())
    {
      //trace($this->_input);
        $v['object'] = $this->_input['REQUEST']['object']->uri();
        $v['value'] = $this->_input['REQUEST']['object']->value();
        return parent::work($v);
    }
}