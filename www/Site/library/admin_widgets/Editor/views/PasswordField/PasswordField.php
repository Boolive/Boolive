<?php
/**
 * Поле пароля
 * 
 * @version 1.0
 */
namespace Site\library\admin_widgets\Editor\views\PasswordField;

use Site\library\admin_widgets\BaseExplorer\views\Item\Item;

class PasswordField extends Item
{
    function show($v = array(), $commands, $input)
    {
        $v['password'] = uniqid();
        return parent::show($v,$commands, $input);
    }
}