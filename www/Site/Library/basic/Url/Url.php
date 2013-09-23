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
    protected function defineRule()
    {
        parent::defineRule();
        $this->_rule->arrays[0]['value'] = Rule::url();
    }
}