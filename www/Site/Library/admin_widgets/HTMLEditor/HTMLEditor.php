<?php
/**
 * Редактор HTML
 * Формирует результат работы спомощью шаблонизации. Шаблоном является значение виджета
 * @version 1.0
 */
namespace Library\admin_widgets\HTMLEditor;

use Library\views\Widget\Widget;

class HTMLEditor extends Widget
{

    public function work($v = array())
    {
      //trace($this->_input);
        $v['uri'] = $this->_input['REQUEST']['object']->uri();
        $v['text'] = $this->_input['REQUEST']['object']->value();
        return parent::work($v);
    }
}