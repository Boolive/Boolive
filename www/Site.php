<?php
/**
 * Сайт
 * Логика корневого объекта. При исполнеии запускает интерфейс.
 * @version 1.0
 */
use Library\views\View\View;

class Site extends \Library\views\View\View
{
    function work()
    {
        return $this->startChild('Interfaces');
    }
}