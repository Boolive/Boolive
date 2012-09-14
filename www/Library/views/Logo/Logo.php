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

namespace Library\views\Logo;

use Library\views\Widget\Widget;

class Logo extends Widget
{
    protected function initInput($input)
    {
        parent::initInput($input);
        if ($this->use_object->getValue()){
            $this->_input['GET']['object'] = $this->object;
        }
    }

    public function work($v = array())
    {
        $v['image'] = $this->_input['GET']['object']->getFile();
        return parent::work($v);
    }
}