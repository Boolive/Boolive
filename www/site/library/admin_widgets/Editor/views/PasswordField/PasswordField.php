<?php
/**
 * Поле пароля
 * 
 * @version 1.0
 */
namespace site\library\admin_widgets\Editor\views\PasswordField;

use site\library\admin_widgets\BaseExplorer\views\Item\Item;

class PasswordField extends Item
{
    function show($v = array(), $commands, $input)
    {
        $v['password'] = uniqid();
        return parent::show($v,$commands, $input);
    }
}