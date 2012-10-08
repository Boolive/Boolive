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
        $v['head'] = $this->_input['REQUEST']['object']->title->getValue();
        if (empty($v['head'])) $v['head'] = $this->_input['REQUEST']['object']->getName();
        return parent::work($v);
    }
}
