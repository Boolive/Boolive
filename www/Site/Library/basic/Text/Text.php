<?php
/**
 * Текст
 * Строковое значение длиной до 65535 символа (64Кбайт)
 *
 * @version 1.0
 */
namespace Library\basic\Text;

use Boolive\data\Entity;

class Text extends Entity
{
    public function defineRule()
    {
        parent::defineRule();
        $this->_rule->arrays[0]['value']->more(0)->max(1000);
    }
}
