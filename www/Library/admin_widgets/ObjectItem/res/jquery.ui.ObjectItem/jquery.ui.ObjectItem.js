/**
 * Виджет объекта в виде элемента списка
 * Инициирует команды входа в отображаемый объект
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($) {
	$.widget("boolive.ObjectItem", $.boolive.AjaxWidget, {
        // uri отображаемого объекта
        _object: '',

        _create: function() {
			$.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;
            // uri объекта
            self._object = this.element.attr('data-object');
            // Вход в объекта
			self.element.click(function(e){
                e.stopPropagation();
                e.preventDefault();
                // Сначала выделяем себя
                self.before('setState', [{select:  self._object}]);
                // Теперь входим
                self.before('setState', [{object:  self._object}]);
			});
        }
	});
})(jQuery);