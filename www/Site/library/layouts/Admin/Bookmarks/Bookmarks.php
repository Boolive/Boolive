<?php
/**
 * Закладки
 * Меню на объекты в админке
 * @version 1.0
 */
namespace Site\library\layouts\Admin\Bookmarks;

use Boolive\values\Rule;
use Site\library\menus\Menu\Menu;

class Bookmarks extends Menu
{
    function show($v = array(), $commands, $input)
    {
        $v['config'] = $this->object->uri();
        return parent::show($v,$commands, $input);
    }
}