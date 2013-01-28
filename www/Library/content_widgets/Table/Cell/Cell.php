<?php
/**
 * Виджет ячейки таблицы
 *
 * @version 1.0
 */
namespace Library\content_widgets\Table\Cell;

use Library\views\AutoWidgetList\AutoWidgetList,
  Library\content_widgets\Table\Table;

class Cell extends AutoWidgetList
{
    public function work($v = array())
    {
        $object = $this->_input['REQUEST']['object'];
        $v['style'] = $object->style->getStyle();
        return parent::work($v);
    }
}