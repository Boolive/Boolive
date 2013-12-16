<?php
/**
 * Закладки
 * Меню на объекты в админке
 * @version 1.0
 */
namespace Library\layouts\Admin\Bookmarks;

use Boolive\values\Rule;
use Library\menus\Menu\Menu;

class Bookmarks extends Menu
{
    function show($v = array(), $commands, $input)
    {
        $v['config'] = $this->object->uri();
        return parent::show($v,$commands, $input);
    }
}