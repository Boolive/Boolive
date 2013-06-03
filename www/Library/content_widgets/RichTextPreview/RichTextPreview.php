<?php
/**
 * Текст анонса
 *
 * @version 1.0
 */
namespace Library\content_widgets\RichTextPreview;

use Library\content_widgets\RichText\RichText;

class RichTextPreview extends RichText
{
    public function work($v = array()){
        return parent::work($v);
    }

    public function getList($cond = array())
    {
        return parent::getList(array(
            'limit' => array(0, 2)
        ));
    }
}