<?php
/**
 * JavaScript
 * Клиентский скрипт на языке JavaScript
 * @version 1.1
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\javascripts\JavaScript;

use Library\views\View\View;

class JavaScript extends View
{
    public function defineRule()
    {
        parent::defineRule();
        $this->_rule->arrays[0]['file']->arrays[0]['name']->ospatterns('*.js');
    }

    public function work()
    {
        // Исполнение зависимых объектов
        $this->depends->linked()->start($this->_commands, $this->_input);
        // Подключение скрипта прототипа - наследование скриптов
//        if (($proto = $this->proto()) && ($proto instanceof self)){
//            $proto->start($this->_commands, $this->_input);
//        }
        // Подключение javascript файла
        if ($file = $this->file()){
            $this->_commands->addHtml('script', array('type'=>'text/javascript', 'src'=>$file));
        }
    }
}