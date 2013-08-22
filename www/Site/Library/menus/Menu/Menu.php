<?php
/**
 * Меню
 *
 * @version 2.0
 */
namespace Library\menus\Menu;

use Library\views\Widget\Widget;

class Menu extends Widget
{
    protected function initInputChild($input){
        parent::initInputChild($input);
        // Подчиенным нужно передать активный пункт меню и отображаемый объект меню
        // Входящий объект используется как активный пункт меню
        $this->_input_child['REQUEST']['active'] = $this->_input['REQUEST']['object']->linked();
        // Отображется всегда свой объект
        $this->_input_child['REQUEST']['object'] = $this->object->linked();
        // Не показыват корневой пункт меню
        $this->_input_child['REQUEST']['show'] = false;
    }

    public function work($v = array()){
        $v['title'] = $this->title->value();
        //$v['view'] = $this->startChild('view');
        $v['item_view'] = $this->startChild('item_view');
        return parent::work($v);
    }
}