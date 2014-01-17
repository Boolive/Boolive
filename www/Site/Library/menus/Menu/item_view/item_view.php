<?php
/**
 * Автоматическое представление пунктов меню
 *
 * @version 2.0
 */
namespace Library\menus\Menu\item_view;

use Boolive\data\Entity;
use Boolive\values\Rule,
    Library\views\AutoWidgetList2\AutoWidgetList2,
    Library\basic\Image\Image;

class item_view extends AutoWidgetList2
{
    protected $_cut_contents_url = true;

    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::entity($this->object_rule->value())->required(), // Объект для пункта меню
                'active' => Rule::entity()->default(null)->required(),// Активный объект (пункт меню)
                'show' => Rule::bool()->default(true)->required() // Показывать пункт или только его подчиенных?
            ))
        ));
    }

    function startInitChild($input)
    {
        parent::startInitChild($input);
        $this->_input_child['REQUEST']['active'] = $this->_input['REQUEST']['active'];
        $this->_input_child['REQUEST']['object'] = $this->_input['REQUEST']['object'];
        $this->_input_child['REQUEST']['show'] = true;
    }

    function show($v = array(), $commands, $input)
    {
        if ($this->_input['REQUEST']['show']){
            /** @var \Boolive\data\Entity $obj */
            $obj = $this->_input['REQUEST']['object'];//->linked();


            // Ссылка
            $real = $obj->linked();
            if ($this->_cut_contents_url && substr($real->uri(), 0, 10) == '/Contents/'){
                $v['item_href'] = substr($real->uri(), 9);
            }else{
                $v['item_href'] = $real->uri();
            }
            // Название пункта
//            $v['item_text'] = $obj->title->value();
//            if (empty($v['item_text']))
            $v['item_text'] = $obj->title->inner()->value();
            $v['item_descript'] = $obj->description->inner()->value();
            $v['item_title'] = $v['item_text'];
            // Иконка
            if ($real->icon->isExist()){
                $v['item_icon'] = $real->icon->/*resize(0,30,Image::FIT_OUTSIDE_LEFT_TOP)->*/file();
            }


            // Активность пункта
            $active = $this->_input['REQUEST']['active'];

            if ($real->eq($active)){
                $v['item_class'] = 'active';
            }else
            if ($active && $active->in($real)){
                $v['item_class'] = 'active-child';
            }else{
                $v['item_class'] = '';
            }
            $v['show-item'] = true;
        }else{
            $v['show-item'] = false;
        }
        return parent::show($v, $commands, $input);
    }

    function getList($cond = array())
    {
        $cond['select'] = 'tree';
        $cond['depth'] = array(1, 1); // выбрать из хранилища всё дерево меню
        $cond['where'] = array('all', array(
                array('attr', 'is_hidden', '=', 0),
                array('attr', 'is_draft', '=', 0),
                array('attr', 'is_property', '=', 0),
                array('attr', 'diff', '!=', Entity::DIFF_ADD)
            ));
        //$cond['order'] = array(array('order', 'asc'));
        $cond['group'] = true; // Для выбранных объектов однорвеменной выполнять подвыборки
        $cond['return'] = array('depth'=>1); // получить только первый уровень дерева
        $cond['cache'] = 2; // Кэшировать сущности
        return parent::getList($cond);
    }
}