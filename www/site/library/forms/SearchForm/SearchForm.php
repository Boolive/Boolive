<?php
/**
 * Виджет
 * Формирует результат работы спомощью шаблонизации. Шаблоном является значение виджета
 * @version 1.0
 */
namespace site\library\forms\SearchForm;

use site\library\views\Widget\Widget,
    boolive\values\Rule;

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