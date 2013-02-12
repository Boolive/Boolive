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
                self.callParents('setState', [{selected:  self._object}]);
                // Теперь входим
                self.callParents('setState', [{object:  self._object}]);
			});
            // Множественное выделение объекта
            self.element.find('.select').click(function(e){
                e.stopPropagation();
                e.preventDefault();
                self.callParents('setState', [{selected: self._object, select_type: 'toggle'}]);
            });
            // Выделение объекта
			self.element.click(function(e){
                e.stopPropagation();
                e.preventDefault();

                var s = self.callParents('getState');
                if (_.indexOf(s.selected, self._object)!=-1){
                    // Отмена выделения при повторном клике
                    self.callParents('setState', [{selected: s.object}]);
                }else{
                    self.callParents('setState', [{selected: self._object}]);
                }
			});
        }
	});
})(jQuery);