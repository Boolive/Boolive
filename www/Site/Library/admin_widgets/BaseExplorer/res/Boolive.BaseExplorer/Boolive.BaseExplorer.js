/**
 * Заготовка jQueryUI виджета с наследованием boolive.Widget
 * Переименуйте WidgetName на своё имя виджета.
 *
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.BaseExplorer", $.boolive.Widget, {

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            this.init_sortable();
            this.call_setState({target: this, direct: 'children'}, this.callParents('getState'), {selected: true});
        },

        init_sortable: function(){
            var self = this;
            this.element.find('.BaseExplorer__list:first').sortable({
                distance: 15,
                handle: '.Item__select',
                //forceHelperSize: false,
                update: function(event, ui) {
                    var object_uri = {};
                    var next_uri = {};
                    var next;
                    object_uri['uri'] = ui.item.attr('data-o');
                    if(ui.item.next().length > 0){
                        next = ui.item.next();
                        next_uri['uri'] = next.attr('data-o');
                        next_uri['next'] = 1;
                    }else{
                        next  = ui.item.prev();
                        next_uri['uri'] = next.attr('data-o');
                        next_uri['next'] = 0;
                    }
                    self.callServer('saveOrder', {
                        saveOrder:{object_uri:object_uri, next_uri:next_uri},
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
                    this.element.find('.Item_selected').removeClass('Item_selected');
                    if (state.selected){
                        var element = this.element;
                        _.each(state.selected, function(s){
                            element.find('.Item[data-o="'+s+'"]').addClass('Item_selected');
                            // @todo Если объект не найден, то загрузить его с сервера. Сервер должен сообщеить после какого объекта тот отображается
                        });
                    }
                }
            }
        },

        call_object_update: function(caller, info){
            var self = this;
            if (!_.isUndefined(info[this.options.object])){
                //console.log(info[this.options.object]);
            }else{
                var uri;
                var reload = false;
                for (uri in info){
                    reload = reload || (this.element.find('[data-o="'+uri+'"]').size()==0);
                }
                if (reload){
                    this.reload({object: this.options.object}, {url:'/', success: function(){
                        self.init_sortable();
                    }});
                }
            }
        },

        call_changeFilter: function(caller, filter){
            var self = this;
            this.reload({object: this.options.object}, {
                    success: function(){
                        self.afterReload();
                    },
                    url: '/'
                }
            );
        },

        afterReload: function(){
            this.init_sortable();
        }
    })
})(jQuery, _);