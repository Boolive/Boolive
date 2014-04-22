<?php
/**
 * Телефон
 * Номер телефона. Примеры: +79997776655, (383)325-77-77, 86543
 * @version 1.0
 */
namespace Site\library\basic\Phone;

use Site\library\basic\String\String;

class Phone extends String
{
    /**
     * Установка правила на атрибуты
     */
    protected function rule()
    {
        $rule = parent::rule();
        $rule->arrays[0]['value']->regexp('/^[0-9- \(\)]*$/u');
        return $rule;
    }
}