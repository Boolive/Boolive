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
            this.reload({object: this.options.object, filter: filter});
        },

        call_changeViewKind: function(caller, kind_name){
            this.reload({object: this.options.object, view_kind: kind_name});
        }
    })
})(jQuery, _);