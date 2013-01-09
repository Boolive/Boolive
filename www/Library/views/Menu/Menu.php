<?php
/**
 * Меню
 *
 * @version 1.0
 */
namespace Library\views\Menu;

use Library\views\Widget\Widget;

class Menu extends Widget
{
    protected function initInputChild($input){
        parent::initInputChild($input);
        // Подчиенным нужно передать активный пункт меню и отображаемый объект меню
        // Входящий объект используется как активный пункт меню
        $this->_input_child['REQUEST']['active'] = $this->_input['REQUEST']['object'];
        // Отображется всегда свой объект
        $this->_input_child['REQUEST']['object'] = $this->object;
    }

    public function work($v = array()){
        $v['title'] = $this->title->value();
        $v['view'] = $this->startChild('view');
        return parent::work($v);
    }
}