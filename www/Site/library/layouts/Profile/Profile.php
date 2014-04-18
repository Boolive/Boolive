<?php
/**
 * Личный кабинет
 * Элемент пользовательского интерфейса. Отображает данные с помощью шаблонизации.
 * @version 1.0
 */
namespace Site\library\layouts\Profile;

use Boolive\values\Rule;
use Site\library\views\Widget\Widget;

class Profile extends Widget
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                //'object' => Rule::entity($this->object_rule->value())->required(),
                'path' => Rule::regexp($this->path_rule->value())->required(),
            ))
        ));
    }

    function show($v = array(), $commands, $input)
    {
        return parent::show($v,$commands, $input);
    }
}