<?php

namespace library\basic\interfaces\widgets\logo;

use library\basic\interfaces\widgets\Widget\Widget,
    Boolive\template\Template;

/**
 * Виджет логотипа
 *
 * Виджет отображает фотографию. Фотография является свойством
 * виджета и отображается им в виде ссылки наглавную страницу сайта.
 *
 * @author Azat Galiev <AzatXaker@gmail.com>
 * @version 1.0
 */
class logo extends Widget
{
    public function work()
    {
        $v['image'] = $this->image->getFile();

        return Template::render($this, $v);
    }
}