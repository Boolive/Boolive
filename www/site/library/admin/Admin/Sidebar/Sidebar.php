<?php
/**
 * Правая панель
 * Элемент пользовательского интерфейса. Отображает данные с помощью шаблонизации.
 * @version 1.0
 */
namespace site\library\admin\Admin\Sidebar;

use site\library\views\View\View;
use site\library\views\Widget\Widget;

class Sidebar extends Widget
{
    function show($v = array(), $commands, $input)
    {
        $list = $this->find(array('where'=>array('attr','is_property','=',0), 'key'=>'name','group'=>true));
        $v['tabs'] = array();
        $have_active = false;
        foreach ($list as $key => $lchild){
            /** @var $child \boolive\data\Entity */
            $child = $lchild->linked(true);
            if ($child instanceof View){
                if (!isset($result[$key])){
                    $out = $child->start($this->_commands, $this->_input_child);
                    if ($out!==false){
                        $v['tabs'][$key] = array(
                            'html' => $out,
                            'active' => !$have_active,
                            'title' => 'F'//$lchild->title->inner()->value()
                        );
                        $have_active = true;
                    }
                }
            }
        }
        $v['tabs']['x'] = array(
            'html' => '',
            'active' => false,
            'title' => 'X'
        );
        return parent::show($v,$commands, $input);
    }
}