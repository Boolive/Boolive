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
            // Вход в объекта
			self.element.find('.enter').click(function(e){
                e.stopPropagation();
                e.preventDefault();
                // Сначала выделяем себя
                var s = self.before('getState');
                if (s.select == self._object){
                    self.before('setState', [{select: s.object}]);
                }else{
                    self.before('setState', [{select: self._object}]);
                }
			});
        }
	});
})(jQuery);