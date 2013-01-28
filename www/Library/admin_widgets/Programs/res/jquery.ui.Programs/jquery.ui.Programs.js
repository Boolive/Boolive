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
        after_setState: function(state, changes){

            var self = this;
            if ('object' in changes || 'view_name' in changes){
                this.reload('/', state, {
                    empty: function(){
                        self.after('program_hide');
                    },
                    success: function(){
                        self.after('program_show');
                    }
                });
            }
        }
	})
})(jQuery);