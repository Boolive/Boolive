<?php
/**
 * Поле изображения
 * Поле формы для редактирования изображения
 * @version 1.0
 */
namespace site\library\admin\widgets\Editor\views\ImageField;

use site\library\admin\widgets\BaseExplorer\views\Item\Item;

class ImageField extends Item
{
    function show($v = array(), $commands, $input)
    {
        $v['file'] = $this->_input['REQUEST']['object']->file();
        return parent::show($v,$commands, $input);
    }
}