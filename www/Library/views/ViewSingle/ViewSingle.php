<?php
/**
 * Единственный вид
 * Автоматичеки исполненяет подчиненные объекты (виды) пока не будет получен положительный результат.
 * Полностью выполняет свою работу только один подчиненный объект (вид).
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\views\ViewSingle;

use Library\views\View\View;

class ViewSingle extends View
{
    public function work()
    {
        // Исполнение всех подчиенных и возврат их результата одной строкой
        return reset($this->startChildren(false));
    }
}