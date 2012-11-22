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
        $cases = $this->programs->switch_views->findAll2(array(
                'where' => array(
                    array('attr', 'is_history', '=', 0),
                    array('attr', 'is_delete', '=', 0),
                ),
                'order' => array(
                    array('order', 'ASC')
                )
            ), false);
        $programs = array();
        foreach ($cases as $case){
            if ($case instanceof \Library\views\SwitchCase\SwitchCase){
                $uri = $case->getValue();
                if ($uri=='all'){
                    $programs = array_merge($programs, $case->findAll2(array(
                            'where' => array(
                                array('attr', 'is_history', '=', 0),
                                array('attr', 'is_delete', '=', 0),
                            ),
                            'order' => array(
                                array('order', 'ASC')
                            )
                        ))
                    );
                }else{
                    $obj = $this->_input['REQUEST']['object'];
                    while ($obj){
                        if ($obj['uri'] == $uri){
                            $programs = array_merge($programs, $case->findAll2(array(
                                    'where' => array(
                                        array('attr', 'is_history', '=', 0),
                                        array('attr', 'is_delete', '=', 0),
                                    ),
                                    'order' => array(
                                        array('order', 'ASC')
                                    )
                                ), false, null)
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
                    'title' => $p->title->getValue(),
                    'view_name' => $p->getName(),
                    'icon' => $p->icon->getFile()
                );
                $v['items'][] = $item;
            }
        }
        return parent::work($v);
    }
}
