<?php
/**
 * jQueryUI Boolive.Widget
 * Логика виджета на стороне клиента (в браузере)
 * @version 1.0
 */
namespace site\library\javascripts\Widget;

use site\library\javascripts\JavaScript\JavaScript;

class Widget extends JavaScript
{
    function fileTemplate()
    {
        $name = $this->name();
        if ($p = $this->proto()){
            if ($def_val = $p->isDefaultValue(null, true)) $p = $def_val;
            $ext = 'boolive.'.$p->name();
        }else{
            $ext = 'Widget';
        }
        return <<<js
/**
 * Query UI boolive.Widget
 * Логика виджета на стороне клиента
 */
(function(\$, _, undefined) {
    $.widget("boolive.$name", \$.$ext, {

        _create: function() {
            \$.$ext.prototype._create.call(this);

        }
    })
})(jQuery, _);
js;
    }

    function work()
    {
        // Подключение скрипта прототипа - наследование скриптов
        if (($proto = $this->proto()) && ($proto instanceof self)){
            $proto->start($this->_commands, $this->_input);
        }
        parent::work();
    }
}