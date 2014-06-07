<?php
/**
 * Поле формы
 * Эталон поля ввода строки
 * @version 1.0
 */
namespace site\library\forms\AutoForm\views\FormField;

use boolive\data\Entity;
use boolive\values\Rule;
use site\library\forms\FormField\FormField as FormField_1;
use site\library\views\Widget\Widget; // @todo Объект наследует FormField_1!!

class FormField extends Widget
{
    function startRule()
    {
        $rule = parent::startRule();
        $rule->arrays[0]['REQUEST']->arrays[0]['value'] = Rule::scalar();
        $rule->arrays[0]['REQUEST']->arrays[0]['base_uri'] = Rule::string()->default('')->required();
        $rule->arrays[0]['REQUEST']->arrays[0]['call'] = Rule::string()->default('show')->required();
        return $rule;
    }

    function work()
    {
        switch ($this->_input['REQUEST']['call']){
            case 'check':
                return $this->processCheck();
                break;
            case 'save':
                if ($this->processCheck() === true){
                    return $this->process();
                }else{
                    return false;
                }
                break;
            default:
                return parent::work();
        }
    }

    function show($v = array(), $commands, $input)
    {
        /** @var Entity $obj */
        $obj = $this->_input['REQUEST']['object'];
        $check = $this->processCheck();
        $v['error'] = isset($check['error'])? $check['error'] : false;
        $v['uri'] = preg_replace('/'.preg_quote($this->_input['REQUEST']['base_uri'].'/','/').'/u', '', $obj->uri());
        $v['title'] = $obj->title->inner()->value();
        $v['value'] = $obj->value();
        $v['id'] = $v['uri'];
        return parent::show($v, $commands, $input);
    }

    function processCheck()
    {
        if (isset($this->_input['REQUEST']['value'])){
            $obj = $this->_input['REQUEST']['object'];
            $obj->value($this->_input['REQUEST']['value']);
            /** @var $error \boolive\errors\Error */
            $error = null;
            if (!$obj->check($error)){
                return array('error' => $error->getUserMessage(true));
            }
        }
        return true;
    }

    function process()
    {
        return false;
    }
}