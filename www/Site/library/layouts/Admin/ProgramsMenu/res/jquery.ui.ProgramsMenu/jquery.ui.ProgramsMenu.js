/**
 * Меню выбора программы (способы отображения текущего объекта)
 * Автоматически обновляется при смене текущего объекта
 * JQuery UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _) {
	$.widget("boolive.ProgramsMenu", $.boolive.Widget, {
        // _state: {object: null, selected: [], view_name: null},
        _active_items: null,

        _create: function() {
			$.boolive.Widget.prototype._create.call(this);
            var self = this;

            self.reload = _.throttle(self.reload, 500);
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
            if (caller.direct == 'children'){
                var self = this;
                if ('selected' in change || 'select' in change){
                    var obj = (state.selected.length == 1)? _.first(state.selected) : state.selected;
                    var have_selected = obj !== state.object;
                    self.reload({object:obj, have_selection:have_selected, select: state.select}, function(){
                        self._select(state);
                    });
                }else
                if ('view_name' in change){
                    self._select(state);
                }
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
                    sel = this.element.find('> ul > li a[data-program^="views/"]:first').parent();
                    if (sel.hasClass('group')){
                        sel = sel.find('li:first-child');
                    }
                }else{
                    sel = this.element.find('> ul > li a[data-program="' + state.view_name+'"]').parent();
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