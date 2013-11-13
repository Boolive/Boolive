<?php
/**
 * Виджет Вставка Html блока 
 *
 * @version 1.0
 * @autor Pavel ishodnikov <gpavellog@gmail.com>
 */
namespace Library\views\HtmlBlock;

use \Library\views\Widget\Widget,
    Boolive\values\Rule;

class HtmlBlock extends Widget
{
    function show($v = array(), $commands, $input)
    {
        $v['object'] = $this->_input['REQUEST']['object']->value();
        return parent::show($v, $commands, $input);
    }
            
}

?>