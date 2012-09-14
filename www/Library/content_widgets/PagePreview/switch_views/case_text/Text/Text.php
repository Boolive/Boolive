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
    protected function getList(){
        // @todo Сделать настраиваемый фильтр
        return $this->_input['GET']['object']->findAll(array('order' =>'`order` ASC', 'count'=>2));
    }
}