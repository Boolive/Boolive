<?php
/**
 * Меню "хлебные крошки"
 * Отображает путь на текущий объект с возможностью перехода к его родителям
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\layouts\Admin\BreadcrumbsMenu;

use Library\views\Widget\Widget;

class BreadcrumbsMenu extends Widget{

	public function work($v = array())
    {
        /** @var $obj \Boolive\data\Entity */
		$obj = $this->_input['REQUEST']['object'];
		$v['items'] = array();
		do{
            $v['items'][] = array(
                'title' => ($name = $obj->name()) ? $name : $obj->title->value(),
                'url'	=> $obj->uri(),
                'active' => empty($v['items']) // активный первый элемент
            );
		}while($obj = $obj->parent());

		return parent::work($v);
	}
}
