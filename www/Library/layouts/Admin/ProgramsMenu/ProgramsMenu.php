<?php
/**
 * Меню программ
 * Меню автоматически формируется в зависимости от отображаемого объекта и доступного для него программ
 * @version 1.0
 */
namespace Library\layouts\Admin\ProgramsMenu;

use Library\views\Widget\Widget;

class ProgramsMenu extends Widget
{
    public function work($v = array())
    {
        $cases = $this->programs->switch_views->find();
        $programs = array();
        foreach ($cases as $case){
            if ($case instanceof \Library\views\SwitchCase\SwitchCase){
                $uri = $case->value();
                if ($uri=='all'){
                    $programs = array_merge($programs, $case->find());
                }else{
                    $obj = $this->_input['REQUEST']['object'];
                    while ($obj){
                        if ($obj->uri() == $uri){
                            $programs = array_merge($programs, $case->find(array(), null)
                            );
                            $obj = null;
                        }else{
                            $obj = $obj->proto();
                        }
                    }
                }
            }
        }
        $v['items'] = array();
        foreach ($programs as $p){
            if ($p instanceof Widget){
                $item = array(
                    'title' => $p->title->value(),
                    'view_name' => $p->name(),
                    'icon' => $p->icon->file()
                );
                $v['items'][] = $item;
            }
        }
        return parent::work($v);
    }
}
