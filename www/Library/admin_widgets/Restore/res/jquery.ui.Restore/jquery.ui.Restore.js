/**
 * Виджет восстановления выбранного объекта
 * Реализует функции подтверждения и отмены восстановления.
 * В случаи подтверждения отправляет команду на сервер
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _) {
    $.widget("boolive.Restore", $.boolive.Widget, {
        // Восстанавливаемый объект
        objects: null,

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            // uri объекта
            self.options.object = $.parseJSON(this.element.attr('data-o'));
            self.element.find('.submit').click(function(e){
                e.preventDefault();
                self.callServer('restore', {object: self.options.object}, function(result, textStatus, jqXHR){
                    self.callParents('refreshState');
                    // Вход в родительский объект
                    history.back();
                });
            });
            self.element.find('.cancel').click(function(e){
                e.preventDefault();
                history.back();
            });
        }

    })
})(jQuery, _);