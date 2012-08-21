<?php
/**
 * Виджет форматированного текста
 *
 * @version 1.0
 */
namespace Library\content_widgets\RichText;

use Library\basic\widgets\ViewObjectsList\ViewObjectsList,
    Boolive\values\Rule;

class RichText extends ViewObjectsList
{
    public function getInputRule()
    {
        return Rule::arrays(array(
            'GET' => Rule::arrays(array(
                'object' => Rule::entity()->required(), // объект, который отображать
                ), Rule::any() // не удалять другие элементы
            )), Rule::any() // не удалять другие элементы
        );
    }

    public function canWork()
    {
        if ($result = parent::canWork()){
            // По URL определяем объект и номер страницы
            $this->_input['GET']['objects_list'] = $this->_input['GET']['object']->findAll(array('order'=>'`order` ASC'));
            unset($this->_input['GET']['objects_list']['title']);
        }
        return $result;
    }

    public function work($v = array()){
        return parent::work($v);
    }
}
