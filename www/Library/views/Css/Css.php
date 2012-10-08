<?php
/**
 * CSS
 * Каскадная таблица стилей для оформления HTML-документа
 * @version 1.1
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\views\Css;

use Library\views\View\View;

class Css extends View
{
    public function defineRule()
    {
        parent::defineRule();
        // Ассоциация с файлами с расширением css
        $this->_rule->arrays[0]['file']->arrays[0]['name']->ospatterns('*.css');
    }

    public function work()
    {
        // Если прототип тоже CSS, то исполняем его, чтобы подключился его файл стиля.
        // Таким образом реализуется наследование файлов стилей
        if (($proto = $this->proto()) && ($proto instanceof self)){
            $proto->start($this->_commands, $this->_input);
        }
        // Подключение CSS файла
        if ($file = $this->getFile()){
            $this->_commands->addHtml('link', array('rel'=>"stylesheet", 'type'=>"text/css", 'href'=>$file));
        }
    }
}