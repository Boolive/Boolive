<?php
/**
 * Название
 *
 * @version 1.0
 * @date 23.07.2013
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\layouts\Admin\ProgramsMenu\item_view;

use Library\views\AutoWidgetList2\AutoWidgetList2,
    Boolive\values\Rule;
use Library\views\View\View;
use Library\views\Widget\Widget;

class item_view extends AutoWidgetList2
{
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::any(
                            Rule::arrays(Rule::entity()),
                            Rule::entity()
                        )->required(),
                'program' => Rule::entity(array('is', '/Library/views/ViewSingle'))->required(), // Объект для пункта меню
                //'active' => Rule::entity()->default(null)->required(),// Активный объект (пункт меню)
                'show' => Rule::bool()->default(true)->required() // Показывать пункт или только его подчиенных?
                )
            ))
        );
    }

    protected function initInputChild($input)
    {
        parent::initInputChild($input);
        //$this->_input_child['REQUEST']['active'] = $this->_input['REQUEST']['active'];
        $this->_input_child['REQUEST']['program'] = $this->_input['REQUEST']['program'];

        $this->_input_child['REQUEST']['show'] = true;
    }

    public function work($v = array())
    {
        if ($this->_input['REQUEST']['show']){
            /** @var \Boolive\data\Entity $obj */
            $obj = $this->_input['REQUEST']['program'];//->linked();
            // Ссылка
            $real = $obj->linked();
            $v['title'] = $real->title->value();
            $v['icon'] = $real->icon->file();
            // Активность пункта
//            $active = $this->_input['REQUEST']['active'];
//            if ($real->eq($active)){
//                $v['item_class'] = 'active';
//            }else
//            if ($active && $active->in($real)){
//                $v['item_class'] = 'active-child';
//            }else{
//                $v['item_class'] = '';
//            }
            $v['show-item'] = true;
        }else{
            $v['show-item'] = false;
        }
        $list = $this->getList();
        $v['views'] = array();
        if (is_array($list)){
            $c = new \Boolive\commands\Commands();
            foreach ($list as $obj){
                $this->_input_child['REQUEST']['program'] = $obj;
                if ($obj->linked() instanceof View && $obj->linked()->canWork($c, $this->_input_child)){
                    if ($result = $this->startChild('views')){
                        $v['views'][$obj->name()] = $result;
                    }
                }
            }
        }
        $this->_input_child['REQUEST']['program'] = $this->_input['REQUEST']['program'];
        return Widget::work($v);
    }

    public function getList($cond = array())
    {
        $cond['select'] = 'children';
        $cond['depth'] = array(1, 1); // выбрать из хранилища всё дерево меню
        $cond['group'] = true; // Для выбранных объектов однорвеменной выполнять подвыборки
        $cond['cache'] = 2; // Кэшировать сущности
        $cond['where'] = array('attr', 'is_delete', '>=', 0);
        return $this->_input['REQUEST']['program']->find($cond, true);
    }
}