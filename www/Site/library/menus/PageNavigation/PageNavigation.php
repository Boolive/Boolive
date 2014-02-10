<?php
/**
 * Меню постраничной навигации
 * Используется при отображении списков с постраничным разделением вывода
 * @version 1.0
 */
namespace Site\library\menus\PageNavigation;

use Site\library\views\Widget\Widget,
    Boolive\values\Rule;

class PageNavigation extends Widget
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::entity()->required(), // объект, который отображается постранично
                'page' => Rule::int()->default(1)->required(),
                'page_count' => Rule::int()->default(1)->required()
            ))
        ));
    }

    function show($v = array(), $commands, $input)
    {
        $obj = $this->_input['REQUEST']['object'];
        $v['uri'] = $obj->uri();
        if (substr($v['uri'],0,10)=='/contents/') $v['uri'] = mb_substr($v['uri'],9);
        $v['count'] = $this->_input['REQUEST']['page_count'];
        $v['current'] = min($v['count'], $this->_input['REQUEST']['page']);
        return parent::show($v, $commands, $input);
    }
}