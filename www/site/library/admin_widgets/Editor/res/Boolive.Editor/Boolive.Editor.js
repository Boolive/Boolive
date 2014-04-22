/**
 * Заготовка jQueryUI виджета с наследованием boolive.Widget
 * Переименуйте WidgetName на своё имя виджета.
 *
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.Editor", $.boolive.BaseExplorer, {

        _create: function() {
            $.boolive.BaseExplorer.prototype._create.call(this);
        }
    })
})(jQuery, _);