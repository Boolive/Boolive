<?php
/**
 * Добавить
 * Инструмент добавления новых объектов
 * @version 1.0
 */
namespace Library\admin_widgets\Explorer\AddTool;

use Boolive\data\Data;
use Library\views\Widget\Widget;

class AddTool extends Widget
{

    function show($v = array(), $commands, $input)
    {
        /** @var \Boolive\data\Entity $program */
        $program = $this->program;
        // Ссылка
        $real = $program->linked();
        $v['title'] = $real->title->value();
        $v['description'] = $real->description->value();
        $v['icon'] = $real->icon->file();

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
                    'title' => $o->title->isExist()? $o->title->value() : $r->title->value(),
                    'description' => $o->description->isExist()? $o->description->value() : $r->description->value()
                );
            }
        }
//        $entity = Data::read('/Library/basic/Object');
//        $v['shorts'][] = array(
//            'uri' => $entity->uri(),
//            'title' => $entity->title->value(),
//            'description' => $entity->description->value()
//        );
        $v['program_uri'] = $real->uri();
        return parent::show($v,$commands, $input);
    }
}