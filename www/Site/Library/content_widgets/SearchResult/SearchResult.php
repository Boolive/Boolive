<?php
/**
 * Поиск по сайту
 * Отображает форму и результаты поиска
 * @version 1.0
 */
namespace Library\content_widgets\SearchResult;

use Boolive\data\Data;
use Boolive\data\Entity;
use Library\content_widgets\Part\Part,
    Boolive\values\Rule;

class SearchResult extends Part
{
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'search' => Rule::string()->default('')->required(),
                        'page'=> Rule::int()->default(1)->required(), // номер страницы
//                        'path'=> Rule::ospatterns('/search*')->required()
                    )
                ),
                'PATH' => Rule::arrays(array(
                    0 => Rule::eq('search')->required()
                    )
                ),
            )
        );
    }

    public function work($v = array())
    {
        $this->_input['REQUEST']['object'] = $this->object;
        $v['search'] = $this->_input['REQUEST']['search'];
        return parent::work($v);
    }

    protected function getList($cond = array())
    {
        $count_per_page = max(1, $this->count_per_page->value()) + 1; // на один больше чтобы показывать или нет следующую страницу
        if ($search = $this->_input['REQUEST']['search']){
            if (!preg_match('/[*+<>~""]/u', $search)){
                $search = preg_replace('/(\s+|$)/u','*$1',$search);
            }
            $cond = array(
                'from' => '/Contents',
                'select' => 'children',
                'depth' => 'max',
                'where' => array('all', array(
                    array('attr', 'is_hidden', '=', 0),
                    array('attr', 'is_draft', '=', 0),
                    array('attr', 'diff', '!=', Entity::DIFF_ADD),
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
            $list = Data::read($cond);
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