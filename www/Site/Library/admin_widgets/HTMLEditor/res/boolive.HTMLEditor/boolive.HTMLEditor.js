/**
 * Заготовка jQueryUI виджета с наследованием boolive.Widget
 * Переименуйте WidgetName на своё имя виджета.
 *
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.HTMLEditor", $.boolive.Widget, {

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            this.element.find('.save').click(function(){
                var url = /^[a-z]+:\/\//i.test(self.options.object) ? self.options.object : window.location.protocol + '//' + window.location.host + self.options.object;
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    accepts: {json: 'application/json'},
                    url: url,
                    data: {
                        method: 'PUT',
                        entity:{
                            value: self.element.find('textarea').val()
                        }
                    },
                    success: function(result, textStatus, jqXHR){
                        alert('Сохранено!')
                    }
                });
            })
        }
    })
})(jQuery, _);