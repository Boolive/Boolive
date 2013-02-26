<?php
/**
 * Меню программ
 * Меню автоматически формируется в зависимости от отображаемого объекта и доступного для него программ
 * @version 1.0
 */
namespace Library\layouts\Admin\ProgramsMenu;

use Library\views\Widget\Widget,
    Boolive\values\Rule;

class ProgramsMenu extends Widget
{

    public function getInputRule()
    {
        return Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::any(
                            Rule::arrays(Rule::entity()),
                            Rule::entity()
                        )->required()
                    )
                )
            )
        );
    }

    public function work($v = array())
    {
        $case = $this->programs->linked()->switch_views->getCase($this->_commands, $this->_input);
        $programs = $case->find();
        $c = new \Boolive\commands\Commands();
        $v['items'] = array();
        foreach ($programs as $p){
            if ($p instanceof Widget && $p->canWork($c, $this->_input_child)){
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
