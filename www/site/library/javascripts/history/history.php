<?php
/**
 * HTML5-History-API
 * Библиотека эмулирует HTML5 History API в старых браузерах
 * @version 1.0
 */
namespace site\library\javascripts\history;

use site\library\javascripts\JavaScript\JavaScript;

class history extends JavaScript
{
    function work()
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
                $config[] = 'basepath=/'.trim($basepath,'/').'/';
            }
            $config[] = TIMESTAMP;
            $file.='?'.implode('&',$config);

            $this->_commands->htmlHead('script', array('type'=>'text/javascript', 'src'=>$file, 'text'=>''));
        }
    }
}
