<?php
/**
 * Сайт
 * Логика корневого объекта. При исполнении запускает интерфейс.
 * @version 1.0
 */
use Library\views\View\View;

class Site extends View
{
    public function name($new_name = null, $choose_unique = false)
    {
        return parent::name(null, false);
    }


    function work()
    {
        return $this->startChild('Interfaces');
    }
}