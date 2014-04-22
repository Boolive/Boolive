<?php
/**
 * Меню
 *
 * @version 2.0
 */
namespace site\library\menus\Menu;

use site\library\views\View\View;
use site\library\views\Widget\Widget;

class Menu extends Widget
{
    function startInit($input)
    {
        View::startInit($input);
    }

    function startInitChild($input)
    {
        parent::startInitChild($input);
        // Подчиенным нужно передать активный пункт меню и отображаемый объект меню
        // Входящий объект используется как активный пункт меню
        $this->_input_child['REQUEST']['active'] = $this->_input['REQUEST']['object']->linked();
        // Отображется всегда свой объект
        $this->_input_child['REQUEST']['object'] = $this->object->linked();
        // Не показыват корневой пункт меню
        $this->_input_child['REQUEST']['show'] = false;
        if (isset($this->_input_child['REQUEST']['view_name'])){
            unset($this->_input_child['REQUEST']['view_name']);
        }
    }

    function show($v = array(), $commands, $input)
    {
        $v['title'] = $this->title->value();
        //$v['view'] = $this->startChild('view');
        $v['item_view'] = $this->startChild('item_view');
        return parent::show($v, $commands, $input);
    }
}