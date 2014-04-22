/**
 * Query UI boolive.Widget
 * Логика виджета на стороне клиента
 */
(function($, _, undefined) {
    $.widget("boolive.PageNavigation", $.boolive.Widget, {

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            this.element.find('.PageNavigation__page').click(function(e){
                e.preventDefault();
                self.callParents('setState',{page:$(this).attr('data-page')});
            });
        }
    })
})(jQuery, _);