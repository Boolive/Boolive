<?php
/**
 * Группа
 * Группирует объекты для автоматичекго их исполнения при исполнении самой группы
 * @version 1.1
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Site\library\basic\interfaces\Group;

use Engine\Entity;

class Group extends Entity
{
    public function work()
    {
        // Исполнение всех подчиенных и возврат их результата одной строкой
        return implode('', $this->startChildren());
    }
}