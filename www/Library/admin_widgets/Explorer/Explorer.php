<?php
/**
 * Обозреватель
 * Отображает списком свойства объекта
 * @version 1.0
 */
namespace Library\admin_widgets\Explorer;

use Library\views\AutoWidgetList\AutoWidgetList;

class Explorer extends AutoWidgetList
{
    public function work($v = array())
    {
        $v['head'] = $this->_input['REQUEST']['object']->title->value();
        if (empty($v['head'])) $v['head'] = $this->_input['REQUEST']['object']->name();
        return parent::work($v);
    }

    protected function getList($cond = array())
    {
        return parent::getList(array(
            'where' => array(
                array('attr', 'is_hidden', '=', 0)
            )
        ));
    }
}
