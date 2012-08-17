<?php
/**
 * Виджет раздела
 *
 * @version 1.0
 */
namespace library\layouts\boolive\center\Content\option_part\Part;

use library\basic\interfaces\widgets\ViewObjectsList\ViewObjectsList,
    Boolive\values\Rule;

class Part extends ViewObjectsList
{
    public function getInputRule()
    {
        return Rule::arrays(array(
            'GET' => Rule::arrays(array(
                'object' => Rule::entity()->required(), // объект, который отображать
                'page'=> Rule::int()->default(1)->required() // номер страницы
                ), Rule::any() // не удалять другие элементы
            )), Rule::any() // не удалять другие элементы
        );
    }

    public function canWork()
    {
        if ($result = parent::canWork()){
            // Выбираем подчиненных раздела с учётом текущей страницы
            $this->_input->GET->objects_list = $this->_input->GET->object->get()->findAll(array(
                'order' =>'`order` ASC',
                'start' => ($this->_input->GET->page->int() - 1) * 4,
                'count' => 4
            ));
        }
        return $result;
    }
}