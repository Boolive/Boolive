<?php
/**
 * Виджет
 * Формирует результат работы спомощью шаблонизации. Шаблоном является значение виджета
 * @version 1.0
 */
namespace Library\views\Widget;

use Boolive\template\Template,
    Library\views\View\View,
    Boolive\values\Rule;

class Widget extends View
{
    /**
     * Возвращает правило на входящие данные
     * @return null|\Boolive\values\Rule
     */
    public function getInputRule()
    {
        return Rule::arrays(array(
                'GET' => Rule::arrays(array(
                        'object' => Rule::entity()->default($this->object)->required()
                    )
                )
            )
        );
    }

    public function work($v = array())
    {
        //$this->startChild('res');
        return Template::render($this, $v);
    }
}