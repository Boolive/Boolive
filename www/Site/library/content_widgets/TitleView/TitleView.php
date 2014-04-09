<?php
/**
 * Виджет заголовка
 *
 * @version 1.0
 */
namespace Site\library\content_widgets\TitleView;

use Boolive\input\Input;
use Site\library\views\Widget\Widget;

class TitleView extends Widget
{
    function show($v = array(), $commands, $input)
    {
        $v['value'] = $this->_input['REQUEST']['object']->value();
        if ($parent = $this->_input['REQUEST']['object']->parent()){
            $v['parent_uri'] = Input::url($parent->uri());
        }else{
            $v['parent_uri'] = '';
        }
        return parent::show($v, $commands, $input);
    }
}