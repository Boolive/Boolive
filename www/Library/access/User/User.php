<?php
/**
 * Пользователь
 *
 * @version 1.0
 * @date 29.12.2012
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\access\User;

use Library\access\Member\Member;

class User extends Member
{

    public function value($new_value = null, $get = false)
    {
        $v = parent::value($new_value);
        return $get? $v : '';
    }

    public function exportedProperties()
    {
        $names = parent::exportedProperties();
        array_push($names, 'email', 'name', 'passw', 'visit_time');
        return $names;
    }
}