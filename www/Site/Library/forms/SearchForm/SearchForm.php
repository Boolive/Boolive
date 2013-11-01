<?php
/**
 * Виджет
 * Формирует результат работы спомощью шаблонизации. Шаблоном является значение виджета
 * @version 1.0
 */
namespace Library\forms\SearchForm;

use Library\views\Widget\Widget,
    Boolive\values\Rule;

class SearchForm extends Widget
{
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'search' => Rule::string()->default('')->required(),
                ))
            )
        );
    }

    public function work($v = array())
    {
        $v['search'] = $this->_input['REQUEST']['search'];
        return parent::work($v);
    }
}