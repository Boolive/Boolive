<?php
/**
 * Сайт
 * Логика корневого объекта. При исполнении запускает интерфейс.
 * @version 1.0
 */
use Library\views\View\View,
    Boolive\values\Rule;

class Site extends View
{

    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'path' => Rule::string(),
                )
            ))
        );
    }

    public function name($new_name = null, $choose_unique = false)
    {
        return parent::name(null, false);
    }


    function work()
    {
        return $this->startChild('Interfaces');
    }
}