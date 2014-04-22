<?php
/**
 * Виджет
 * Формирует результат работы спомощью шаблонизации. Шаблоном является значение виджета
 * @version 1.0
 */
namespace site\library\layouts\Admin\ProgramsMenu\item_view\views\view_toggle_action;

use site\library\views\Widget\Widget,
    boolive\values\Rule;

class view_toggle_action extends Widget
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'program' => Rule::entity(array('is', '/library/admin_widgets/ToggleAction'))->required(), // Объект для пункта меню
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
            $v['checked'] = $real->state();
            $v['title'] = $real->title->value();
            $v['description'] = $real->description->value();
//            $v['program'] = preg_replace('/'.preg_quote($this->_input['REQUEST']['base_uri'],'/').'/u', '', $obj->uri());
//            $v['href'] = Input::url(null, 0, array('view_name' => $v['program']));
            $v['icon'] = $real->icon->inner()->file();
            $v['show-item'] = true;
        }else{
            $v['show-item'] = false;
        }
        $v['program_uri'] = $this->_input['REQUEST']['program']->linked()->uri();
        return parent::show($v, $commands, $input);
    }
}