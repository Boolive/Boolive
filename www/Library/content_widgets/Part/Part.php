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

    protected function getList(){
        $cases = $this->linked(true)->switch_views->getCases();
        $cnt = sizeof($cases);
        $protos = array();
        while ($cnt > 0){
            $cnt--;
            if ($cases[$cnt]->value() == 'all'){
                $protos = array();
                $cnt = 0;
            }else{
                $protos[] = $cases[$cnt]->value();
            }
        }

        $count_per_page = max(1, $this->count_per_page->value());
        $obj = $this->_input['REQUEST']['object'];
        $list = $obj->find(array(
            'where' => array('is', $protos),
            'order' => array(array('order', 'ASC')),
            'limit' => array(
                ($this->_input['REQUEST']['page'] - 1) * $count_per_page,
                $count_per_page
            )
        ));
//        $list = $obj->findAll(array(
//                'order' =>'`order` ASC',
//                'start' => ($this->_input['REQUEST']['page'] - 1) * $count_per_page,
//                'count' => $count_per_page
//            )
//        );
        $this->_input_child['REQUEST']['page_count'] = ceil($obj->find(array('select'=>'count'))/$count_per_page);
        return $list;
    }
}