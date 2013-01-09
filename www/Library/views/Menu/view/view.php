<?php
/**
 * Автоматическое представление пунктов меню
 *
 * @version 1.0
 */
namespace Library\views\Menu\view;

use Boolive\values\Rule,
    Library\views\AutoWidgetList\AutoWidgetList;

class view extends AutoWidgetList
{
    public function getInputRule(){
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::entity()->required(), // Объект для пункта меню
                'active' => Rule::entity()->default(null)->required()// Активный объект (пункт меню)
                )
            ))
        );
    }

    protected function initInputChild($input){
        parent::initInputChild($input);
        $this->_input_child['REQUEST']['active'] = $this->_input['REQUEST']['active'];
        $this->_input_child['REQUEST']['object'] = $this->_input['REQUEST']['object'];
    }

    public function work($v = array()){
        return parent::work($v);
    }

//    protected function getList($protos){
//        // @todo Сделать настраиваемый фильтр
//        $list = $this->_input['REQUEST']['object']->find(array(
//            'where' => array(
//                array('is', $protos)
//            ),
//            'order' => array(
//                array('order', 'ASC')
//            )
//        ));
//        return $list;
//    }
}