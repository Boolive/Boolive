<?php
/**
 * Поле числа
 * 
 * @version 1.0
 */
namespace site\library\admin\widgets\Editor\views\NumberField;

use site\library\admin\widgets\BaseExplorer\views\Item\Item;

class NumberField extends Item
{
    function show($v = array(), $commands, $input)
    {
        $obj = $this->_input['REQUEST']['object'];
        $v['unit'] = $obj->unit->inner()->value();
        return parent::show($v,$commands, $input);
    }
}