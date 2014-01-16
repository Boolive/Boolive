<?php
/**
 * Сайт
 * Логика корневого объекта. При исполнении запускает интерфейс.
 * @version 1.0
 */
namespace Library\views\Site;

use Library\views\View\View,
    Boolive\values\Rule;

class Site extends View
{
    protected function rule()
    {
        $rule = parent::rule();
        $rule->arrays[0]['name']->min(0);
        return $rule;
    }

    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'path' => Rule::string(),
            ))
        ));
    }

    function name($new_name = null, $choose_unique = false)
    {
        return parent::name($this->uri()==='/' ? null: $new_name, false);
    }


    function work()
    {
        return $this->Interfaces->start($this->_commands, $this->_input_child);
    }

    function birth($for = null, $draft = true)
    {
        $obj = parent::birth($for, $draft);
        $obj->name('site', true);
        return $obj;
    }
}