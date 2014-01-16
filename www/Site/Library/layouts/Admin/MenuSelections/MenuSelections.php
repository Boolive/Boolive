<?php
/**
 * Меню варантов выборки
 * 
 * @version 1.0
 */
namespace Library\layouts\Admin\MenuSelections;

use Boolive\values\Rule;
use Library\views\Widget\Widget;

class MenuSelections extends Widget
{
    function show($v = array(), $commands, $input)
    {
        $choices = $this->items->find(array('where'=>array('attr','is_property','=',0)));
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