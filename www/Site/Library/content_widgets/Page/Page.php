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

    public function work($v = array()){

        return parent::work($v);
    }

    protected function getList($cond = array())
    {
        $cond['where'] = array('all', array(
                array('attr', 'is_hidden', '=', 0),
                array('attr', 'is_delete', '=', 0),
                array('attr', 'diff', '!=', Entity::DIFF_ADD)
            ));
        return parent::getList($cond);
    }
}
