<?php
/**
 * Текст анонса
 *
 * @version 1.0
 */
namespace Library\content_widgets\PagePreview\switch_views\case_text\Text;

use Library\content_widgets\RichText\RichText;

class Text extends RichText
{
    protected function getList($cond = array())
    {
        return parent::getList(array(
            'limit' => array(0, 2)
        ));
    }
}