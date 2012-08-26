<?php
/**
 * Виджет
 *
 * @version 1.0
 */
namespace Library\basic\widgets\Widget;

use Boolive\data\Entity,
    Boolive\template\Template;

class Widget extends Entity
{
    public function work($v = array())
    {
        // Подключаем необходимые ресурсы перед шаблонизацией
        $this->startChild('res');
        
        return Template::render($this, $v);
    }
}