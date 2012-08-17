<?php
/**
 * Виджет анонса раздела
 *
 * @version 1.0
 */
namespace library\layouts\boolive\center\Content\option_part\Part\option_part\PartPreview;

use library\basic\interfaces\widgets\ViewObjectsList\ViewObjectsList,
    Boolive\values\Rule;

class PartPreview extends ViewObjectsList
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
            $this->_input->GET->objects_list = $this->_input->GET->object->get()->findAll();
        }
        return $result;
    }

    public function work($v = array())
    {
        echo 'PartPreview';
    }
}
