/**
 * Виджет уничтожения выбранного объекта
 * Реализует функции подтверждения и отмены удаления.
 * В случаи подтверждения отправляет команду на сервер
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _) {
    $.widget("boolive.Destroy", $.boolive.Widget, {
        // Удаляемый объект
        objects: null,
//        prev_o: '',
//        prev_v: '',

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            // uri объекта
            self.options.object = $.parseJSON(this.element.attr('data-o'));
//            self.prev_o = this.element.attr('data-prev-o');
//            self.prev_v = this.element.attr('data-prev-v');

            self.element.find('.submit').click(function(e){
                e.preventDefault();
                self.callServer('destroy', {object: self.options.object}, function(result, textStatus, jqXHR){
                    //@todo Обработать ошибки (контроль доступа и целостности)
                    if (result.out.error){
                        alert('Невозможно уничтожить. Нет доступа или объект используется');
                    }else{
                       history.back();
                    }
                    // Вход в родительский объект

//                    self.callParents('setState', [{object: self.prev_o, selected: null, view_name: self.prev_v}]);
                });
            });
            self.element.find('.cancel').click(function(e){
                e.preventDefault();
                history.back();
            });
        }

        /**
         * Возвращает URI родительского объекта
         * @param uri
         * @return {*}
         */
//        getParentOfUri: function(uri){
//            var m = uri.match(/^(.*)\/[^\\\/]*$/);
//            if (m){
//                return m[1];
//            }else{
//                return '';
//            }
//        }
    })
})(jQuery, _);