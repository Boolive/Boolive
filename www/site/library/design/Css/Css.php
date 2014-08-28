<?php
/**
 * CSS
 * Каскадная таблица стилей для оформления HTML-документа
 * @version 1.1
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace site\library\design\Css;

use site\library\views\View\View;

class Css extends View
{
    protected function rule()
    {
        $rule = parent::rule();
        // Ассоциация с файлами с расширением css
        $rule->arrays[0]['file']->arrays[0]['name']->ospatterns('*.css');
        return $rule;
    }

    function work()
    {
        // Если прототип тоже CSS, то исполняем его, чтобы подключился его файл стиля.
        // Таким образом реализуется наследование файлов стилей
//        if (($proto = $this->proto()) && ($proto instanceof self) && ($proto != $this)){
//            $proto->start($this->_commands, $this->_input);
//        }
        // Подключение CSS файла
        if ($file = $this->file()){
            $this->_commands->htmlHead('link', array('rel'=>"stylesheet", 'type'=>"text/css", 'href'=>$file));
        }
    }

    function birth($for = null, $draft = true)
    {
        $obj = parent::birth($for, $draft);
        $obj->name(mb_strtolower($obj->name()), true);
        return $obj;
    }
}