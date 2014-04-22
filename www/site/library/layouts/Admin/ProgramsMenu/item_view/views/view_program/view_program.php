<?php
/**
 * Название
 *
 * @version 1.0
 * @date 23.07.2013
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace site\library\layouts\Admin\ProgramsMenu\item_view\views\view_program;

use boolive\input\Input;
use site\library\views\Widget\Widget,
    boolive\values\Rule;

class view_program extends Widget
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'program' => Rule::entity(array('is', '/library/views/View'))->required(), // Объект для пункта меню
                //'active' => Rule::entity()->default(null)->required(),// Активный объект (пункт меню)
                'show' => Rule::bool()->default(true)->required(), // Показывать пункт или только его подчиенных?
                'base_uri' => Rule::string()->required()
            ))
        ));
    }

    function show($v = array(), $commands, $input)
    {
        if ($this->_input['REQUEST']['show']){
            /** @var \boolive\data\Entity $obj */
            $obj = $this->_input['REQUEST']['program'];//->linked();
            // Ссылка
            $real = $obj->linked();
            $v['title'] = $real->title->value();
            $v['description'] = $real->description->value();
            $v['program'] = preg_replace('/'.preg_quote($this->_input['REQUEST']['base_uri'],'/').'/u', '', $obj->uri());
            $v['href'] = Input::url(null, 0, array('view_name' => $v['program']));
            $v['icon'] = $real->icon->inner()->file();
            $v['show-item'] = true;
        }else{
            $v['show-item'] = false;
        }
        return parent::show($v, $commands, $input);
    }
}