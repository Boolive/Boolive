/**
 * Query UI boolive.Widget
 * Логика виджета на стороне клиента
 */
(function($, _, undefined) {
    $.widget("boolive.ItemPanel", $.boolive.Widget, {

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            self.element.on('click', '.item', function(e){
                e.preventDefault();
                self.callParents('openPanel', ['Новая панель!!']);
//                var s = self.callParents('getState');
//                self.callParents('setState', [{
//                    object:  (_.isArray(s.selected) && s.selected.length==1)? _.first(s.selected) : s.selected,
//                    view_name: $(this).attr('data-program')
//                }]);
            });
        }
    })
})(jQuery, _);