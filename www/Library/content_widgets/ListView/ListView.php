<?php

namespace Library\content_widgets\ListView;

class ListView extends \Library\views\AutoWidgetList\AutoWidgetList
{
    public function work($v = array())
    {
        trace($this->_input['GET']['object']);
        return parent::work($v);
    }
}
