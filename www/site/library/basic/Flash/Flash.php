<?php
/**
 * 
 * Flash клип в формате .swf
 * @version 1.0
 */
namespace Site\library\basic\Flash;

use Boolive\data\Entity;

class Flash extends Entity
{
    protected function rule()
    {
        $rule = parent::rule();
        // Ассоциация с файлами с расширением css
        $rule->arrays[0]['file']->arrays[0]['name']->ospatterns('*.swf')->required();
        return $rule;
    }
}