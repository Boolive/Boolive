<?php
/**
 * Выход
 * Отмена авторизации
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\views\Logout;

use Library\views\View\View,
    Boolive\auth\Auth,
    Boolive\input\Input,
    Boolive\values\Rule;

class Logout extends View
{
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'logout' => Rule::bool()->required()
                    )
                )
            )
        );
    }

    public function work()
    {
        Auth::setUser(null);
        $this->_commands->redirect(Input::url(null, 0, array(), false, true));
        return false;
    }
}
