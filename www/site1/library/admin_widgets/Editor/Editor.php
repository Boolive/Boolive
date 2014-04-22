<?php
/**
 * Редактор
 * Редактирование свойств объекта  
 * @version 1.0
 */
namespace Site\library\admin_widgets\Editor;

use Boolive\values\Rule;
use Site\library\admin_widgets\BaseExplorer\BaseExplorer;

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