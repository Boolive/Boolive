<?php
/**
 * Панель настроек
 * Элемент пользовательского интерфейса. Отображает данные с помощью шаблонизации.
 * @version 1.0
 */
namespace site\library\admin\widgets\Panel;

use site\library\views\Widget\Widget;

class Panel extends Widget
{
    function show($v = array(), $commands, $input)
    {
        return parent::show($v,$commands, $input);
    }
}