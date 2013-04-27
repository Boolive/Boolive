<?php
/**
 * Вариант переключателя для списка
 *
 * @version 1.0
 */
namespace Library\admin_widgets\Explorer\switch_views\case_default;

use Boolive\values\Rule,
    Library\views\SwitchCase\SwitchCase;

class case_default extends SwitchCase
{
    public function getInputRule()
    {
        return Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'view_kind' => Rule::eq('list')->required(),
                        'object' => Rule::any(
                            Rule::arrays(Rule::entity(array('is', $this->value()))),
                            Rule::entity(array('is', $this->value()))
                        )->required(),
                        'view_name' => Rule::string()->default('')->required(), // имя виджета, которым отображать принудительно
                    )
                )
            )
        );
    }
}