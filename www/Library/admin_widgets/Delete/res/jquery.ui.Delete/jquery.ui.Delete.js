/**
 * Виджет удаление выбранного объекта
 * Реализует функции подтверждения и отмены удаления.
 * В случаи подтверждения отправляет команду на сервер
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _) {
    $.widget("boolive.Delete", $.boolive.AjaxWidget, {
        // Удаляемый объект
        objects: null,
//        prev_o: '',
//        prev_v: '',

        _create: function() {
            $.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;
            // uri объекта
            self.objects = $.parseJSON(this.element.attr('data-o'));
            //self.prev_o = this.element.attr('data-prev-o');
            //self.prev_v = this.element.attr('data-prev-v');

            self.element.find('.submit').click(function(e){
                e.preventDefault();
                self.callServer('delete', {object: self.options.object}, function(result, textStatus, jqXHR){
                    // Вход в родительский объект
                    history.back();
                    //self.callParents('setState', [{object: self.prev_o, selected: null, view_name: self.prev_v}]);
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
