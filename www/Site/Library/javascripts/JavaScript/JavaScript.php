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
    protected function rule()
    {
        $rule = parent::rule();
        $rule->arrays[0]['file']->arrays[0]['name']->ospatterns('*.js');
        return $rule;
    }

    function work()
    {
        // Исполнение зависимых объектов
        $this->depends->linked()->start($this->_commands, $this->_input);
        // Подключение скрипта прототипа - наследование скриптов
//        if (($proto = $this->proto()) && ($proto instanceof self)){
//            $proto->start($this->_commands, $this->_input);
//        }
        // Подключение javascript файла
        if ($file = $this->file()){
            $this->_commands->htmlHead('script', array('type'=>'text/javascript', 'src'=>$file.'?'.$this->date(true), 'text'=>''));
        }
    }

    function exportedProperties()
    {
        $names = parent::exportedProperties();
        $names[] = 'depends';
        return $names;
    }
}