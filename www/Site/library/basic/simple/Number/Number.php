<?php
/**
 * Число
 *
 * @version 1.0
 */
namespace library\basic\simple\Number;

use Boolive\data\Entity,
    Boolive\values\Rule;

class Number extends Entity
{
    /**
     * Установка правила на атрибуты
     */
    protected function defineRule()
    {
        parent::defineRule();
        $this->_rule->arrays['value']->any[1] = Rule::double();
    }
}