<?php
/**
 * Вариант переключателя для плитки
 *
 * @version 1.0
 */
namespace Library\admin_widgets\Explorer\switch_views\case_tile_default;

use Boolive\values\Rule,
    Library\views\SwitchCase\SwitchCase;

class case_tile_default extends SwitchCase
{
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'view_kind' => Rule::eq('tile')->required(),
                        'object' => Rule::any(
                            Rule::arrays(Rule::entity($this->value())),
                            Rule::entity($this->value())
                        )->required(),
                        'view_name' => Rule::string()->default('')->required(), // имя виджета, которым отображать принудительно
                    )
                )
            )
        );
    }
}