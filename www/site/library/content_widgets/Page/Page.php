<?php
/**
 * Виджет страницы
 *
 * @version 1.0
 */
namespace site\library\content_widgets\Page;

use boolive\data\Entity;
use site\library\views\AutoWidgetList2\AutoWidgetList2;

class Page extends AutoWidgetList2
{
    function startInit($input)
    {
        return parent::startInit($input);
    }

    function show($v = array(), $commands, $input)
    {
        $page = $this->_input['REQUEST']['object'];
        if ($this->show_seo->inner()->value()){
            $this->_commands->htmlHead('title', array('text'=>$page->title->value()));
            $this->_commands->htmlHead('meta', array('name'=>'description', 'content'=>$page->description->value()));
        }
        return parent::show($v, $commands, $input);
    }

    protected function getList($cond = array())
    {
        if (!isset($cond['where'])){
            $cond['where'] = array('all', array(
                array('attr', 'is_hidden', '=', 0),
                array('attr', 'is_draft', '=', 0),
                array('attr', 'is_property', '=', 1)
            ));
        }
        return parent::getList($cond);
    }
}
