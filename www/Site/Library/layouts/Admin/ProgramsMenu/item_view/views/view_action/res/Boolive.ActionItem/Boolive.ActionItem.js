/**
 * Пункт меню действия в админке
 * По клику отправляет POST запрос виджету на сервер
 *
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.ActionItem", $.boolive.Widget, {

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            self.element.on('click', 'a', function(e){
                e.preventDefault();
                e.stopPropagation();
                var s = self.callParents('getState');
                var object = (_.isArray(s.selected) && s.selected.length==1)? _.first(s.selected) : s.selected;
//                self.callServer('hide',{
//                    direct: self.options.object,
//                    object: object
//                },{
//                    success: function(result, textStatus, jqXHR){
//                        console.log(result);
//                    }
//                });
                self.callParents('object_update', [{
                    objects: s.selected,
                    updates: {
                        is_hidden: true
                    }
                }]);
            });
        }
    })
})(jQuery, _);