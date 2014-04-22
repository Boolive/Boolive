<?php
/**
 * Поле числа
 * 
 * @version 1.0
 */
namespace Site\library\admin_widgets\Editor\views\NumberField;

use Site\library\admin_widgets\BaseExplorer\views\Item\Item;

class NumberField extends Item
{
    function show($v = array(), $commands, $input)
    {
        $obj = $this->_input['REQUEST']['object'];
        $v['unit'] = $obj->unit->inner()->value();
        return parent::show($v,$commands, $input);
    }
}