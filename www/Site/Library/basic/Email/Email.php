<?php
/**
 * Email адрес
 *
 * @version 1.0
 */
namespace Library\basic\Email;

use Boolive\data\Entity,
    Boolive\values\Rule;

class Email extends Entity
{
    /**
     * Установка правила на атрибуты
     */
    protected function rule()
    {
        $rule = parent::rule();
        $rule->arrays[0]['value'] = Rule::email();
        return $rule;
    }
}
