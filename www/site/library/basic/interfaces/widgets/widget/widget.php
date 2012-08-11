<?php
/**
 * Виджет
 *
 * @version 1.0
 */
namespace Site\library\basic\widgets\Widget;

use Engine\Entity,
    Engine\Template;

class Widget extends Entity
{
    public function work($v = array())
    {
        return Template::Render($this, $v);
    }
}