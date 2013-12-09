<?php
/**
 * Вид видов
 * Для отображения виджетов и любых других видов, например, js
 * @version 1.0
 */
namespace Library\content_widgets\Page\views\ViewViews;

use Boolive\values\Rule;
use Library\views\View\View;

class ViewViews extends View
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::entity(array('is','/Library/views/View'))->required()
            ))
        ));
    }

    function work()
    {
        return $this->_input['REQUEST']['object']->start($this->_commands, $this->_input_child);
    }
}