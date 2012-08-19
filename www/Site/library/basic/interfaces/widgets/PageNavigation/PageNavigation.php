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
                'page' => Rule::int()->default(1)->required(),
                'page_count' => Rule::int()->default(1)->required()
            ))
        ));
    }

    public function work($v = array()){
        echo $this->_input->GET->page.' ';
        echo $this->_input->GET->page_count;
        return parent::work($v);
    }
}
