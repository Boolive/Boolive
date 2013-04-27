<?php
/**
 * Виджет комментариев
 *
 * @version 1.0
 */
namespace Library\content_widgets\Comments;

use Library\views\AutoWidgetList\AutoWidgetList,
    Boolive\values\Rule;

class Comments extends AutoWidgetList
{
    public function getInputRule()
    {
        return Rule::arrays(array(
            'show_title' => Rule::bool()->default(true)->required(),
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::entity()->required(),
            ))
        ));
    }

    public function work($v = array())
    {
        $v['show_title'] = $this->_input['show_title'];
        return parent::work($v);
    }
}
