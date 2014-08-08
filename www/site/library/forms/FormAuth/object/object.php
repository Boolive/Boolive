<?php
/**
 * Модель авторизации
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace site\library\forms\FormAuth\object;

use boolive\data\Entity,
    boolive\auth\Auth,
    boolive\data\Data2;

class object extends Entity
{
    function auth()
    {
        if ($this->check()){

            $user = Data2::read(array(
                'select' => 'children',
                'from' => '/members',
                'depth' => array(1,'max'),
                'where' => array(
                    array('is_link', '=', '0'),
                    array('name', '=', $this->name->value()),
//                    array('child', 'email', array(
//                        array('value', '=', $this->email->value())
//                    )),
                    array('child', 'passw', array(
                        array('value', '=', Auth::getHash($this->passw->value()))
                    )),
                ),
                'limit' => array(0,1)
            ), false);
            // Пользователь найден?
            if ($user){
                Auth::setUser($user[0], $this->remember->value()?2500000:0);
                return true;
            }
        }
        return false;
    }
}
