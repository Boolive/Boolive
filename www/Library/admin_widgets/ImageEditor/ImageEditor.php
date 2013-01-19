<?php
/**
 * Редактор изображения
 *
 * @version 1.0
 * @date 17.01.2013
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\admin_widgets\ImageEditor;

use Library\views\Widget\Widget;

class ImageEditor extends Widget
{
    public function work($v = array())
    {
        $v['file'] = $this->_input['REQUEST']['object']->file();
        return parent::work($v);
    }
}