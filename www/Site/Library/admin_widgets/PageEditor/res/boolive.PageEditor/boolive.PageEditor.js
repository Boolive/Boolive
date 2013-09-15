/**
 * User: pauline
 * Date: 17.04.13
 * Виджет для сохранения изменений в подчиненных виджетах страницы
 */
(function($) {
    $.widget("boolive.PageEditor", $.boolive.Widget, {

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
        },
        /**
         * Выделение объекта
         */
        call_setState: function(caller, state, changes){
            if (caller.direct == 'children'){
                if ($.isPlainObject(changes) && ('selected' in changes)){
                    this.element.find('.main .selected').removeClass('selected');
                    if (state.selected){
                        var element = this.element;
                        _.each(state.selected, function(s){
                            element.find('.main [data-o="'+s+'"]').addClass('selected');
                        });
                    }
                }
            }
        }
    });
  })(jQuery);
