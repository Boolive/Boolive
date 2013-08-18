/**
 * User: pauline
 * To change this template use File | Settings | File Templates.
 */
(function($) {
    $.widget("boolive.PageTextEditor", $.boolive.Widget, {
        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            var a = self.element.find('.textlink');
            a.click(function(e){
                e.preventDefault();
                self.callParents('setState', [{object: self.options.object, view_name:''}]);
            });
        }
    });
})(jQuery);