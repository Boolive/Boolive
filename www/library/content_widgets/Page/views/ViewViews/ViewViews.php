<?php
/**
 * Вид видов
 * Для отображения виджетов и любых других видов, например, js
 * @version 1.0
 */
namespace site\library\content_widgets\Page\views\ViewViews;

use boolive\values\Rule;
use site\library\views\View\View;

class ViewViews extends View
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::entity(array('is','/library/views/View'))->required()
            ))
        ));
    }

    function work()
    {
        return $this->_input['REQUEST']['object']->linked()->start($this->_commands, $this->_input_child);
    }
}