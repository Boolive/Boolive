<?php
/**
 * Меню варантов выборки
 * 
 * @version 1.0
 */
namespace site\library\admin\Admin\MenuSelections;

use boolive\values\Rule;
use site\library\views\Widget\Widget;

class MenuSelections extends Widget
{
    function show($v = array(), $commands, $input)
    {
        $choices = $this->items->find(array('where'=>array('is_property','=',0)));
        $active = reset($choices);
        foreach ($choices as $ch){
            $v['choices'][$ch->name()] = array(
                'title' => $ch->value(),
                'active' => $ch->eq($active)
            );
        }
        return parent::show($v,$commands, $input);
    }
}