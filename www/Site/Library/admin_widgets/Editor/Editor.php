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
        $obj = $this->_input['REQUEST']['object'];
        $v['title'] = $obj->title->inner()->value();
        if ($p = $obj->proto()){
            $v['proto-uri'] = $p->uri();
            $v['proto-title'] = $p->title->inner()->value();
            $v['proto-description'] = $p->description->inner()->value();
        }else{
            $v['proto-uri'] = '//0';
            $v['proto-title'] = 'Сущность';
            $v['proto-description'] = $obj->description->inner()->value();
        }
        return parent::show($v,$commands, $input);
    }
}