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
    protected function getList()
    {
        // @todo Сделать настраиваемый фильтр
        return $this->_input['REQUEST']['object']->findAll2(array(
            'where' => array(
                array('attr', 'is_history', '=', 0),
                array('attr', 'is_delete', '=', 0),
            ),
            'order' => array(
                array('order', 'ASC')
            ),
            'limit' => array(0, 2)
        ), false, null);
        //findAll(array('order' =>'`order` ASC', 'count'=>2));
    }
}