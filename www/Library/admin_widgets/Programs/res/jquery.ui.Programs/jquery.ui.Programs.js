/**
 * Виджет программ
 * При смене программы, перегружает своё содержимое.
 * На стороне сервера учитывается выбранная программа и возвращается её HTML
 * Отображение кэшируется
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.Programs", $.boolive.AjaxWidget, {

//        _history: undefined,
//        _key: undefined,

        _create: function(){
            $.boolive.AjaxWidget.prototype._create.call(this);
//            this._key = this._makeKey(this.callParents('getState'));
//            this._history = {};
        },

//        _makeKey: function(state){
//            return (_.isArray(state.object)? state.object.join(';') : state.object) + ' - ' + state.view_name;
//        },

        /**
         * Вход в объект или смена программы - перегрузка программы (возможна смена)
         */
        call_setState: function(caller, state, changes){
            var self = this;
            if (caller.direct == 'children' && _.isObject(changes) && ('object' in changes || 'view_name' in changes)){
//                var key = this._makeKey(state);
//                // Запоминание текущего содержимого
//                this._history[this._key]= {
//                    dom: this.element.children().detach(),
//                    children: this._children
//                };
//                // Установка нового содержимого
//                if (key in this._history){
//                    this.callChildren('program_hide');
//                    this.element.append(this._history[key].dom);
//                    this._children = this._history[key].children;
//                    this.callChildren('program_show');
//                }else{

                    this.reload(state, {
                        empty: function(){
                            self.callChildren('program_hide');
                        },
                        success: function(){
                            self.callChildren('program_show');
                        }
                    });
//                }
//                this._key = key;
            }
        }
    })
})(jQuery, _);