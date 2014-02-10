<?php
/**
 * Виджет HTML блока
 * Формирует результат работы спомощью шаблонизации. Шаблоном является значение виджета
 * @version 1.0
 */
namespace Site\Library\layouts\boolive\sidebar\HtmlBlock;

use Site\Library\views\Widget\Widget;

class HtmlBlock extends Widget
{

    function show($v = array(), $commands, $input)
    {
        return parent::show($v, $commands, $input);
    }
}