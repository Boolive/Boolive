<?php
/**
 * Строка
 * Строковое значение длиной 255 символов
 * @version 1.0
 */
namespace site\library\basic\String;

use boolive\data\Entity;

class String extends Entity
{
    protected function rule()
    {
        $rule = parent::rule();
        $rule->arrays[0]['value']->min(0)->max(255);
        return $rule;
    }
}