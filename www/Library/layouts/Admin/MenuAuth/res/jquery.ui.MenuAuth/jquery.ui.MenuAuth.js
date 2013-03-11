/**
 * Виджет меню авторизации
 * User: pauline
 */
(function($) {
    $.widget("boolive.MenuAuth", $.boolive.AjaxWidget, {

        _create: function() {
            $.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;
            self.element.find('.userlink').click(function(e){
                e.preventDefault();
                self.callParents('setState', [{
                    object: self.element.find('.userlink').attr('data-o')
                }]);
            });
        },
        /**
        * При входе в пользователя - обновление стиля для его кнопки
        * @param caller Информация, кто вызывал {target, direct}
        * @param state Объект текущего состояния.
        * @param changes Что изменилось в объекте состояния
        */
        call_setState: function(caller, state, changes){
            if ($.isPlainObject(changes) && ('object' in changes)){
                var uri = state.object;
                var link = this.element.find('.userlink');
                if(uri == link.attr('data-o')) {
                    link.parent().addClass('active');
                }else{
                    link.parent().removeClass('active');
                }
            }
        }

    });
})(jQuery);