<?php
/**
 * Текст
 * Строковое значение длиной до 65535 символа (64Кбайт)
 *
 * @version 1.0
 */
namespace site\library\basic\Text;

use boolive\data\Entity;

class Text extends Entity
{
    protected function rule()
    {
        $rule = parent::rule();
        $rule->arrays[0]['value']->min(0)->max(65535);
        return $rule;
    }

    function save($children = true, $access = true)
    {
        if ($this->_attribs['value_type'] != Entity::VALUE_FILE){
            $this->_attribs['value_type'] = Entity::VALUE_TEXT;
        }
        return parent::save($children, $access);
    }
}
