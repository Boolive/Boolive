<?php
/**
 * Виджет
 * Формирует результат работы спомощью шаблонизации. Шаблоном является значение виджета
 * @version 1.0
 */
namespace Library\layouts\Admin\ProgramsMenu\item_view\views\view_action;

use Library\views\Widget\Widget,
    Boolive\values\Rule;

class view_action extends Widget
{
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'program' => Rule::entity(array('is', '/Library/views/View'))->required(), // Объект для пункта меню
                //'active' => Rule::entity()->default(null)->required(),// Активный объект (пункт меню)
                'show' => Rule::bool()->default(true)->required(), // Показывать пункт или только его подчиенных?
                'base_uri' => Rule::string()->required()
                )
            ))
        );
    }

    public function work($v = array())
    {
        if ($this->_input['REQUEST']['show']){
            /** @var \Boolive\data\Entity $obj */
            $obj = $this->_input['REQUEST']['program'];//->linked();
            // Ссылка
            $real = $obj->linked();
            $state = $real->state()?'true':'false';
            $info = $real->state->{$state};
            $v['title'] = $info->title->value();
//            $v['program'] = preg_replace('/'.preg_quote($this->_input['REQUEST']['base_uri'],'/').'/u', '', $obj->uri());
//            $v['href'] = Input::url(null, 0, array('view_name' => $v['program']));
            $v['icon'] = $info->icon->file();
            $v['show-item'] = true;
        }else{
            $v['show-item'] = false;
        }
        $v['program_uri'] = $this->_input['REQUEST']['program']->linked()->uri();
        return parent::work($v);
    }
}