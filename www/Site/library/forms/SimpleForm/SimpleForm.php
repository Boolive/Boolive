<?php
/**
 * Форма простая
 * Эталон простейшей формы. Поля формы прописываются в шаблоне.
 * @version 1.0
 */
namespace Site\library\forms\SimpleForm;

use Boolive\errors\Error;
use Boolive\session\Session,
    Boolive\values\Check,
    Boolive\values\Rule,
    Site\library\views\Widget\Widget;

class SimpleForm extends Widget
{
    private $_token;

    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                //'object' => Rule::entity($this->object_rule->value())->required(),
                'path' => Rule::regexp($this->path_rule->value())->required(),
                'form' => Rule::eq($this->id())->default(false)->required()
            )),
            'COOKIE' => Rule::arrays(array(
                'token' => Rule::string()->max(32)->default(false)->required()
            ))
        ));
    }

    function work()
    {
        if ($this->_input['REQUEST']['form']!==false){
            // Обработка формы
            $input = $this->processCheck($this->_commands, $this->_input_all, $error);
            if (!$error){
                Session::remove('form');
                try{
                    return $this->process($this->_commands, $input);
                }catch (Error $error){}
            }
            if ($error){
                $error->setDictionary($this->processErrorDictionary());
                Session::set('form', array($this->id().$this->getToken() => array(
                    'error' => $error,
                    'input' => $this->_input_all
                )));
                setcookie('token', $this->getToken(), 0, '/');
            }
            // Несмотря на ошибки, форма работала
            return true;
        }else{
            // Отображение формы
            $session = array();
            if ($this->_input['COOKIE']['token'] && Session::isExist('form')){
                $form = $session = Session::get('form');
                if (isset($form[$this->id().$this->_input['COOKIE']['token']])){
                    $session = $form[$this->id().$this->_input['COOKIE']['token']];
                    Session::remove('form');
                }
            }
            return $this->show(array(), $this->_commands, $this->_input, $session);
        }
    }

    /**
     * Отображение формы
     * @param array $v Значения, подготавливаемые для вставки в шаблоне
     * @param $commands Командв для родительских и подчиненных видов
     * @param $input Отфильтрованные данные по startRule()
     * @param array $session Данные и ошибки формы от предыдущей отправки.
     * @return string
     */
    function show($v = array(), $commands, $input, $session = array())
    {
        return parent::show($v, $commands, $input);
    }

    /**
     * Правило обработки формы
     * @return Rule
     */
    function processRule()
    {
        return Rule::any();
    }

    /**
     * Фильтр и проверка формы
     * @param $commands
     * @param $input
     * @param $error
     * @return mixed
     */
    function processCheck($commands, $input, &$error)
    {
        return Check::filter($input, $this->processRule(), $error);
    }

    /**
     * Словарь сообщений об ошибках по правилу формы
     * @return array
     */
    function processErrorDictionary()
    {
        return array();
    }

    /**
     * Обработка формы
     * @param $commands
     * @param $input
     * @return bool
     */
    function process($commands, $input)
    {

    }

    /**
     * Токен для сохранения в сессию ошибочных данных формы
     * @param bool $remake
     * @return string
     */
    function getToken($remake = false)
    {
        if (!isset($this->_token) || $remake){
            $this->_token = uniqid();
        }
        return (string)$this->_token;
    }

    function classTemplate($methods = array(), $use = array())
    {
        $use[] = 'Boolive\values\Rule';
        if (!isset($methods['processRule'])){
            $methods['processRule'] = <<<code
    /**
     * Правило обработки формы
     * @return Rule
     */
    function processRule()
    {
//        return Rule::arrays(array(
//            'REQUEST' => Rule::arrays(array(
//                'field1' => Rule::string()->more(0)->max(250)->required(),
//                // другие поля..
//            ))
//        ));
        return parent::processRule();
    }
code;
        }
        if (!isset($methods['process'])){
            $methods['process'] = <<<code
    /**
     * Выполнение действия, если форма корректна
     * @param \Boolive\commands\Commands \$commands
     * @param array \$input
     * @return bool|string
     */
    function process(\$commands, \$input)
    {
        return parent::process(\$commands, \$input);
    }
code;
        }
        if (!isset($methods['show'])){
            $methods['show'] = <<<code
    function show(\$v = array(), \$commands, \$input, \$session = array())
    {
        return parent::show(\$v,\$commands, \$input, \$session);
    }
code;
        }
        return parent::classTemplate($methods, $use);
    }
}