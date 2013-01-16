/**
 * Меню выбора программы (способы отображения текущего объекта)
 * Автоматически обновляется при смене текущего объекта
 * JQuery UI widget
 * Copyright 2012 (C) Boolive
 */
(function($) {
	$.widget("boolive.ProgramsMenu", $.boolive.AjaxWidget, {
        _state: {object: null, select: null, view_name: null},

        _create: function() {
			$.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;
            self.element.on('click', 'li a', function(e){
                e.preventDefault();
                var s = self.before('getState');
                self.before('setState', [{
                    object: s.select,
                    view_name: $(this).attr('href')
                }]);
            });
            this.after_setState(this.before('getState'));
        },

        /**
         * При любом изменении состояния (вход, выделение объекта, выбор программы)
         * загрузка пунктов меню программ и выделение текущего пункта
         * @param state
         */
        after_setState: function(state){
            var self = this;
            if (self._state.select != state.select){
                self._state = $.extend({}, state);
                self.reload('/', {object:state.select}, function(){
                    self.select(state);
                });
            }else{
                self._state = $.extend({}, state);
                self.select(state);
            }
        },

        /**
         * Выделение пункта программы с учётом состояния
         * @param state
         */
        select: function(state){
            // Выделение, если не выделен подчиенный объект
            if (state.object == state.select){
                var sel = null;
                // Если view_name не указан, то выделяется первая закладка
                if (!state.view_name){
                    sel = this.element.find('> ul > li:first-child');
                }else{
                    sel = this.element.find('> ul > li a[href="' + state.view_name+'"]').parent();
                }
                if (sel != this._active_items){
                    if (this._active_items) this._active_items.removeClass('active');
                    this._active_items = sel;
                    this._active_items.addClass('active');
                }
            }else{
                if (this._active_items) this._active_items.removeClass('active');
                this._active_items = null;
            }
        }
	})
})(jQuery);