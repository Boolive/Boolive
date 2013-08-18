<?php
/**
 * Редактор заголовка
 *
 * @version 1.0
 * @date 17.01.2013
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\admin_widgets\HeadEditor;

use Library\views\Widget\Widget;

class HeadEditor extends Widget
{
    public function work($v = array())
    {
        $v['value'] = $this->_input['REQUEST']['object']->value();
        return parent::work($v);
    }
}