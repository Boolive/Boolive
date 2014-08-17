<?php
/**
 * Меню постраничной навигации
 * Используется при отображении списков с постраничным разделением вывода
 * @version 1.0
 */
namespace site\library\menus\PageNavigation;

use boolive\input\Input;
use site\library\views\Widget\Widget,
    boolive\values\Rule;

class PageNavigation extends Widget
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::entity()->required(), // объект, который отображается постранично
                'page' => Rule::int()->default(1)->required(),
                'page_count' => Rule::int()->more(1)->required()
            ))
        ));
    }

    function show($v = array(), $commands, $input)
    {
        $obj = $this->_input['REQUEST']['object'];
        $v['uri'] = Input::url($obj->uri());
        $v['count'] = $this->_input['REQUEST']['page_count'];
        $v['current'] = min($v['count'], $this->_input['REQUEST']['page']);
        $v['show'] = $this->show_cnt->inner()->value();
        return parent::show($v, $commands, $input);
    }
}