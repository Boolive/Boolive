<?php
/**
 * Булево
 * Логическое значение
 *
 * @version 1.0
 */
namespace site\library\basic\Boolean;

use boolive\data\Entity,
    boolive\values\Rule;

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
        $value = intval(parent::value($new_value));
        if ($this->_checked){
            return intval($value);
        }else{
            return $value;
        }
    }
}