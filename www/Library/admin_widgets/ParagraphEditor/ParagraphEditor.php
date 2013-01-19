<?php
/**
 * Редактор абзаца
 *
 * @version 1.0
 * @date 17.01.2013
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\admin_widgets\ParagraphEditor;

use Library\views\Widget\Widget;

class ParagraphEditor extends Widget
{
    public function work($v = array())
    {
        $v['value'] = $this->_input['REQUEST']['object']->value();
        return parent::work($v);
    }
}