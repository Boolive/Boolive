<?php
/**
 * Регистрация
 * Форма регистрации пользователя
 * @version 1.0
 */
namespace Library\forms\FormReg;

use Boolive\auth\Auth;
use Boolive\commands\Commands;
use Boolive\data\Data;
use Boolive\errors\Error;
use Boolive\values\Rule,
    Library\forms\SimpleForm\SimpleForm;

class FormReg extends SimpleForm
{
    /**
     * Правило обработки формы
     * @return Rule
     */
    function processRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'email' => Rule::email()->more(0)->max(250)->required(),
                'login' => Rule::string()->min(3)->max(250)->required(),
                'passw' => Rule::string()->min(3)->max(250)->ignore('max')->required(),
                'passw_retry' => Rule::scalar()->required()
            ))
        ));
    }

    function processErrorDictionary()
    {
        return array(
            'login' => array(
                'string' => 'Введите логин',
                'min' => 'Допустимая длина логина от 3 до 50 символов',
                'max' => 'Допустимая длина логина от 3 до 50 символов',
            ),
            'passw' => array(
                'min' => 'Допустимая длина пароля от 3 до 50 символов',
                'max' => 'Допустимая длина пароля от 3 до 50 символов',
            ),
            'passw_retry' => array(
                //'string' => 'Введите пароль ещё раз',
            )
        );
    }

    function processCheck($commands, $input, &$error)
    {
        $input = parent::processCheck($commands, $input, $error);
        if ($input['REQUEST']['passw'] !== $input['REQUEST']['passw_retry']){
            if (!$error) $error = new Error();
            $error->REQUEST->arrays->passw_retry->eq = "Пароли не совпадают";
        }
        if (!$error){
            // Проверка уникальности логина
            $result = Data::read(array(
                'from' =>  '/Members',
                'select' => array('exists','children'),
                'depth' => 'max',
                'where' => array(
                    array('is', '/Library/access/User'),
                    array('attr','name','=',$input['REQUEST']['login'])
                )
            ), false);
            if ($result){
                if (!$error) $error = new Error();
                $error->REQUEST->arrays->login->exists = "Логин занят";
            }
            // Проверка уникальности мыла
            $result = Data::read(array(
                'from' =>  '/Members',
                'select' => array('exists','children'),
                'depth' => 'max',
                'where' => array(
                    array('is', '/Library/access/User'),
                    array('child','email',array(
                        array('attr','value','=',$input['REQUEST']['email'])
                    ))
                )
            ), false);
            if ($result){
                if (!$error) $error = new Error();
                $error->REQUEST->arrays->email->exists = "Email адрес занят";
            }
        }
        return $input;
    }

    function show($v = array(), $commands, $input, $session = array())
    {
        // Значения полей
        //$v['value'] = array();
        $v['value']['login'] = isset($session['input']['REQUEST']['login']) ? $session['input']['REQUEST']['login'] : '';
        $v['value']['email'] = isset($session['input']['REQUEST']['email']) ? $session['input']['REQUEST']['email'] : '';
        $v['value']['passw'] = isset($session['input']['REQUEST']['passw']) ? $session['input']['REQUEST']['passw'] : '';
        $v['value']['passw_retry'] = isset($session['input']['REQUEST']['passw_retry']) ? $session['input']['REQUEST']['passw_retry'] : '';
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

        return parent::show($v, $commands, $input, $session);
    }

    /**
     * Выполнение действия, если форма корректна
     * @param Commands $commands
     * @param array $input
     * @return bool|string
     */
    function process($commands, $input)
    {
        // Созданание юзера
        $user = Auth::getUser();
        $user->parent('/Members/registered');
        $user->name($input['REQUEST']['login']);
        $user->title->value($input['REQUEST']['login']);
        $user->email->value($input['REQUEST']['email']);
        $user->email->isDraft(true);//неподтвержденный email
        $user->passw->value($input['REQUEST']['passw']);
        $user->save(true, false);
        return true;
    }
}