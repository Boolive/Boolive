<?php
/**
 * Модель авторизации
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\views\FormAuth\object;

use Boolive\data\Entity,
    Boolive\auth\Auth,
    Boolive\data\Data;

class object extends Entity
{
    public function auth()
    {
        if ($this->check()){

            $user = Data::select(array(
                'from' => array('/Members'),
                'where' => array(
                    array('attr', 'is_link', '=', '0'),
                    array('attr', 'name', '=', $this->name->value()),
//                    array('child', 'email', array(
//                        array('attr', 'value', '=', $this->email->value())
//                    )),
                    array('child', 'passw', array(
                        array('attr', 'value', '=', Auth::getHash($this->passw->value()))
                    )),
                ),
                'limit' => array(0,1)
            ), null, false);
            // Пользователь найден?
            if ($user){
                Auth::setUser($user[0], $this->remember->value()?2500000:0);
                return true;
            }
        }
        return false;
    }
}
