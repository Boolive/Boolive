<?php
/**
 * Email адрес
 *
 * @version 1.0
 */
namespace site\library\basic\Email;

use boolive\values\Rule;
use site\library\basic\String\String;

class Email extends String
{
    /**
     * Установка правила на атрибуты
     */
    protected function rule()
    {
        $rule = parent::rule();
        $rule->arrays[0]['value']->email();
        return $rule;
    }
}
