<?php
/**
 * Группа
 * Группирует объекты для автоматичекго их исполнения при исполнении самой группы
 * @version 1.1
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace library\basic\interfaces\Group;

use Boolive\Entity;

class Group extends Entity
{
    public function work()
    {
        // Исполнение всех подчиенных и возврат их результата одной строкой
        return implode('', $this->startChildren());
    }
}