<?php
/**
 * URL
 * Абсолютный URL, например http://site.ru
 * @version 1.0
 */
namespace Library\basic\Url;

use Boolive\data\Entity,
    Boolive\values\Rule;

class Url extends Entity
{
    protected function rule()
    {
        $rule = parent::rule();
        $rule->arrays[0]['value'] = Rule::url();
        return $rule;
    }
}