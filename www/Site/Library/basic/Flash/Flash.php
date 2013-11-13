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
    function rule()
    {
        $rule = parent::rule();
        // Ассоциация с файлами с расширением css
        $rule->arrays[0]['file']->arrays[0]['name']->ospatterns('*.swf')->required();
        return $rule;
    }
}