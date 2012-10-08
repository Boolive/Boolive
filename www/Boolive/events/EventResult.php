<?php
/**
 * Класс результата вызова обработчиков события
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\events;

class EventResult
{
    /** @var int Количество исполненных методов-обработчиков */
    public $count;
    /** @var mixed Результат вызова методов-обработчиков */
    public $value;

    function __construct()
    {
        $this->count = 0;
        $this->value = null;
    }
}
