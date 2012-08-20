<?php
/**
 * Меню постраничной навигации
 *
 * @version 1.0
 */
namespace library\basic\interfaces\widgets\PageNavigation;

use library\basic\interfaces\widgets\Widget\Widget,
    Boolive\values\Rule;

class PageNavigation extends Widget
{
    public function getInputRule(){
        return Rule::arrays(array(
            'GET' => Rule::arrays(array(
                'object' => Rule::entity()->required(), // объект, который отображается постранично
                'page' => Rule::int()->default(1)->required(),
                'page_count' => Rule::int()->default(1)->required()
            ))
        ));
    }

    public function work($v = array()){
        $obj = $this->_input['GET']['object'];
        $v['uri'] = substr($obj['uri'], 9);
        $v['count'] = $this->_input['GET']['page_count'];
        $v['current'] = min($v['count'], $this->_input['GET']['page']);
        return parent::work($v);
    }
}