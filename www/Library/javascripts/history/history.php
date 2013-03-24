<?php
/**
 * HTML5-History-API
 * Библиотека эмулирует HTML5 History API в старых браузерах
 * @version 1.0
 */
namespace Library\JavaScripts\history;

use Library\JavaScripts\JavaScript\JavaScript;

class history extends JavaScript
{
    public function work()
    {
        // Исполнение зависимых объектов
        $this->depends->start($this->_commands, $this->_input);
        // Подключение javascript файла
        if ($file = $this->file()){
            $config = array();
            if ($type = $this->type->value()){
                $config[] = 'type='.$type;
            }
            if ($redirect = $this->redirect->value()){
                $config[] = 'redirect=true';
            }
            if ($basepath = $this->basepath->value()){
                $config[] = 'basepath='.$basepath;
            }
            if ($config) $file.='?'.implode('&',$config);

            $this->_commands->addHtml('script', array('type'=>'text/javascript', 'src'=>$file));
        }
    }
}
