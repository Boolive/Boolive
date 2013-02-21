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
        $this->_rule->arrays[0]['value'] = Rule::bool();
    }

    public function value($new_value = null)
    {
        return intval(parent::value($new_value));
    }
}