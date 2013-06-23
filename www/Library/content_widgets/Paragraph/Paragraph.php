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
        $v['style'] = $this->_input['REQUEST']['object']->find(array('select'=>'tree', 'depth'=>array(1, 'max'), 'return'=>array('depth'=>0), 'comment' => 'read tree of text element'));
        if ($this->_input['REQUEST']['object']->style->isExist()){
            $v['style'] = $this->_input['REQUEST']['object']->style->getStyle();
        }
        return parent::work($v);
    }
}
