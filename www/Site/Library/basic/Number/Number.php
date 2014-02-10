<?php
/**
 * Число
 *
 * @version 1.0
 */
namespace Site\Library\basic\Number;

use Boolive\data\Entity,
    Boolive\values\Rule;

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
}