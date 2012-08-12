<?php
/**
 * Виджет
 *
 * @version 1.0
 */
namespace library\basic\widgets\Widget;

use Boolive\Entity,
    Boolive\Template;

class Widget extends Entity
{
    public function work($v = array())
    {
        return Template::Render($this, $v);
    }
}