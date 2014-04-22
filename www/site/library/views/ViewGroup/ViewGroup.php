<?php
/**
 * Группа видов
 * Автоматичеки исполненяет все подчиненные объекты (виды)
 * @version 1.1
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Site\library\views\ViewGroup;

use Site\library\views\View\View;

class ViewGroup extends View
{
//    protected function initInputChild($input)
//    {
//        parent::initInputChild($this->_input);
//    }

    function work()
    {
        // Исполнение всех подчиенных и возврат их результата одной строкой
        return implode('', $this->startChildren());
    }
}