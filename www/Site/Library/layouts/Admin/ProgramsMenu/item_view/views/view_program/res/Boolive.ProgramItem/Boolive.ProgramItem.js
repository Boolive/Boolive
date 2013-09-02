/**
 * Пункт меню программы в админке
 * По клику меняет текущее представление (программу) в админке
 *
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.ProgramItem", $.boolive.Widget, {

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            self.element.on('click', 'a', function(e){
                e.preventDefault();
                var s = self.callParents('getState');
                self.callParents('setState', [{
                    object:  (_.isArray(s.selected) && s.selected.length==1)? _.first(s.selected) : s.selected,
                    view_name: $(this).attr('data-program')
                }]);
            });
        }
    })
})(jQuery, _);