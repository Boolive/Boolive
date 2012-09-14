<?php
/**
 * Пункт меню страницы
 *
 * @version 1.0
 */
namespace Library\views\Menu\view\switch_views\case_page\ItemPage;

use Library\views\Menu\view\view,
    Boolive\values\Rule,
    Boolive\template\Template;

class ItemPage extends view
{
    public function getInputRule()
    {
        return Rule::arrays(array(
            'GET' => Rule::arrays(array(
                'object' => Rule::entity(), // Объект для пункта меню
                'active' => Rule::entity()->default(null)// Активный объект (пункт меню)
                )
            ))
        );
    }

    public function work($v = array())
    {
        $obj = $this->_input['GET']['object'];
        // Название пункта
        $v['item_text'] = $obj->title->getValue();
        $v['item_title'] = $v['item_text'];
        // Ссылка
        $real = $obj->notLink();
//        while ($real && $real['is_link']){
//            $real = $real->proto();
//        }
        if ($real){
            if (substr($real['uri'], 0, 10) == '/Contents/'){
                $v['item_href'] = substr($real['uri'], 10);
            }else{
                $v['item_href'] = $real['uri'];
            }
        }else{
            $v['item_href'] = '';
        }
        // Активность пункта
        $active = $this->_input['GET']['active'];
        if ($real->isEqual($active)){
			$v['item_class'] = 'active';
        }else
        if ($active && $active->isChildOf($real)){
            $v['item_class'] = 'active-child';
        }else{
            $v['item_class'] = '';
        }
        return parent::work($v);
    }
}
