/**
 * Заготовка jQueryUI виджета с наследованием boolive.Widget
 * Переименуйте WidgetName на своё имя виджета.
 *
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.SelectObjectItem", $.boolive.Widget, {
        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            this.element.on('click', '.view_all', function(e){
                e.preventDefault();
                e.stopPropagation();
                var s = self.callParents('getState');
                var object = (_.isArray(s.selected) && s.selected.length==1)? _.first(s.selected) : s.selected;
                self.callParents('openWindow', [null,
                    {
                        url: "/",
                        data: {
                            direct: self.options.object, // uri выиджета выбора объекта
                            object: '' //какой объект показать
                        }
                    },
                    function(result, params){
                        if (result == 'submit' && 'selected' in params){
                            self.selected(params.selected, object);
                        }
                    }
                ]);
            });
            this.element.on('click', '.object', function(e){
                e.preventDefault();
                e.stopPropagation();
                var s = self.callParents('getState');
                var object = (_.isArray(s.selected) && s.selected.length==1)? _.first(s.selected) : s.selected;
                self.selected($(this).attr('href'), object);
            });
        },

        selected: function(selected, object){
            var self = this;
            self.callServer('selected',{
                direct: self.options.object,
                object: object,
                selected: selected
            },{
                success: function(result, textStatus, jqXHR){
                    if (_.isObject(result.out) && !_.isEmpty(result.out.changes)){
                        //console.log(result);
                        self.callParents('object_update', [result.out.changes]);
                    }
                }
            });
        }
    })
})(jQuery, _);