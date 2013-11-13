<?php
/**
 * Виджет страницы
 *
 * @version 1.0
 */
namespace Library\content_widgets\Page;

use Boolive\data\Entity;
use Library\views\AutoWidgetList2\AutoWidgetList2;

class Page extends AutoWidgetList2
{

    function show($v = array(), $commands, $input){

        return parent::show($v, $commands, $input);
    }

    protected function getList($cond = array())
    {
        $cond['where'] = array('all', array(
                array('attr', 'is_hidden', '=', $this->_input['REQUEST']['object']->attr('is_hidden')),
                array('attr', 'is_draft', '=', 0),
                array('attr', 'diff', '!=', Entity::DIFF_ADD)
            ));
        return parent::getList($cond);
    }
}
