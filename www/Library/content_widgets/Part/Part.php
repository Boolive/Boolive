<?php
/**
 * Виджет раздела
 *
 * @version 1.0
 */
namespace Library\content_widgets\Part;

use Library\views\AutoWidgetList\AutoWidgetList,
    Boolive\values\Rule;

class Part extends AutoWidgetList
{
    public function getInputRule()
    {
        return Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::entity()->default($this->object)->required(),
                        'page'=> Rule::int()->default(1)->required() // номер страницы
                    )
                )
            )
        );
    }

    protected function getList($cond = array()){
        $obj = $this->_input['REQUEST']['object'];
        $count_per_page = max(1, $this->count_per_page->value());
        $this->_input_child['REQUEST']['page_count'] = ceil($obj->find(array('select'=>'count'))/$count_per_page);
        $cond = array(
            'order' => array(array('order', 'ASC')),
            'limit' => array(
                ($this->_input['REQUEST']['page'] - 1) * $count_per_page,
                $count_per_page
            )
        );
        return parent::getList($cond);
    }
}