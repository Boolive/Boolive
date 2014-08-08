<?php
/**
 * Виджет для файла изображения
 *
 * @version 1.0
 */
namespace site\library\content_widgets\Image;

use site\library\views\Widget\Widget;

class Image extends Widget
{
    function show($v = array(), $commands, $input)
    {
        /** @var \site\library\basic\Image\Image $obj */
        $obj = $this->_input['REQUEST']['object'];
        $v['file'] = $obj->file();
        $v['style'] = $obj->find(array('select'=>'children','struct'=>'tree', 'depth'=>array(1, 'max'), 'return'=>false, 'comment' => 'read tree of text element'));
        if ($obj->style->isExist()){
            $v['style'] = $obj->style->getStyle();
        }
        $v['width'] = $obj->width();
        $v['height'] = $obj->height();
        return parent::show($v, $commands, $input);
    }
}
