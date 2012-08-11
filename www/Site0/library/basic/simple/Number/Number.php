<?php
/**
 * Число
 *
 * @version 1.0
 */
namespace Site\library\basic\simple\Number;

use Engine\Entity,
    Engine\Rule;

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