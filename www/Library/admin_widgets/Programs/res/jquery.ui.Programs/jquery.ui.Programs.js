/**
 * Виджет программ
 * При смене программы, перегружает своё содержимое.
 * На стороне сервера учитывается выбранная программа и возвращается её HTML
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($) {
    $.widget("boolive.Programs", $.boolive.AjaxWidget, {
        /**
         * Вход в объект или смена программы - перегрузка программы (возможна смена)
         * @param state
         * @param changes
         */
        call_setState: function(caller, state, changes){ //after

            var self = this;
            if ($.isPlainObject(changes) && ('object' in changes || 'view_name' in changes)){
                this.reload(state, {
                    empty: function(){
                        self.callChildren('program_hide');
                    },
                    success: function(){
                        self.callChildren('program_show');
                    }
                });
            }
        }
    })
})(jQuery);