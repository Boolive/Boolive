<?php
/**
 * Класс
 *
 * @version 1.0
 */
namespace Library\layouts\boolive;

use Library\views\Focuser\Focuser,
    Boolive\values\Rule;

class boolive extends Focuser
{
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'path' => Rule::string(),
                )
            ),
            'previous' => Rule::eq(false)
            )
        );
    }
}