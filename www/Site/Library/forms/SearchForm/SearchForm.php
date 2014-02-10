<?php
/**
 * Виджет
 * Формирует результат работы спомощью шаблонизации. Шаблоном является значение виджета
 * @version 1.0
 */
namespace Site\Library\forms\SearchForm;

use Site\Library\views\Widget\Widget,
    Boolive\values\Rule;

class SearchForm extends Widget
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'search' => Rule::string()->default('')->required(),
            ))
        ));
    }

    function show($v = array(), $commands, $input)
    {
        $v['search'] = $this->_input['REQUEST']['search'];
        return parent::show($v, $commands, $input);
    }
}