/**
 * Виджет экспорта выбранного объекта
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _) {
    $.widget("boolive.Export", $.boolive.AjaxWidget, {
        // Удаляемый объект
        objects: null,

        _create: function() {
            $.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;
            // uri объекта
            self.options.object = $.parseJSON(this.element.attr('data-o'));

            self.element.find('.confirm .submit').click(function(e){
                e.preventDefault();
                self.element.find('.confirm').hide();
                self.element.find('.progress').show();
//                self.callServer('start', {object: self.options.object}, function(result, textStatus, jqXHR){
//                    history.back();
//                });
            });
            self.element.find('.confirm .cancel').click(function(e){
                e.preventDefault();
                history.back();
            });
            self.element.find('.progress .cancel').click(function(e){
                e.preventDefault();
                history.back();
            });
        }
    })
})(jQuery, _);
