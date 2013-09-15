<?php
/**
 * Виджет для редавктирования страницы в админке
 * @author: polinа Putrolaynen
 * @date: 12.03.13
 * @version 1.0
 */
namespace Library\admin_widgets\PageEditor;

use Boolive\data\Entity,
    Library\views\AutoWidgetList2\AutoWidgetList2,
    Boolive\values\Rule;

class PageEditor extends AutoWidgetList2
{
    public function work($v = array())
    {
        $v['object'] = $this->_input['REQUEST']['object']->uri();
        return parent::work($v);
    }


}