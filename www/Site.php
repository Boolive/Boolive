<?php
/**
 * Сайт
 * Логика корневого объекта. При исполнении запускает интерфейс.
 * @version 1.0
 */
use Library\views\View\View;

class Site extends View
{
    function work()
    {
        return $this->startChild('Interfaces');
    }
}