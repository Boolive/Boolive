<?php
/**
 * Поле формы
 *
 * @version 1.0
 */
namespace Site\library\forms\FormField;

use Site\library\views\Widget\Widget;

class FormField extends Widget
{
    function startInit($input)
    {
        parent::startInit($input);
        if (isset($this->_input['REQUEST']['object']) &&
            isset($input['REQUEST'][$this->_input['REQUEST']['object']->uri()]))
        {
            $this->_input['REQUEST']['input'] = $input['REQUEST'][$this->_input['REQUEST']['object']->uri()];
        }
    }

    function show($v = array(), $commands, $input)
    {
        $v['error'] = '';
        if (isset($this->_input['REQUEST']['input'])){
            $this->_input['REQUEST']['object']->value($this->_input['REQUEST']['input']);
            /** @var $error \Boolive\errors\Error */
            $error = null;
            if (!$this->_input['REQUEST']['object']->check($error)){
                $v['error'] = $error->getUserMessage(true);
            }
        }
        $v['uri'] = $this->_input['REQUEST']['object']->uri();
        $v['title'] = $this->_input['REQUEST']['object']->title->inner()->value();
        $v['value'] = $this->_input['REQUEST']['object']->value();
        $v['id'] = md5($this->uri());
        return parent::show($v, $commands, $input);
    }
}
