<?php
/**
 * Виджет пунктов меню
 * Для отображения свойств объекта соответствующими виджетом из списка
 * @version 1.0
 */
namespace site\library\layouts\Admin\Bookmarks\item_view;

use site\library\menus\Menu\item_view\item_view as item_view_1;

class item_view extends item_view_1
{
    protected $_cut_contents_url = false;

    function show($v = array(), $commands, $input)
    {
        $obj = $this->_input['REQUEST']['object'];
        if ($this->_input['REQUEST']['show']){
            $v['item_key'] = $obj->key();
        }
        $v['item_href'] = $obj->proto()->uri();
        return parent::show($v, $commands, $input);
    }
}