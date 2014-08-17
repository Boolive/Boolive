<?php
/**
 * Форма универсальная
 * Отображает свойства объекта соответствующими полями. По умолчанию сохраняет объект
 * @version 1.0
 */
namespace site\library\forms\AutoForm;

use boolive\data\Entity;
use boolive\errors\Error;
use boolive\functions\F;
use boolive\input\Input;
use boolive\session\Session;
use boolive\values\Rule;
use site\library\views\AutoWidgetList2\AutoWidgetList2;

class AutoForm extends AutoWidgetList2
{
    const FROM_RESULT_NO = 0;
    const FORM_RESULT_ERROR = 1;
    const FORM_RESULT_OK = 2;

    private $_token;
    private $_base_uri;
    private $_result = self::FROM_RESULT_NO;

    function startRule()
    {
        $rule = parent::startRule();
        $rule->arrays[0]['REQUEST']->arrays[0]['form'] = Rule::eq($this->id())->default(false)->required();
        $rule->arrays[0]['COOKIE']->arrays[0]['token'] = Rule::string()->max(32)->default(false)->required();
        return $rule;
    }

    function startInitChild($input)
    {
        parent::startInitChild($input);
        $this->_base_uri = $this->_input_child['REQUEST']['base_uri'] = $this->_input['REQUEST']['object']->uri();
    }

    function work()
    {
        if ($this->_input['REQUEST']['form']!==false){
            // Обработка формы
            $session = array();
            try{
                // Вызов полей для свойств объекта
                $this->_input_child['REQUEST']['call'] = 'check';
                $list = $this->getList();
                if (is_array($list)){
                    foreach ($list as $obj){
                        $name = preg_replace('/'.preg_quote($this->_base_uri.'/','/').'/u', '', $obj->uri());
                        if (isset($this->_input_child['REQUEST'][$name])){
                            $this->_input_child['REQUEST']['value'] = $this->_input_child['REQUEST'][$name];
                        }
                        if (isset($this->_input_child['FILES'][$name])){
                            $this->_input_child['FILES']['value'] = $this->_input_child['FILES'][$name];
                        }
                        $this->showObject($obj);
                    }
                }
                if (!$this->_input['REQUEST']['object']->errors()->isExist()){
                    // Выполнение действия
                    $this->process();
                    $this->_result = self::FORM_RESULT_OK;
                    if (!($redirect = $this->_commands->get('redirect'))){
                        $redirect = $this->redirect->inner();
                        if (!$redirect->isDraft() && $redirect->value()!=''){
                            $this->_commands->redirect(Input::url($redirect->value()));
                        }
                    }
                }else{
                    $this->_result = self::FORM_RESULT_ERROR;
                }
            }catch (\Exception $error){
                $this->_result = self::FORM_RESULT_ERROR;
            }
            $session['result'] = $this->_result;
            if ($this->_result == self::FORM_RESULT_ERROR){
                $session['object'] = $this->_input['REQUEST']['object']->toArray();
                // @todo Для ajax запросов нужна развернутая информация об ошибках для каждого поля
                $session['message'] = 'Ошибки';
            }else
            if ($this->_result == self::FORM_RESULT_ERROR){
                $session['message'] = 'Успех';
            }
            // @todo Для ajax запросов в сессию сохранять нет смысла
            Session::set('form', array($this->id().$this->getToken() => $session));
            setcookie('token', $this->getToken(), 0, '/');
            return $session;
        }else{
            // Отображение формы
            $v = array();
            if (isset($this->_input['COOKIE']['token']) && Session::isExist('form')){
                $form = Session::get('form');
                if (isset($form[$this->id().$this->_input['COOKIE']['token']])){
                    $form = $form[$this->id().$this->_input['COOKIE']['token']];
                    Session::remove('form');
                }
                if (isset($form['object'])){
                    $this->_input['REQUEST']['object'] = Entity::fromArray($form['object']);
                }
                if (isset($form['result'])){
                    $this->_result = $form['result'];
                }
            }
            return $this->show($v, $this->_commands, $this->_input);
        }
    }

    function show($v = array(), $commands, $input)
    {
        $v['title'] = $this->title->inner()->value();
        $v['result'] = $this->_result;
        if ($this->_result == self::FORM_RESULT_ERROR){
            $v['message'] = $this->message_error->inner()->value();
        }else
        if ($this->_result == self::FORM_RESULT_OK){
            $v['message'] = $this->message_ok->inner()->value();
        }
        $obj = $this->_input['REQUEST']['object'];
        if ($obj->isExist()){
            $v['object'] = $obj->id();
        }else{
            $v['object'] = array();
            if ($p = $obj->proto()) $v['object']['proto'] = $p->uri();
            if ($p = $obj->parent()) $v['object']['parent'] = $p->uri();
            $v['object'] = F::toJSON($v['object'], false);
        }
        return parent::show($v,$commands, $input);
    }

    function process()
    {
        return true;
    }

    /**
     * Токен для сохранения в сессию ошибочных данных формы
     * @param bool $remake
     * @return string
     */
    function getToken($remake = false)
    {
        if (!isset($this->_token) || $remake){
            $this->_token = uniqid('', true);
        }
        return (string)$this->_token;
    }

    protected function getList($cond = array())
    {
        $cond['key'] = 'name';
        /** @var Entity $obj */
        $obj = $this->_input['REQUEST']['object'];
        if ($obj->errors()->isExist()){
            return $obj->children();
        }else{
            return $obj->find($cond, true);
        }
        //$cond['comment'] = 'read list of objects in the AutoWidgetList2';

    }

    function classTemplate($methods = array(), $use = array())
    {
        if (!isset($methods['process'])){
            $methods['process'] =
<<<php
        /**
         * Выполнение действие с объектов формы
         */
        function process()
        {
            \$obj = \$this->_input['REQUEST']['object'];
            return true;
        }
php;
        }
        return parent::classTemplate($methods, $use);
    }
}