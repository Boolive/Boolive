<?php
/**
 * Поиск по сайту
 * Отображает форму и результаты поиска
 * @version 1.0
 */
namespace site\library\content_widgets\SearchResult;

use boolive\data\Data2;
use boolive\data\Entity;
use site\library\content_widgets\Part\Part,
    boolive\values\Rule;

class SearchResult extends Part
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'search' => Rule::string()->default('')->required(),
                'page'=> Rule::int()->default(1)->required(), // номер страницы
            )),
            'PATH' => Rule::arrays(array(
                0 => Rule::eq('search')->required()
            )),
        ));
    }

    function show($v = array(), $commands, $input)
    {
        $this->_input['REQUEST']['object'] = $this->object;
        $v['search'] = $this->_input['REQUEST']['search'];
        return parent::show($v, $commands, $input);
    }

    protected function getList($cond = array())
    {
        $count_per_page = max(1, $this->count_per_page->value()) + 1; // на один больше чтобы показывать или нет следующую страницу
        if ($search = $this->_input['REQUEST']['search']){
            if (!preg_match('/[*+<>~""]/u', $search)){
                $search = preg_replace('/(\s+|$)/u','*$1',$search);
            }
            $cond = array(
                'from' => '/contents',
                'select' => 'children',
                'depth' => array(1,'max'),
                'where' => array('all', array(
                    array('attr', 'is_hidden', '=', 0),
                    array('attr', 'is_draft', '=', 0),
                    array('attr', 'is_property', '=', 0),
                    array('any', array(
                        array('child', 'title', array(array('match',$search,1))),
                        array('child', 'text', array(array('match',$search,1)))
                    ))
                )),
                'order' => array(array('order', 'ASC')),
                'limit' => array(
                    ($this->_input['REQUEST']['page'] - 1) * $count_per_page,
                    $count_per_page
                ),
                'group' => true
            );
            $list = Data2::read($cond);
        }else{
            $list = array();
        }
        // Кол-во
        if (count($list)==$count_per_page){
            array_pop($list);
            $this->_input_child['REQUEST']['page_count'] = 2;
        }else{
            $this->_input_child['REQUEST']['page_count'] = 1;
        }
        return $list;
    }
}