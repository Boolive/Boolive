<?php
/**
 * Поле изображения
 * Поле формы для редактирования изображения
 * @version 1.0
 */
namespace Site\Library\admin_widgets\Editor\views\ImageField;

use Site\Library\admin_widgets\BaseExplorer\views\Item\Item;

class ImageField extends Item
{
    function show($v = array(), $commands, $input)
    {
        $v['file'] = $this->_input['REQUEST']['object']->file();
        return parent::show($v,$commands, $input);
    }
}