<?php
/**
 * HTML5-History-API
 * Библиотека эмулирует HTML5 History API в старых браузерах
 * @version 1.0
 */
namespace Library\javascript_plugins\HistoryAPI;

use Library\views\JavaScript\JavaScript;

class HistoryAPI extends JavaScript
{
    public function work()
    {
        // Исполнение зависимых объектов
        $this->depends->start($this->_commands, $this->_input);
        // Подключение javascript файла
        if ($file = $this->getFile()){
            $config = array();
            if ($type = $this->type->getValue()){
                $config[] = 'type='.$type;
            }
            if ($redirect = $this->redirect->getValue()){
                $config[] = 'redirect=true';
            }
            if ($basepath = $this->basepath->getValue()){
                $config[] = 'basepath='.$basepath;
            }
            if ($config) $file.='?'.implode('&',$config);

            $this->_commands->addHtml('script', array('type'=>'text/javascript', 'src'=>$file));
        }
    }
}
