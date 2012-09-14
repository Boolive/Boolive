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
                'GET' => Rule::arrays(array(
                        'object' => Rule::entity()->default($this->object)->required(),
                        'page'=> Rule::int()->default(1)->required() // номер страницы
                    )
                )
            )
        );
    }

    protected function getList(){
        $count_per_page = max(1, $this->count_per_page->getValue());
        $obj = $this->_input['GET']['object'];
        $list = $obj->findAll(array(
                'order' =>'`order` ASC',
                'start' => ($this->_input['GET']['page'] - 1) * $count_per_page,
                'count' => $count_per_page
            )
        );
        $this->_input_child['GET']['page_count'] = ceil($obj->findCountAll()/$count_per_page);
        return $list;
    }
}