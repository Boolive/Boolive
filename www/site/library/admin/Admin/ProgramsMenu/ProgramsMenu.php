<?php
/**
 * Меню программ
 * Меню автоматически формируется в зависимости от отображаемого объекта и доступного для него программ
 * @version 2.0
 */
namespace site\library\admin\Admin\ProgramsMenu;

use site\library\views\Widget\Widget,
    boolive\values\Rule;

class ProgramsMenu extends Widget
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::any(
                    Rule::arrays(Rule::entity()),
                    Rule::entity()
                )->required()
            ))
        ));
    }

    function startInitChild($input){
        parent::startInitChild($input);
        // Отображется список видов
        $this->_input_child['REQUEST']['program'] = $this->programs->linked()->views;
        // Начальная чаcть uri, которая будет отрезаться для получения относительного пути на вид
        $this->_input_child['REQUEST']['base_uri'] = $this->_input_child['REQUEST']['program']->uri().'/';
        // Не показыват корневой пункт меню
        $this->_input_child['REQUEST']['show'] = false;
        // Не использовать явное указание, каким видом показывать пункт меню
        if (isset($this->_input_child['REQUEST']['view_name'])){
            unset($this->_input_child['REQUEST']['view_name']);
        }
    }

    function show($v = array(), $commands, $input){
        $v['item_view'] = $this->startChild('item_view');
        return parent::show($v, $commands, $input);
    }
}
