<?php
/**
 * Список ключевых слов
 * Виджет для отображения всех ключевых слов страницы или другого содержимого
 * @version 1.0
 */
namespace site\library\content_widgets\Keywords;

use site\library\views\AutoWidgetList2\AutoWidgetList2;

class Keywords extends AutoWidgetList2
{
    function show($v = array(), $commands, $input)
    {
        return parent::show($v,$commands, $input);
    }
}