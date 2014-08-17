<?php
/**
 * Меню "хлебные крошки"
 * Отображает путь на текущий объект с возможностью перехода к его родителям
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace site\library\admin\Admin\BreadcrumbsMenu;

use boolive\data\Data2,
    boolive\data\Entity,
    boolive\values\Check,
    boolive\values\Rule,
    site\library\views\Widget\Widget,
    boolive\input\Input;

class BreadcrumbsMenu extends Widget{

	function show($v = array(), $commands, $input)
    {
        /** @var $obj \boolive\data\Entity */
		$obj = $this->_input['REQUEST']['object'];
        $v['current'] = $obj->uri();
        $v['items'] = $this->getItems($obj);
		return parent::show($v, $commands, $input);
	}

    /**
     * Получения списка пунктов меню
     * @param Entity $object
     * @return array
     */
    private function getItems($object)
    {
        $parents = $object->find(array(
            'select' => 'parents',
            'depth' => array(0,'max'),
            'order' => array('parent_cnt', 'desc'),
            'where' => array(
                array('is_draft', '>=', 0),
                array('is_hidden', '>=', 0)
            )
        ));
        if ($object->isRemote()){
            $parents[] = Data2::read();
        }
        $items = array();
        foreach ($parents as $p){
            $item = array(
                'url' => ltrim($p->uri(),'/'),
                'uri' => $p->uri(),
                'class' => empty($items) ? 'active' : '' // активный первый элемент
            );
            if ($p->isRemote()){
                $item['class'].=' remote';
            }
            if ($p->name() == '' && $p->isRemote()){
                $item['title'] = $p->uri();
            }else{
                $t = $p->title->inner();
                $item['title'] = $t->isExist()||$t->isInner() ? $t->value() : $p->name();
            }
            $items[] = $item;
        }

        return $items;
    }

    /**
     * @param $input
     * @return array|null
     */
    function call_getBreadcrumbs($input)
    {
        $input = Check::filter($input, Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::entity()->required()
            ))
        )), $error);
        if (!$error){
            return $this->getItems($input['REQUEST']['object']);
        }else{
            return null;
        }
    }
}
