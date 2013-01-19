<?php
/**
 * Виджет ячейки таблицы
 *
 * @version 1.0
 */
namespace Library\content_widgets\Table\Cell;

use Library\views\AutoWidgetList\AutoWidgetList;

class Cell extends AutoWidgetList
{
    public function work($v = array())
    {
        $object = $this->_input['REQUEST']['object'];
        $style = $object->style->find();
        $v['style'] = array();
        foreach($style as $st){
            $v['style'][$st->name()]=$st->name().': '.$st->value();
        }
        $v['style'] = implode(';', $v['style']);

        return parent::work($v);
    }
}