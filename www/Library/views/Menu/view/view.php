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
            'GET' => Rule::arrays(array(
                'object' => Rule::entity()->required(), // Объект для пункта меню
                'active' => Rule::entity()->default(null)->required()// Активный объект (пункт меню)
                )
            ))
        );
    }

    protected function initInputChild($input){
        parent::initInputChild($input);
        $this->_input_child['GET']['active'] = $this->_input['GET']['active'];
        $this->_input_child['GET']['object'] = $this->_input['GET']['object'];
    }

    public function work($v = array()){
        return parent::work($v);
    }

    protected function getList(){
        // @todo Сделать настраиваемый фильтр
        $list = $this->_input['GET']['object']->findAll(array('order' =>'`order` ASC'));
        foreach ($list as $key => $object){
            /** @var $object \Boolive\data\Entity */
            if (!$object->is('/Library/content_samples/Page') && !$object->is('/Library/content_samples/Part')){
                unset($list[$key]);
            }
        }
        return $list;
    }
}