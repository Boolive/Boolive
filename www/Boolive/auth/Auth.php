<?php
/**
 * Модуль авторизации и аутентификации пользователя
 * Определяет текущего пользователя, выполняет вход/выход и регистрацию пользователя
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\auth;

use Boolive\data\Data;

class Auth
{
    /**
     * Текущий пользователь
     * @return \Library\basic\members\Member\Member
     */
    static function getUser()
    {
        return Data::read('/Members/registered/admins/vova', '', 0, null, false, false);
    }
}