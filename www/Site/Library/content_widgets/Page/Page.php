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
    function show($v = array(), $commands, $input)
    {
        $page = $this->_input['REQUEST']['object'];
        $this->_commands->htmlHead('title', array('text'=>$page->title->value()));
        $this->_commands->htmlHead('meta', array('name'=>'description', 'content'=>$page->description->value()));
        return parent::show($v, $commands, $input);
    }

    protected function getList($cond = array())
    {
        if (!isset($cond['where'])){
            $cond['where'] = array('all', array(
                array('attr', 'is_hidden', '=', $this->_input['REQUEST']['object']->attr('is_hidden')),
                array('attr', 'is_draft', '=', 0),
                array('attr', 'is_property', '=', 1),
                array('attr', 'diff', '!=', Entity::DIFF_ADD)
            ));
        }
        return parent::getList($cond);
    }
}
