<?php
/**
 * Виджет для файла изображения
 *
 * @version 1.0
 */
namespace Library\content_widgets\Image;

use Library\views\Widget\Widget;

class Image extends Widget
{
    public function work($v = array())
    {
        $v['file'] = $this->_input['REQUEST']['object']->file();
        $v['style'] = $this->_input['REQUEST']['object']->find(array('select'=>'tree', 'depth'=>array(1, 'max'), 'return'=>array('depth'=>0), 'comment' => 'read tree of text element'));
        if ($this->_input['REQUEST']['object']->style->isExist()){
            $v['style'] = $this->_input['REQUEST']['object']->style->getStyle();
        }
        return parent::work($v);
    }
}
