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
            self._object = this.element.attr('data-o');
            // Вход в объекта
			self.element.find('.title').click(function(e){
                e.stopPropagation();
                e.preventDefault();
                // Сначала выделяем себя
                self.callParents('setState', [{select:  self._object}]);
                // Теперь входим
                self.callParents('setState', [{object:  self._object}]);
			});
            // Вход в объекта
			self.element.click(function(e){
                e.stopPropagation();
                e.preventDefault();
                // Сначала выделяем себя
                var s = self.callParents('getState');
                if (s.select == self._object){
                    self.callParents('setState', [{select: s.object}]);
                }else{
                    self.callParents('setState', [{select: self._object}]);
                }
			});
        }
	});
})(jQuery);