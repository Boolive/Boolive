<?php
/**
 * Виджет для файла изображения
 *
 * @version 1.0
 */
namespace Library\content_widgets\Image;

use Library\basic\widgets\Widget\Widget,
    Boolive\values\Rule;

class Image extends Widget
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

    public function work($v = array())
    {
        $v['file'] = $this->_input['GET']['object']->getFile();
        return parent::work($v);
    }
}
