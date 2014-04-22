<?php
/**
 * Виджет логотипа
 *
 * Виджет отображает фотографию. Фотография является свойством
 * виджета и отображается им в виде ссылки наглавную страницу сайта.
 *
 * @author Azat Galiev <AzatXaker@gmail.com>
 * @version 1.1
 */

namespace Site\library\views\Logo;

use Site\library\views\Widget\Widget;

class Logo extends Widget
{
    function show($v = array(), $commands, $input)
    {
        $v['image'] = $this->_input['REQUEST']['object']->file();
        return parent::show($v, $commands, $input);
    }
}