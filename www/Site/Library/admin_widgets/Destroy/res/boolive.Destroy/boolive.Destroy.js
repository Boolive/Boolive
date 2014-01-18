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
            self.element.find('.submit').click(function(e){
                e.preventDefault();
                self.callServer('destroy', {
                    object: self.options.object,
                    select: self.callParents('getState').select
                }, function(result, textStatus, jqXHR){
                    //@todo Обработать ошибки (контроль доступа и целостности)
                    if (result.out.error){
                        alert(result.out.error);
                    }else{
                        history.back();
                        // Отмена выделения удаленных объектов
                        $(window).on('after_popsate'+self.eventNamespace, function(){
                            $(window).off('after_popsate'+self.eventNamespace);
                            self.callParents('setState', [{selected:null}]);
                        });
                    }
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