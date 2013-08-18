<?php
/**
 * Название
 *
 * @version 1.0
 * @date 24.04.2013
 * @author Polina Shestakova <paulinep@yandex.ru>
 */
namespace Library\basic\simple\Password;

use Boolive\auth\Auth,
    Boolive\data\Entity;

class Password extends Entity
{
    public function value($new_value = null)
    {
        if(isset($new_value) && mb_strlen($new_value)!=64){
            $new_value = Auth::getHash($new_value);
        }
        return parent::value($new_value);
    }
}