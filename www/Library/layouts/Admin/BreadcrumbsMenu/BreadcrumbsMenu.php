<?php
/**
 * Меню "хлебные крошки"
 * Отображает путь на текущий объект с возможностью перехода к его родителям
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\layouts\Admin\BreadcrumbsMenu;

use Library\views\Widget\Widget,
    Boolive\input\Input;

class BreadcrumbsMenu extends Widget{

	public function work($v = array())
    {
        /** @var $obj \Boolive\data\Entity */
		$obj = $this->_input['REQUEST']['object'];
		$parents = $obj->find(array(
            'select' => 'parents',
            'depth' => array(0,'max'),
            'order' => array('parent_cnt', 'desc'),
            'where' => array(
                array('attr', 'diff', '>=', 0),
                array('attr', 'is_delete', '>=', 0)
            )
        ));
        $v['items'] = array();
        foreach ($parents as $p){
            $v['items'][] = array(
                'title' => ($p->title->isExist()) ? $p->title->value() : $p->name(),
                'url' => '/admin'.$p->uri(),
                'uri' => $p->uri(),
                'active' => empty($v['items']) // активный первый элемент
            );
        }
		return parent::work($v);
	}
}
