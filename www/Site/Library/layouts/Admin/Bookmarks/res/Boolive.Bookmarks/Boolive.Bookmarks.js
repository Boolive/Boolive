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
            self.element.on('click', '.Bookmarks__add', function(e){
                e.preventDefault();
                var proto = self.callParents('getState').object;
                var url = /^[a-z]+:\/\//i.test(self.options.object) ? self.options.object : window.location.protocol + '//' + window.location.host + self.options.object;
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    accepts: {json: 'application/json'},
                    url: url,
                    data: {
                        method: 'POST',
                        entity: {
                            proto: proto,
                            parent: self.options.object,
                            is_link: true,
                            is_draft: false
                        }
                    },
                    success: function(result, textStatus, jqXHR){
                        self.reload({
                            object: proto
                        }, function(){
//                            alert('add!!');
                        });
                    },
                    error: function(jqXHR, textStatus){
                        console.log(jqXHR);
                    }
                });
            });
            self.element.on('click', '.Bookmarks__item-remove', function(e){
                e.preventDefault();
                e.stopPropagation();
                var obj = self.callParents('getState').object;
                var url = /^[a-z]+:\/\//i.test(self.options.object) ? self.options.object : window.location.protocol + '//' + window.location.host + $(this).attr('data-o');
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    accepts: {json: 'application/json'},
                    url: url,
                    data: {
                        method: 'DELETE'
                    },
                    error: function(jqXHR, textStatus){
                        if (jqXHR.status == 204){
                            self.reload({object: obj});
                        }
                    }
                });
            });
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