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
            this.after_setState(this.before('getState'), {select: true});
        },
        /**
         * Выделение объекта
         * @param state
         * @param changes
         */
        after_setState: function(state, changes){
            if ('select' in changes){
                this.element.find('.selected').removeClass('selected');
                if (state.select){
                    this.element.find('[data-object="'+this.escape(state.select)+'"]').addClass('selected');
                }
            }
        }
	})
})(jQuery);