/**
 * Заготовка jQueryUI виджета с наследованием boolive.Widget
 * Переименуйте WidgetName на своё имя виджета.
 *
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.WidgetName", $.boolive.Widget, {

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);

        }
    })
})(jQuery, _);