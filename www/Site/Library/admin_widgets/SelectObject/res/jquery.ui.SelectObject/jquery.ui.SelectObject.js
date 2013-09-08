/**
 * Виджет выбора объекта
 * Используется как окно независимое в админки
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, window) {
    $.widget("boolive.SelectObject", $.boolive.Widget, {
        // Выделенный объект
        _select: null,

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            // Текущий выделенный объект узнаётся у родительского виджета (у админки)
            this._select = this.callParents('getState').selected;
            // Подтверждение выбора
            self.element.on('click', '.submit', function(e){
                e.preventDefault();
                // Вызов команды закрытия текущего окна (себя) с возвратом результата
                self.callParents('closeWindow', ['submit', {selected: self._select}]);
            });
            // Отмена выбора
            self.element.on('click', '.cancel', function(e){
                e.preventDefault();
                self.callParents('closeWindow', ['cancel']);
            });

            $(document).on('load-html' + this.eventNamespace, function() {
                self.fixed_buttons();
            });
            $(document).on('resize' + this.eventNamespace, function() {
                self.fixed_buttons();
            });
            self.fixed_buttons();
        },
        /**
         * При выделении объекта фиксируем его URI во внутреннем свойстве
         * @param state
         * @param changes
         */
        call_setState: function(caller, state, changes){ //after
            if (caller.direct == 'children'){
                this._select = state.selected.slice(0);
            }
        },

        fixed_buttons: function(){
            var wh = $(window).height();
            var sh = this.element.outerHeight(true);
            var el = this.element.find('> .buttons');
            if ( wh < sh) {
                el.css({
                    position: 'fixed',
                    bottom: 0
                });
            }else{
                el.css({
                    position: 'relative',
                    bottom: 'auto'
                });
            }
            el.width(this.element.innerWidth());
        }
    })
})(jQuery, window);