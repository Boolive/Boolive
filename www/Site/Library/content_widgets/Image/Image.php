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
    function show($v = array(), $commands, $input)
    {
        $v['file'] = $this->_input['REQUEST']['object']->file();
        $v['style'] = $this->_input['REQUEST']['object']->find(array('select'=>'tree', 'depth'=>array(1, 'max'), 'return'=>false, 'comment' => 'read tree of text element'));
        if ($this->_input['REQUEST']['object']->style->isExist()){
            $v['style'] = $this->_input['REQUEST']['object']->style->getStyle();
        }
        return parent::show($v, $commands, $input);
    }
}
