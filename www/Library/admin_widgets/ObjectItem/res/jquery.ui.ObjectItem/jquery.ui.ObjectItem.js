/**
 * Виджет объекта в виде элемента списка
 * Инициирует команды входа в отображаемый объект
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($) {
    $.widget("boolive.ObjectItem", $.boolive.AjaxWidget, {

        _create: function() {
            $.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;

            // Идентификатор объекта к которому будет переход (обычно его URI)
            if (!this.options.link){
                this.options.link = this.element.attr('data-l');
            }
            // Вход в объекта
            self.element.find('.title').click(function(e){
                e.stopPropagation();
                e.preventDefault();
                // Сначала выделяем себя
                self.callParents('setState', [{selected:  self.options.object}]);
                // Теперь входим
                self.callParents('setState', [{object:  self.options.link}]);
            });
            // Вход в объект-ссылку
            self.element.find('.prop').click(function(e){
                e.stopPropagation();
                e.preventDefault();
                // Сначала выделяем себя
                self.callParents('setState', [{selected:  self.options.object}]);
                // Теперь входим
                self.callParents('setState', [{object:  self.options.object}]);
            });
            // Множественное выделение объекта
            self.element.find('.select').click(function(e){
                e.stopPropagation();
                e.preventDefault();
                self.callParents('setState', [{selected: self.options.object, select_type: 'toggle'}]);
            });
            // Выделение объекта
            self.element.click(function(e){
                e.stopPropagation();
                e.preventDefault();

                var s = self.callParents('getState');
                if (_.indexOf(s.selected, self.options.object)!=-1){
                    // Отмена выделения при повторном клике
                    self.callParents('setState', [{selected: s.object}]);
                }else{
                    self.callParents('setState', [{selected: self.options.object}]);
                }
            });
        }
    });
})(jQuery);