/**
 * Виджет программ
 * При смене программы, перегружает своё содержимое.
 * На стороне сервера учитывается выбранная программа и возвращается её HTML
 * Отображение кэшируется
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.Programs", $.boolive.Widget, {

        _create: function(){
            $.boolive.Widget.prototype._create.call(this);
        },

        /**
         * Вход в объект или смена программы - перегрузка программы (возможна смена)
         */
        call_setState: function(caller, state, changes){
            var self = this;
            if (caller.direct == 'children' && _.isObject(changes) && ('object' in changes || 'view_name' in changes)){
                this.reload({
                    object: state.object,
                    view_name: state.view_name,
                    selected: state.selected
                }, {
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
})(jQuery, _);