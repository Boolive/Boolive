<?php
/**
 * Виджет логотипа
 *
 * Виджет отображает фотографию. Фотография является свойством
 * виджета и отображается им в виде ссылки наглавную страницу сайта.
 *
 * @author Azat Galiev <AzatXaker@gmail.com>
 * @version 1.0
 */

namespace Library\basic\widgets\logo;

use Library\basic\widgets\Widget\Widget;

class logo extends Widget
{
    public function work($v = array())
    {
        $v['image'] = $this->image->getFile();

        return parent::work($v);
    }
}