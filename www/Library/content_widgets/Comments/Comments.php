<?php
/**
 * Виджет комментариев
 *
 * @version 1.0
 */
namespace Library\content_widgets\Comments;

use Library\views\AutoWidgetList\AutoWidgetList;

class Comments extends AutoWidgetList
{
    public function work($v = array())
    {
        echo 'Comments';
    }
}
