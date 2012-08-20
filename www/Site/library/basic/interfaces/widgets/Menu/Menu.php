<?php
/**
 * Меню
 *
 * @version 1.0
 */
namespace library\basic\interfaces\widgets\Menu;

use library\basic\interfaces\widgets\Widget\Widget,
    Boolive\values\Rule;

class Menu extends Widget
{
    public function getInputRule()
    {
        return Rule::arrays(array(
            'GET' => Rule::arrays(array(
                'object' => Rule::entity()->default(null), // объект, который отображается (активный пункт меню)
                ), Rule::any()
            )), Rule::any()
        );
    }

    public function work($v = array()){
        // Выбираем пункты меню
        $this->_input['GET']['objects_list'] = $this->items->findAll(array('order' =>'`order` ASC'));
        // Активный пункт меню
        $this->_input['GET']['object_active'] = $this->_input['GET']['object'];
        // Отображение всех пунктов с помощью подчиненного views
        $v['views'] = $this->startChild('views');
        return parent::work($v);
    }
}
