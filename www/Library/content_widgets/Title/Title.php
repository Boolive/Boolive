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

    public function work($v = array())
    {
        $v['value'] = $this->_input['GET']['object']->getValue();
        if ($parent = $this->_input['GET']['object']->parent()){
            $v['parent_uri'] = $parent['uri'];
            if (mb_substr($v['parent_uri'],0,10)=='/Contents/'){
                $v['parent_uri'] = mb_substr($v['parent_uri'],10);
            }
        }else{
            $v['parent_uri'] = '';
        }

        return parent::work($v);
    }
}
