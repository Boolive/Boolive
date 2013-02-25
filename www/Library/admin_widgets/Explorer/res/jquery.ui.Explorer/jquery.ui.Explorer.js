/**
 * Виджет обозревателя объектов
 * Визуализирует выделение объектов
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _) {
    $.widget("boolive.Explorer", $.boolive.AjaxWidget, {

        _create: function(){
            $.boolive.AjaxWidget.prototype._create.call(this);
            this.call_setState({target: this, direct: 'children'}, this.callParents('getState'), {selected: true});
        },
        /**
         * Выделение объекта
         */
        call_setState: function(caller, state, changes){

            if (caller.direct == 'children'){
                if ($.isPlainObject(changes) && ('selected' in changes)){
                    this.element.find('.content .selected').removeClass('selected');
                    if (state.selected){
                        var element = this.element;
                        _.each(state.selected, function(s){
                            element.find('.content [data-o="'+s+'"]').addClass('selected');
                        });
                    }
                }
            }
        },

        call_changeFilter: function(caller, filter){
            console.log(this.options.object);
            this.reload({object: this.options.object, filter: filter});
        }
    })
})(jQuery, _);