<?php
/**
 * Меню "хлебные крошки"
 * Отображает путь на текущий объект с возможностью перехода к его родителям
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\layouts\Admin\BreadcrumbsMenu;

use Boolive\data\Data,
    Boolive\data\Entity,
    Boolive\values\Check,
    Boolive\values\Rule,
    Library\views\Widget\Widget,
    Boolive\input\Input;

class BreadcrumbsMenu extends Widget{

	public function work($v = array())
    {
        /** @var $obj \Boolive\data\Entity */
		$obj = $this->_input['REQUEST']['object'];
        $v['current'] = $obj->uri();
        $v['items'] = $this->getItems($obj);
		return parent::work($v);
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
                array('attr', 'diff', '>=', 0),
                array('attr', 'is_draft', '>=', 0)
            )
        ));
        if ($object->isRemote()){
            $parents[] = Data::read();
        }
        $items = array();
        foreach ($parents as $p){
            $item = array(
                'url' => '/admin'.$p->uri(),
                'uri' => $p->uri(),
                'class' => empty($items) ? 'active' : '' // активный первый элемент
            );
            if ($p->isRemote()){
                $item['class'].=' remote';
            }
            if ($p->name() == '' && $p->isRemote()){
                $item['title'] = $p->uri();
            }else{
                $item['title'] = ($p->title->isExist()) ? $p->title->value() : $p->name();
            }
            $items[] = $item;
        }

        return $items;
    }

    /**
     * @param $input
     * @return array|null
     */
    public function call_getBreadcrumbs($input)
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
