<?php
/**
 * Выход
 * Отмена авторизации
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace site\library\views\Logout;

use site\library\views\View\View,
    boolive\auth\Auth,
    boolive\input\Input,
    boolive\values\Rule;

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
        // редирект на текущий URL без параметров
        $this->_commands->redirect(Input::url());
        return false;//какбы не работали, чтобы продолжился запуск следующих предствлений
    }
}
