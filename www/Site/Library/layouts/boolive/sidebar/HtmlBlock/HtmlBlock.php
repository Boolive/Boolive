<?php
/**
 * Виджет HTML блока
 * Формирует результат работы спомощью шаблонизации. Шаблоном является значение виджета
 * @version 1.0
 */
namespace Library\layouts\boolive\sidebar\HtmlBlock;

use Library\views\Widget\Widget;

class HtmlBlock extends Widget
{

    function show($v = array(), $commands, $input)
    {
        return parent::show($v, $commands, $input);
    }
}