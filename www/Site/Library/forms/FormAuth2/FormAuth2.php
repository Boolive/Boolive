<?php
/**
 * Вход
 * Форма авторизации (входа) пользователя
 * @version 1.0
 */
namespace Site\Library\forms\FormAuth2;

use Boolive\auth\Auth;
use Boolive\data\Data;
use Boolive\errors\Error;
use Site\Library\forms\SimpleForm\SimpleForm,
    Boolive\values\Rule;

class FormAuth2 extends SimpleForm
{
    /**
     * Правило обработки формы
     * @return Rule
     */
    function processRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'login' => Rule::string()->more(0)->max(250)->required(),
                'passw' => Rule::string()->more(0)->max(250)->required(),
                'remember' => Rule::bool()->default(false)->required()
            ))
        ));
    }

    /**
     * Выполнение действия, если форма корректна
     * @param \Boolive\commands\Commands $commands
     * @param array $input
     * @return bool|string
     */
    function process($commands, $input)
    {
        $user = Data::read(array(
            'select' => 'children',
            'from' => '/Members',
            'depth' => 'max',
            'where' => array(
                array('attr', 'is_link', '=', '0'),
                array('attr', 'name', '=', $input['REQUEST']['login']),
                array('child', 'passw', array(
                    array('attr', 'value', '=', Auth::getHash($input['REQUEST']['passw']))
                )),
            ),
            'limit' => array(0,1)
        ), false);
        // Пользователь найден?
        if ($user){
            Auth::setUser($user[0], $input['REQUEST']['remember']?2500000:0);
        }else{
            $e = new Error();
            $e->REQUEST->arrays->login = "Неверный логин или пароль";
            throw $e;
        }
    }

    function show($v = array(), $commands, $input, $session = array())
    {
        // Значения полей
        //$v['value'] = array();
        $v['value']['login'] = isset($session['input']['REQUEST']['login']) ? $session['input']['REQUEST']['login'] : '';
        $v['value']['passw'] = isset($session['input']['REQUEST']['passw']) ? $session['input']['REQUEST']['passw'] : '';
        $v['value']['remember'] = isset($session['input']['REQUEST']['remember']) ? $session['input']['REQUEST']['remember'] : false;
        // Ошибки полей
        //$v['error'] = array();
        if (isset($session['error']) && $session['error']->REQUEST->arrays->isExist()){
            /** @var Error $error */
            $error = $session['error']->REQUEST->arrays;
            foreach (array_keys($v['value']) as $name){
                if (isset($error->{$name})){
                    $v['error'][$name] = $error->{$name}->getUserMessage(true,'.');
                }
            }
        }
        return parent::show($v,$commands, $input, $session);
    }
}