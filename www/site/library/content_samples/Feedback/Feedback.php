<?php
/**
 * Обратная связь
 * Информационный объект обратной связи. Содержит параметры отправления и сообщение.
 * @version 1.0
 */
namespace site\library\content_samples\Feedback;

use boolive\data\Entity,
    boolive\mail\Mail,
    boolive\values\Check,
    boolive\values\Rule;

class Feedback extends Entity
{
    function send()
    {
        if ($this->check()){
            try{
                return Mail::send(
                    $this->email_to->value(),
                    Check::filter($this->title->value(), Rule::escape()),
                    Check::filter($this->message->value(), Rule::escape()),
                    $this->email_from->value()
                );
            }catch (\Exception $e){
                $this->errors()->fatal = $e;
            }
        }
        return false;
    }
}