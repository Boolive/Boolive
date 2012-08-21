<?php
/**
 * Виджет заголовка
 *
 * @version 1.0
 */
namespace Library\content_widgets\Title;

use Library\basic\widgets\Widget\Widget,
    Boolive\values\Rule;

class Title extends Widget
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
            $this->_input['GET']['objects_list'] = $this->_input['GET']['object']->findAll();
        }
        return $result;
    }

    public function work($v = array())
    {
        echo '<h2>'.$this->_input['GET']['object']->getValue().'</h2>';
    }
}
