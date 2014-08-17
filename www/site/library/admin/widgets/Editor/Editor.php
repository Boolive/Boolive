<?php
/**
 * Редактор
 * Редактирование свойств объекта  
 * @version 1.0
 */
namespace site\library\admin\widgets\Editor;

use boolive\values\Rule;
use site\library\admin\widgets\BaseExplorer\BaseExplorer;

class Editor extends BaseExplorer
{
    function startRule()
    {
        $rule = parent::startRule();
        $rule->arrays[0]['REQUEST']->arrays[0]['select'] = Rule::in('property')->required();
        return $rule;
    }

    function show($v = array(), $commands, $input)
    {
        return parent::show($v,$commands, $input);
    }
}