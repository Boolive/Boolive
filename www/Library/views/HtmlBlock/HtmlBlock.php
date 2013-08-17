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
        $v['text'] = $this->text->value();
        return parent::work($v);
    }
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                    'path' => Rule::string()->regexp('|^/news|'),
                    )
                )
            )
        );
    }
            
}

?>