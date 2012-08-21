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
            $count_per_page = max(1, $this->count_per_page->getValue());
            $obj = $this->_input['GET']['object'];
            // Выбираем подчиненных раздела с учётом текущей страницы
            $this->_input['GET']['objects_list'] = $obj->findAll(array(
                'order' =>'`order` ASC',
                'start' => ($this->_input['GET']['page'] - 1) * $count_per_page,
                'count' => $count_per_page
            ));
            $this->_input['GET']['page_count'] = ceil($obj->findCountAll()/$count_per_page);
        }
        return $result;
    }
}