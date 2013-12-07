<?php
/**
 * Редактор
 * Редактирование свойств объекта  
 * @version 1.0
 */
namespace Library\admin_widgets\Editor;

use Library\admin_widgets\BaseExplorer\BaseExplorer;

class Editor extends BaseExplorer
{
    function show($v = array(), $commands, $input)
    {
        $v['object'] = $this->_input['REQUEST']['object']->uri();
        if ($p = $this->_input['REQUEST']['object']->proto()){
            $v['title'] = $p->title->inner()->value();
            $v['description'] = $p->description->inner()->value();
            $v['proto'] = ltrim($p->uri(),'/');
        }else{
            $v['title'] = 'Сущность';
            $v['description'] = 'Объект без прототипа';
            $v['proto'] = '//0';
        }
        return parent::show($v,$commands, $input);
    }
}