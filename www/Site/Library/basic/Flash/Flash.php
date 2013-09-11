<?php
/**
 * 
 * Flash клип в формате .swf
 * @version 1.0
 */
namespace Library\basic\Flash;

use Boolive\data\Entity;

class Flash extends Entity
{
    public function defineRule()
    {
        parent::defineRule();
        // Ассоциация с файлами с расширением css
        $this->_rule->arrays[0]['file']->arrays[0]['name']->ospatterns('*.swf')->required();
    }
}