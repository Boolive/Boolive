<?php
/**
 * Название
 *
 * @version 1.0
 * @date 15.04.2013
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\admin_widgets\RichTextEditor\Toolbar\bars;

use Library\views\Widget\Widget;

class bars extends Widget
{
    public function work($v = array())
    {
        $v['bars'] = $this->startChildren();
        return parent::work($v);
    }
}