<?php
/**
 * Булево
 * Логическое значение
 *
 * @version 1.0
 */
namespace Library\basic\simple\Boolean;

use Boolive\data\Entity,
    Boolive\values\Rule;

class Boolean extends Entity
{
    /**
     * Установка правила на атрибуты
     */
    protected function defineRule()
    {
        parent::defineRule();
        $this->_rule->arrays['value']->any[1] = Rule::bool();
    }
}