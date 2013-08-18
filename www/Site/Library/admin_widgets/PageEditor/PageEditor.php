<?php
/**
 * Виджет для редавктирования страницы в админке
 * @author: polinа Putrolaynen
 * @date: 12.03.13
 * @version 1.0
 */
namespace Library\admin_widgets\PageEditor;

use Boolive\data\Entity,
    Library\views\AutoWidgetList\AutoWidgetList,
    Boolive\values\Rule;

class PageEditor extends AutoWidgetList
{
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::entity(array('is','/Library/content_samples/Page'))->required(),
                    )
                )
            )
        );
    }

    public function work($v = array())
    {
        $v['object'] = $this->_input['REQUEST']['object']->uri();
        return parent::work($v);
    }


}