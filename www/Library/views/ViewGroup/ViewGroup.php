<?php
/**
 * Группа видов
 * Автоматичеки исполненяет все подчиенные объекты (виды)
 * @version 1.1
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\views\ViewGroup;

use Library\views\View\View;

class ViewGroup extends View
{
//    protected function initInputChild($input)
//    {
//        parent::initInputChild($this->_input);
//    }

    public function work()
    {
        // Исполнение всех подчиенных и возврат их результата одной строкой
        return implode('', $this->startChildren());
    }
}