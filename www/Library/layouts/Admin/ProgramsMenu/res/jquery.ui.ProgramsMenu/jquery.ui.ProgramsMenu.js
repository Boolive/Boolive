/**
 * Меню выбора программы (способы отображения текущего объекта)
 * Автоматически обновляется при смене текущего объекта
 * JQuery UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _) {
	$.widget("boolive.ProgramsMenu", $.boolive.AjaxWidget, {
        // _state: {object: null, selected: [], view_name: null},
        _active_items: null,

        _create: function() {
			$.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;
            self.element.on('click', 'li a', function(e){
                e.preventDefault();
                var s = self.callParents('getState');
                self.callParents('setState', [{
                    object:  (_.isArray(s.selected) && s.selected.length==1)? _.first(s.selected) : s.selected,
                    view_name: $(this).attr('href')
                }]);
            });
            this.call_setState({target: this, direct: 'children'}, this.callParents('getState'), {view_name: true});
        },

        /**
         * При любом изменении состояния (вход, выделение объекта, выбор программы)
         * загрузка пунктов меню программ и выделение текущего пункта
         * @param caller
         * @param state
         * @param change Какие изменения в state
         */
        call_setState: function(caller, state, change){ //after
            var self = this;
            if ('selected' in change){
                var obj = (state.selected.length == 1)? _.first(state.selected) : state.selected;
                self.reload('/', {object:obj}, function(){
                    self._select(state);
                });
            }else
            if ('view_name' in change){
                self._select(state);
            }
        },

        /**
         * Выделение пункта программы с учётом состояния
         * @param state
         */
        _select: function(state){
            // Выделение программы, если выделен родительский объект
            if (_.indexOf(state.selected, state.object)!=-1){
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
})(jQuery, _);