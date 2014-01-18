/**
 * Query UI boolive.Widget
 * Логика виджета на стороне клиента
 */
(function($, _, undefined) {
    $.widget("boolive.MenuSelections", $.boolive.Widget, {

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            this.element.on('click', '.MenuSelections__item', function(e){
                e.preventDefault();
                var state = self.callParents('getState');
                self.callParents('setState', [{
                    object: state.selected.length === 1 ? _.first(state.selected) : state.object,
                    select: $(this).attr('data-name')
                }]);
                e.stopPropagation();
            });
            this.call_setState({direct:'children'}, this.callParents('getState'), {select:true});
        },

        call_setState: function(caller, state, change){ //after
            if (caller.direct == 'children'){
                var self = this;
                if ('select' in change){
                    self.element.find('.MenuSelections__item_active').removeClass('MenuSelections__item_active');
                    self.element.find('.MenuSelections__item[data-name="'+state.select+'"]').addClass('MenuSelections__item_active');
                }
            }
        }
    })
})(jQuery, _);