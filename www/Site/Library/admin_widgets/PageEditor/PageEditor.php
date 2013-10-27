<?php
/**
 * Виджет для редавктирования страницы в админке
 * @author: polinа Putrolaynen
 * @date: 12.03.13
 * @version 1.0
 */
namespace Library\admin_widgets\PageEditor;

use Boolive\data\Entity,
    Library\views\AutoWidgetList2\AutoWidgetList2;

class PageEditor extends AutoWidgetList2
{
    public function work($v = array())
    {
        $v['object'] = $this->_input['REQUEST']['object']->uri();
        return parent::work($v);
    }

    protected function getList($cond = array())
    {
        $obj = $this->_input['REQUEST']['object'];
        $cond['where'] = array('all', array(
            array('attr', 'is_hidden', '=', $obj->attr('is_hidden')),
            array('attr', 'is_draft', '=', $obj->attr('is_draft')),
            array('attr', 'diff', '!=', Entity::DIFF_ADD)
        ));
        return parent::getList($cond);
    }
}