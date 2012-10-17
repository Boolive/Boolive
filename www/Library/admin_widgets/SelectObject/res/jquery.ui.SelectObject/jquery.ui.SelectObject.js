/**
 * Виджет выбора объекта
 * Используется как окно независимое в админки
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($) {
	$.widget("boolive.SelectObject", $.boolive.AjaxWidget, {
        // Выделенный объект
        _select: null,

        _create: function() {
			$.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;
            // Текущий выделенный объект узнаётся у родительского виджета (у админки)
            this._select = this.before('getState').select;
            // Подтверждение выбора
            self.element.on('click', '.submit', function(e){
                e.preventDefault();
                // Вызов команды закрытия текущего окна (себя) с возвратом результата
                self.before('closeWindow', ['submit', {select: self._select}]);
            });
            // Отмена выбора
            self.element.on('click', '.cancel', function(e){
                e.preventDefault();
                self.before('closeWindow', ['cancel']);
            });
        },
        /**
         * При выделении объекта фиксируем его URI во внутреннем свойстве
         * @param state
         * @param changes
         */
        after_setState: function(state, changes){
            this._select = state.select;
        }
	})
})(jQuery);