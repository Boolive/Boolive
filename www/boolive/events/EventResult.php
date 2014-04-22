<?php
/**
 * Класс результата вызова обработчиков события
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace boolive\events;

class EventResult
{
    /** @var int Количество исполненных методов-обработчиков */
    public $count;
    /** @var mixed Результат вызова методов-обработчиков */
    public $result;

    function __construct()
    {
        $this->count = 0;
        $this->result = null;
    }
}
