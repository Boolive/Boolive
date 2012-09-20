<?php
/**
 * Виджет форматированного текста
 *
 * @version 1.0
 */
namespace Library\content_widgets\RichText;

use Library\views\AutoWidgetList\AutoWidgetList;

class RichText extends AutoWidgetList
{
    public function work($v = array())
    {
        //trace($this->_input['GET']['object']->findAll(array('order' =>'`order` ASC')));
        return parent::work($v);
    }
}
