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
    public function work($v = array())
    {
        $v['object'] = $this->_input['REQUEST']['object']->value();
        return parent::work($v);
    }
            
}

?>