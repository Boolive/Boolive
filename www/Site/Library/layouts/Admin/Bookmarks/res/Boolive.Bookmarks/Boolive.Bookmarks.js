/**
 * Заготовка jQueryUI виджета с наследованием boolive.Widget
 * Переименуйте WidgetName на своё имя виджета.
 *
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.Bookmarks", $.boolive.Widget, {

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            // Нажатие по пункту меню
			self.element.on('click', 'a', function(e){
                e.preventDefault();
                e.stopPropagation();
                // Вход в объект
                self.callParents('setState', [{
                    object: $(this).attr('data-o')
                }]);
			});
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
                if ('object' in change){
                    self.element.find('.active').removeClass('active');
                    self.element.find('[data-o="'+state.object+'"]').parent().addClass('active');
                }
            }
        }
    })
})(jQuery, _);