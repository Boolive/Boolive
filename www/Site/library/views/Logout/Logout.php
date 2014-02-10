<?php
/**
 * Выход
 * Отмена авторизации
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Site\library\views\Logout;

use Site\library\views\View\View,
    Boolive\auth\Auth,
    Boolive\input\Input,
    Boolive\values\Rule;

class Logout extends View
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'logout' => Rule::bool()->required()
            ))
        ));
    }

    function work()
    {
        Auth::setUser(null);
        $this->_commands->redirect(Input::url(null, 0, array()));
        return false;//какбы не работали, чтобы продолжился запуск следующих предствлений
    }
}
