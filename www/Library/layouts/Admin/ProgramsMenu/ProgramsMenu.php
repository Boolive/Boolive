<?php
/**
 * Меню программ
 * Меню автоматически формируется в зависимости от отображаемого объекта и доступного для него программ
 * @version 2.0
 */
namespace Library\layouts\Admin\ProgramsMenu;

use Library\views\Widget\Widget,
    Boolive\values\Rule;

class ProgramsMenu extends Widget
{
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::any(
                            Rule::arrays(Rule::entity()),
                            Rule::entity()
                        )->required()
                    )
                )
            )
        );
    }

    protected function initInputChild($input){
        parent::initInputChild($input);
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

    public function work($v = array()){
        $v['item_view'] = $this->startChild('item_view');
        return parent::work($v);
    }
}
