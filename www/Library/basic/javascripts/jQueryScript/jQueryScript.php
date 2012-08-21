<?php
/**
 * jQuery скрипт
 * JavaScript использующий библиотеку jQuery. Также применяется для создания плагинов для jQuery"
 * @version 1.1
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\basic\javascripts\jQueryScript;

use Library\basic\javascripts\JavaScript\JavaScript;

class jQueryScript extends JavaScript
{
    public function defineRule()
    {
        parent::defineRule();
        $this->_rule->arrays[0]['file']->arrays[0]['name']->ospatterns('jquery.*.js');
    }
}