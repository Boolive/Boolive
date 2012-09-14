<?php
/**
 * Поле обратного адреса
 *
 * @version 1.0
 */
namespace Library\content_widgets\Feedback\switch_views\case_message\MessageField;

use Library\views\Widget\Widget,
    Boolive\values\Rule;

class MessageField extends Widget
{
    public function work($v = array())
    {
        $v['title'] = $this->_input['GET']['object']->title->getValue();
        $v['value'] = $this->_input['GET']['object']->getValue();
        $v['name'] = $this->getName();
        $v['id'] = $this['uri'];
        return parent::work($v);
    }
}
