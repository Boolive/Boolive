<?php
/**
 * Автоматическое представление пунктов меню
 *
 * @version 2.0
 */
namespace Library\views\Menu\item_view;

use Boolive\values\Rule,
    Library\views\AutoWidgetList2\AutoWidgetList2;

class item_view extends AutoWidgetList2
{
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::entity($this->object_rule->value())->required(), // Объект для пункта меню
                'active' => Rule::entity()->default(null)->required(),// Активный объект (пункт меню)
                'show' => Rule::bool()->default(true)->required() // Показывать пункт или только его подчиенных?
                )
            ))
        );
    }

    protected function initInputChild($input)
    {
        parent::initInputChild($input);
        $this->_input_child['REQUEST']['active'] = $this->_input['REQUEST']['active'];
        $this->_input_child['REQUEST']['object'] = $this->_input['REQUEST']['object'];
        $this->_input_child['REQUEST']['show'] = true;
    }

    public function work($v = array())
    {
        if ($this->_input['REQUEST']['show']){
            /** @var \Boolive\data\Entity $obj */
            $obj = $this->_input['REQUEST']['object'];//->linked();
            // Название пункта
//            $v['item_text'] = '';//$obj->title->value();
//            $v['item_title'] = $v['item_text'];
            // Ссылка
            $real = $obj->linked();
            //if ($real){
                if (substr($real->uri(), 0, 10) == '/Contents/'){
                    $v['item_href'] = substr($real->uri(), 10);
                }else{
                    $v['item_href'] = $real->uri();
                }
                if (empty($v['item_text'])) $v['item_text'] = $real->title->value();
//            }else{
//                $v['item_href'] = '';
//            }
            $v['item_title'] = $v['item_text'];
            // Активность пункта
            $active = $this->_input['REQUEST']['active'];
            if ($real->isEqual($active)){
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
        return parent::work($v);
    }

    public function getList($cond = array())
    {
        $cond['select'] = 'tree';
        $cond['depth'] = array(1, 'max'); // выбрать из хранилища всё дерево меню
        //$cond['order'] = array(array('order', 'asc'));
        $cond['group'] = true; // Для выбранных объектов однорвеменной выполнять подвыборки
        $cond['return'] = array('depth'=>1); // получить только первый уровень дерева
        $cond['cache'] = 2; // Кэшировать сущности
        return parent::getList($cond);
    }
}