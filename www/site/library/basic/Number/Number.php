<?php
/**
 * Число
 *
 * @version 1.0
 */
namespace site\library\basic\Number;

use boolive\data\Entity,
    boolive\values\Rule;

class Number extends Entity
{
    /**
     * Установка правила на атрибуты
     */
    protected function rule()
    {
        $rule = parent::rule();
        $rule->arrays[0]['value'] = Rule::double();
        return $rule;
    }

    function value($new_value = null)
    {
        $value = parent::value($new_value);
        if ($this->_checked){
            return doubleval($value);
        }else{
            return $value;
        }
    }
}