<?php
/**
 * Название
 *
 * @version 1.0
 * @date 24.04.2013
 * @author Polina Shestakova <paulinep@yandex.ru>
 */
namespace site\library\basic\Password;

use boolive\auth\Auth,
    boolive\data\Entity;

class Password extends Entity
{
    function value($new_value = null)
    {
        if(isset($new_value) && mb_strlen($new_value)!=64){
            $new_value = Auth::getHash($new_value);
        }
        return parent::value($new_value);
    }
}