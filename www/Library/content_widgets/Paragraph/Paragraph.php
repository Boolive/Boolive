<?php
/**
 * Виджет для абзаца текста
 *
 * @version 1.0
 */
namespace Library\content_widgets\Paragraph;

use Library\views\Widget\Widget;

class Paragraph extends Widget
{
    public function work($v = array())
    {
        $v['value'] = $this->_input['REQUEST']['object']->value();
        $v['style'] = $this->_input['REQUEST']['object']->find(array('select'=>'tree', 'depth'=>array(1, 'max'), 'comment' => 'read tree of text element'));
        $v['style'] = $this->_input['REQUEST']['object']->style->getStyle();
        return parent::work($v);
    }
}
