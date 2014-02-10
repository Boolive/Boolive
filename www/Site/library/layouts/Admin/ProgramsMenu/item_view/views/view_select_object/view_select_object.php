<?php
/**
 * Пункт выбора объекта
 * Формирует результат работы спомощью шаблонизации. Шаблоном является значение виджета
 * @version 1.0
 */
namespace Site\library\layouts\Admin\ProgramsMenu\item_view\views\view_select_object;

use Boolive\data\Data;
use Site\library\views\Widget\Widget,
    Boolive\values\Rule;

class view_select_object extends Widget
{

    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::entity()->required(),
                'program' => Rule::entity(array('is', '/library/admin_widgets/SelectObject'))->required(), // Объект для пункта меню
                //'active' => Rule::entity()->default(null)->required(),// Активный объект (пункт меню)
                'show' => Rule::bool()->default(true)->required(), // Показывать пункт или только его подчиненных?
                'base_uri' => Rule::string()->required()
            ))
        ));
    }

    function show($v = array(), $commands, $input)
    {
        if ($this->_input['REQUEST']['show']){
            /** @var \Boolive\data\Entity $obj */
            $obj = $this->_input['REQUEST']['program'];//->linked();
            // Ссылка
            $real = $obj->linked();
            $v['checked'] = $real->state();
            $v['title'] = $real->title->value();
            $v['description'] = $real->description->value();
            $v['icon'] = $real->icon->file();
            $v['show-item'] = true;

            $shorts = $real->short_select->find(array('where'=>
                array('link',
                    array('heirs', $this->_input['REQUEST']['object']))
                )
            );

            $v['shorts'] = array();
            foreach ($shorts as $short){
                $objects = $short->find(array('attr','is_link','>',0));
                foreach ($objects as $o){
                    $r = $o->linked();
                    $v['shorts'][] = array(
                        'uri' => $r->uri(),
                        'title' => $o->title->inner()->value(),
                        'description' => $o->description->inner()->value()
                    );
                }
            }
            if ($p = $this->_input['REQUEST']['object']->proto()){
                $v['open_proto'] = $p->uri();
            }
            $v['open_all'] = '/library';
            $v['open_link'] = '/library';

//             $entity = Data::read('/library/basic/Object');
//            $v['shorts'][] = array(
//                'uri' => $entity->uri(),
//                'title' => $entity->title->value()
//            );
        }else{
            $v['show-item'] = false;
        }
        $v['program_uri'] = $this->_input['REQUEST']['program']->linked()->uri();
        return parent::show($v, $commands, $input);
    }
}