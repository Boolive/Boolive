<?php
/**
 * Пользователь
 *
 * @version 1.0
 * @date 29.12.2012
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Site\library\access\User;

use Site\library\access\Member\Member;

class User extends Member
{

    function value($new_value = null, $get = false)
    {
        $v = parent::value($new_value);
        return $get? $v : '';
    }
}