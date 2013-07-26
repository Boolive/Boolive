<?php
/**
 * Класс
 *
 * @version 1.0
 */
namespace Library\admin_widgets\Programs;

use Library\views\AutoWidget2\AutoWidget2,
    Boolive\values\Rule;

class Programs extends AutoWidget2
{
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::any(
                            Rule::arrays(Rule::entity()),
                            Rule::entity()
                        )->required()
                    )
                )
            )
        );
    }
}