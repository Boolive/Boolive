<?php
/**
 * jQuery UI скрипт
 * JavaScript использующий библиотеку jQueryUI и jQuery. Также применяется для создания плагинов для jQueryUI
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\views\jQueryUIScript;

use Library\views\jQueryScript\jQueryScript;

class jQueryUIScript extends jQueryScript
{
    public function defineRule()
    {
        parent::defineRule();
        $this->_rule->arrays[0]['file']->arrays[0]['name']->ospatterns('jquery.ui.*.js');
    }
}