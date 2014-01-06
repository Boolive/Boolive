<?php
/**
 * Логика
 * 
 * @version 1.0
 */
namespace Library\admin_widgets\LogicEditor;

use Library\views\Widget\Widget;

class LogicEditor extends Widget
{
    function show($v = array(), $commands, $input)
    {
        $v['data-o'] = $this->_input['REQUEST']['object']->uri();
        $v['content'] = $this->_input['REQUEST']['object']->classContent(false, false);
        $v['content'] = $v['content']['content'];
        $v['555'] = '000000';
        return parent::show($v,$commands, $input);
    }
}