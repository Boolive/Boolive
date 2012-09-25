<?php
/**
 * Меню постраничной навигации
 * Используется при отображении списков с постраничным разделением вывода
 * @version 1.0
 */
namespace Library\views\PageNavigation;

use Library\views\Widget\Widget,
    Boolive\values\Rule;

class PageNavigation extends Widget
{
    public function getInputRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::entity()->required(), // объект, который отображается постранично
                'page' => Rule::int()->default(1)->required(),
                'page_count' => Rule::int()->default(1)->required()
            ))
        ));
    }

    public function work($v = array())
    {
        $obj = $this->_input['REQUEST']['object'];
        $v['uri'] = substr($obj['uri'], 9);
        $v['count'] = $this->_input['REQUEST']['page_count'];
        $v['current'] = min($v['count'], $this->_input['REQUEST']['page']);
        return parent::work($v);
    }
}