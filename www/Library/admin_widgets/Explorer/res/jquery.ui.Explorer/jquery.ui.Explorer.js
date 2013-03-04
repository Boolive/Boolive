/**
 * Виджет обозревателя объектов
 * Визуализирует выделение объектов
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _) {
    $.widget("boolive.Explorer", $.boolive.AjaxWidget, {

        _create: function(){
            var self = this;
            $.boolive.AjaxWidget.prototype._create.call(this);
            this.init_sortable();
            this.call_setState({target: this, direct: 'children'}, this.callParents('getState'), {selected: true});
        },

        init_sortable: function(){
            var self = this;
            this.element.find('.content:first').sortable({
                update: function(event, ui) {
                    var object_uri = {};
                    var next_uri = {}
                    object_uri['uri'] = ui.item.attr('data-o');
                    if(ui.item.next().length>0){
                        var next = ui.item.next();
                        next_uri['uri'] = next.attr('data-o');
                        next_uri['next'] =1;
                    }else{
                        var next  = ui.item.prev();
                        next_uri['uri'] = next.attr('data-o');
                        next_uri['next'] =0;
                    }
                    self.callServer('saveOrder', {
                        saveOrder:{objectUri:object_uri, nextUri:next_uri},
                        object: self.options.object
                    });
                }
            });
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
            var self = this;
            this.reload({object: this.options.object, filter: filter}, function(){
                self.init_sortable();
            });
        },

        call_changeViewKind: function(caller, kind_name){
            var self = this;
            this.reload({object: this.options.object, view_kind: kind_name}, function(){
                self.init_sortable();
            });
        }
    })
})(jQuery, _);