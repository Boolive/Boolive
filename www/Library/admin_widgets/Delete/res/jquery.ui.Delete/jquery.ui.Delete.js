/**
 * Виджет удаление выбранного объекта
 * Реализует функции подтверждения и отмены удаления.
 * В случаи подтверждения отправляет команду на сервер
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($) {
	$.widget("boolive.Delete", $.boolive.AjaxWidget, {
        // Удаляемый объект
        object: null,

        _create: function() {
			$.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;
            // uri объекта
            self.object = this.element.attr('data-o');

            self.element.find('.submit').click(function(e){
                e.preventDefault();
                self._call('delete', {object: self.object}, function(result, textStatus, jqXHR){
                    // Вход в родительский объект
                    self.callParents('setState', [{object: self.getParentOfUri(self.object), view_name: null}]);
                });
            });
            self.element.find('.cancel').click(function(e){
                e.preventDefault();
                history.back();
            });
        },

        /**
         * Возвращает URI родительского объекта
         * @param uri
         * @return {*}
         */
        getParentOfUri: function(uri){
            var m = uri.match(/^(.*)\/[^\\\/]*$/);
            if (m){
                return m[1];
            }else{
                return '';
            }
        }
	})
})(jQuery);
