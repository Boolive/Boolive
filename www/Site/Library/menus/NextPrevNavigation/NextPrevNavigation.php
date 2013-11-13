<?php
/**
 * Виджет навигации по страницам ("Следующая", "Предыдущая")
 *
 * @version 1.1
 * @author Azat Galiev <AzatXaker@gmail.com>
 */

namespace Library\menus\NextPrevNavigation;

use Library\views\Widget\Widget,
    Boolive\values\Rule,
    Boolive\values\Check,
    Boolive\errors\Error;

class NextPrevNavigation extends Widget
{
	/** @var array Типы объектов, на которые возможен переход */
    private $types;

    /**
     * Типы объектов, на которые возможен переход
     * @return array
     */
    function getTypes()
    {
        if (!isset($this->types)){
            $types = $this->object_types->find(array('key'=>'name', 'comment' => 'read type of NextPrev content'));
            unset($types['title'], $types['description']);
            foreach ($types as $key => $type) {
                $type = $type->linked();
                $types[$key] = $type->uri();
            }
            $this->types = array_values($types);
        }
        return $this->types;
    }

    /**
     * Возвращает правило на входящие данные
     * @return null|\Boolive\values\Rule
     */
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::entity(array('is', $this->getTypes()))->required()
            ))
        ));
    }

    function show($v = array(), $commands, $input)
    {
        $object = $this->_input['REQUEST']['object'];
        // Типы объектов, на которые возможен переход
        $object_types = $this->getTypes();
        // Следующая страницы
        $next = $object->parent()->find(array(
                'where' => array(
                    array('attr', 'order', '>', $object->order()),
                    array('is', $object_types)
                ),
                'order' => array(
                    array('order', 'ASC')
                ),
                'limit' => array(0,1),
                'comment' => 'read next conent'
        ));
        // Предыдущая страница
        $prev = $object->parent()->find(array(
            'where' => array(
                    array('attr', 'order', '<', $object->order()),
                    array('is', $object_types)
                ),
                'order' => array(
                    array('order', 'DESC')
                ),
                'limit' => array(0,1),
                'comment' => 'read prev content'
        ));
        // Если есть следующая или предыдущая, то виджет отображается
        if (!empty($next) || !empty($prev)){
             // Инфо следующей страницы
            if (empty($next)){
                $v['next'] = null;
            }else{
                $v['next'] = array('title' => $next[0]->title->value());
                if (substr($next[0]->uri(), 0, 10) == '/Contents/') {
                    $v['next']['href'] = substr($next[0]->uri(), 10);
                } else {
                    $v['next']['href'] = $next[0]->uri();
                }
            }
            // Инфо предыдущей страницы
            if (empty($prev)){
                $v['prev'] = null;
            }else{
                $v['prev'] = array('title' => $prev[0]->title->value());
                if (substr($prev[0]->uri(), 0, 10) == '/Contents/') {
                    $v['prev']['href'] = substr($prev[0]->uri(), 10);
                } else {
                    $v['prev']['href'] = $prev[0]->uri();
                }
            }
            return parent::show($v, $commands, $input);
        }
        return false;
    }
}