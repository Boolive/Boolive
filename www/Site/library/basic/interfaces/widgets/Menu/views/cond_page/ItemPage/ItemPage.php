<?php
/**
 * Пункт меню страницы
 *
 * @version 1.0
 */
namespace library\basic\interfaces\widgets\Menu\views\cond_page\ItemPage;

use library\basic\interfaces\widgets\Menu\views\views,
    Boolive\values\Rule,
    Boolive\template\Template;

class ItemPage extends views
{
    public function getInputRule()
    {
        return Rule::arrays(array(
            'GET' => Rule::arrays(array(
                'object' => Rule::entity(), // Объект для пункта меню
                'object_active' => Rule::entity()->default(null)// Активный объект (пункт меню)
                ), Rule::any() // не удалять другие элементы
            )), Rule::any() // не удалять другие элементы
        );
    }

    public function work($v = array())
    {
        $obj = $this->_input['GET']['object'];
        // Название пункта
        $v['item_text'] = $obj->title->getValue();
        $v['item_title'] = $v['item_text'];
        // Ссылка
        $real = $obj;
        while ($real && substr($real['uri'],0,10)!='/contents/'){
            $real = $real->proto();
        }
        if ($real){
            $v['item_href'] = substr($real['uri'], 10);
        }else{
            $v['item_href'] = '';
        }
        // Активность пункта
        $active = $this->_input['GET']['object_active'];
        if ($real->isEqual($active)){
			$v['item_class'] = 'active';
        }else
        if ($active && $active->isChildOf($real)){
            $v['item_class'] = 'active-child';
        }else{
            $v['item_class'] = '';
        }
        // Подчиненные пункты
        if ($obj->not_auto->getValue() == 1){
            $this->_input['GET']['objects_list'] = $this->_input['GET']['object']->find(array('order' =>'`order` ASC'));
        }else{
            $this->_input['GET']['objects_list'] = $this->_input['GET']['object']->findAll(array('order' =>'`order` ASC'));
        }
        return parent::work($v);
    }
}
