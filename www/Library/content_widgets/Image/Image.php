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
        $v['file'] = $this->_input['GET']['object']->getFile();
        return parent::work($v);
    }
}
