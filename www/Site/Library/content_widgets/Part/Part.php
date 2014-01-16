<?php
/**
 * Виджет раздела
 *
 * @version 1.0
 */
namespace Library\content_widgets\Part;

use Boolive\data\Entity;
use Library\content_widgets\Page\Page,
    Boolive\values\Rule;

class Part extends Page
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::entity($this->object_rule->value())->required(),
                'page'=> Rule::int()->default(1)->required() // номер страницы
            ))
        ));
    }

    protected function getList($cond = array())
    {
        $obj = $this->_input['REQUEST']['object'];
        $count_per_page = max(1, $this->count_per_page->value());
        $where = array('all', array(
            array('attr', 'is_hidden', '=', $this->_input['REQUEST']['object']->attr('is_hidden')),
            array('attr', 'is_draft', '=', 0),
            array('attr', 'is_property', '=', 0),
            array('attr', 'diff', '!=', Entity::DIFF_ADD)
        ));
        $this->_input_child['REQUEST']['page_count'] = ceil($obj->find(array('select'=>'count', 'where'=>$where))/$count_per_page);
        $cond = array(
            'where' => $where,
            'order' => array(array('order', 'ASC')),
            'limit' => array(
                ($this->_input['REQUEST']['page'] - 1) * $count_per_page,
                $count_per_page
            ),
            'group' => true
        );
        return parent::getList($cond);
    }
}