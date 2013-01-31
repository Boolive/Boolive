/**
 * Виджет обозревателя объектов
 * Визуализирует выделение объектов
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($) {
	$.widget("boolive.Explorer", $.boolive.AjaxWidget, {

        _create: function(){
            $.boolive.AjaxWidget.prototype._create.call(this);
            this.call_setState({target: this, direct: 'children'}, this.callParents('getState'), {select: true});
        },
        /**
         * Выделение объекта
         * @param state
         * @param changes
         */
        call_setState: function(caller, state, changes){ //after
            if ($.isPlainObject(changes) && ('select' in changes)){
                this.element.find('.selected').removeClass('selected');
                if (state.select){
                    this.element.find('[data-o="'+this.escape(state.select)+'"]').addClass('selected');
                }
            }
        }
	})
})(jQuery);