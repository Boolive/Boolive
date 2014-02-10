<?php
/**
 * Булево
 * Логическое значение
 *
 * @version 1.0
 */
namespace Site\Library\basic\Boolean;

use Boolive\data\Entity,
    Boolive\values\Rule;

class Boolean extends Entity
{
    /**
     * Установка правила на атрибуты
     */
    protected function rule()
    {
        $rule = parent::rule();
        $rule->arrays[0]['value'] = Rule::bool();
        return $rule;
    }

    function value($new_value = null)
    {
        return intval(parent::value($new_value));
    }
}