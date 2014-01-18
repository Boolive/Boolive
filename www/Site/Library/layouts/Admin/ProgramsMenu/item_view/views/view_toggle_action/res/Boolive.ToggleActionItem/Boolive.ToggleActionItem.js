/**
 * Пункт меню действия в админке
 * По клику отправляет POST запрос виджету на сервер
 *
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.ToggleActionItem", $.boolive.Widget, {

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            self.element.on('click', '.item', function(e){
                e.preventDefault();
                e.stopPropagation();
                var s = self.callParents('getState');
                var object = (_.isArray(s.selected) && s.selected.length==1)? _.first(s.selected) : s.selected;
                self.callServer('toggle',{
                    direct: self.options.object,
                    object: object,
                    select: s.select
                },{
                    success: function(result, textStatus, jqXHR){
                        if (_.isObject(result.out) && !_.isEmpty(result.out.changes)){
                            // Сообщаем родительским виджетам (а корнеь подчиненным) об изменении объекта
                            self.callParents('object_update', [result.out.changes], null, true);
                            // Новый статус команды
                            if (result.out.state){
                                self.element.addClass('checked');
                            }else{
                                self.element.removeClass('checked');
                            }
                        }
                    }
                });
            });
        }
    })
})(jQuery, _);