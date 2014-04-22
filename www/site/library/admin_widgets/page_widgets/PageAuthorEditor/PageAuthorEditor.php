<?php
/**
 * Виджет для выбора автора для страницы
 * @author: polinа Putrolaynen
 * @date: 22.04.13
 *
 */
namespace Site\library\admin_widgets\page_widgets\PageAuthorEditor;

use Site\library\views\Widget\Widget,
    Boolive\values\Rule;

class PageAuthorEditor extends Widget
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::entity(array('is', '/library/access/User'))->required(),
                'call' => Rule::string()->default('')->required(),
                'user' => Rule::arrays(Rule::string()),
            ))
        ));
    }

    function show($v = array(), $commands, $input)
    {
        if (!empty($this->_input['REQUEST']['call'])){
            //Изменение автора страницы
            if (isset($this->_input['REQUEST']['user'])){
                return $this->callChangeAuthor(
                    $this->_input['REQUEST']['user']
                );
            }
            return null;
        }
        $v['object'] = $this->_input['REQUEST']['object']->uri();
        $v['value'] = $this->_input['REQUEST']['object']->linked()->title->value();
        $v['title'] = $this->_input['REQUEST']['object']->linked()->proto()->title->value();
        return parent::show($v, $commands, $input);
    }

    protected function callChangeAuthor($user)
    {
        /** @var Entity $obj */
        $obj = $this->_input['REQUEST']['object'];
        $obj->proto($user['user']);
        $obj->isLink(true);
        $obj->save();
        return $obj->title->value();
    }
}