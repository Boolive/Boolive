<?php
/**
 * Меню
 *
 * @version 1.0
 */
namespace Library\views\Menu;

use Boolive\values\Rule,
    Library\views\Widget\Widget;

class Menu extends Widget
{
    protected function initInputChild($input){
        parent::initInputChild($input);
        // Подчиенным нужно передать активный пункт меню и отображаемый объект меню
        // Входящий объект используется как активный пункт меню
        $this->_input_child['GET']['active'] = $this->_input['GET']['object'];
        // Отображется всегда свой объект
        $this->_input_child['GET']['object'] = $this->object;
    }

    public function work($v = array()){
        $v['title'] = $this->title->getValue();
        $v['view'] = $this->startChild('view');
        return parent::work($v);
    }
}