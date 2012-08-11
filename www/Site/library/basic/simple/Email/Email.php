<?php
/**
 * Email адрес
 *
 * @version 1.0
 */
namespace Site\library\basic\simple\Email;

use Engine\Entity,
    Engine\Rule;

class Email extends Entity
{
    /**
     * Установка правила на атрибуты
     */
    protected function defineRule()
    {
        parent::defineRule();
        $this->_rule->arrays['value']->any[1] = Rule::email();
    }
}
