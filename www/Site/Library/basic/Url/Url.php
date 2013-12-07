<?php
/**
 * URL
 * Абсолютный URL, например http://site.ru
 * @version 1.0
 */
namespace Library\basic\Url;

use Boolive\values\Rule;
use Library\basic\String\String;

class Url extends String
{
    protected function rule()
    {
        $rule = parent::rule();
        $rule->arrays[0]['value']->url();
        return $rule;
    }
}