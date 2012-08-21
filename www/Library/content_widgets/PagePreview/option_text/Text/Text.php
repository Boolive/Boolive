<?php
/**
 * Текст анонса
 *
 * @version 1.0
 */
namespace Library\content_widgets\PagePreview\option_text\Text;

use Library\content_widgets\RichText\RichText;

class Text extends RichText
{
    public function canWork()
    {
        if ($result = parent::canWork()){
            // По URL определяем объект и номер страницы
            $this->_input['GET']['objects_list'] = $this->_input['GET']['object']->findAll(array('order'=>'`order` ASC', 'count'=>2));
            unset($this->_input['GET']['objects_list']['title']);
        }
        return $result;
    }
}
