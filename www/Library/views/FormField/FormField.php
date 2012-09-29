<?php
/**
 * Поле формы
 *
 * @version 1.0
 */
namespace Library\views\FormField;

use Library\views\Widget\Widget,
    Boolive\values\Rule;

class FormField extends Widget
{
    protected function initInput($input)
    {
        parent::initInput($input);
        if (isset($this->_input['REQUEST']['object']) &&
            isset($input['REQUEST'][$this->_input['REQUEST']['object']['uri']]))
        {
            $this->_input['REQUEST']['input'] = $input['REQUEST'][$this->_input['REQUEST']['object']['uri']];
        }
    }

    public function work($v = array())
    {
        $v['error'] = '';
        if (isset($this->_input['REQUEST']['input'])){
            $this->_input['REQUEST']['object']['value'] = $this->_input['REQUEST']['input'];
            /** @var $error \Boolive\errors\Error */
            $error = null;
            if (!$this->_input['REQUEST']['object']->check($error)){
                $v['error'] = $error->getUserMessage(true);
            }
        }
        $v['uri'] = $this->_input['REQUEST']['object']['uri'];
        $v['title'] = $this->_input['REQUEST']['object']->title->getValue();
        $v['value'] = $this->_input['REQUEST']['object']->getValue();
        $v['id'] = md5($this['uri']);
        return parent::work($v);
    }
}
