<?php
/**
 * Виджет для файла изображения
 *
 * @version 1.0
 */
namespace Site\library\content_widgets\Image;

use Site\library\views\Widget\Widget;

class Image extends Widget
{
    function show($v = array(), $commands, $input)
    {
        /** @var \Site\library\basic\Image\Image $obj */
        $obj = $this->_input['REQUEST']['object'];
        $v['file'] = $obj->file();
        $v['style'] = $obj->find(array('select'=>'tree', 'depth'=>array(1, 'max'), 'return'=>false, 'comment' => 'read tree of text element'));
        if ($obj->style->isExist()){
            $v['style'] = $obj->style->getStyle();
        }
        $v['width'] = $obj->width();
        $v['height'] = $obj->height();
        return parent::show($v, $commands, $input);
    }
}
