<?php

namespace Library\content_widgets\ListView\switch_views;

class switch_views extends \Library\views\SwitchViews\SwitchViews
{
    public function work($v = array())
    {
        trace('Hey!');
        return parent::work($v);
    }
}