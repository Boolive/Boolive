/**
 * Заготовка jQueryUI виджета с наследованием boolive.Widget
 * Переименуйте WidgetName на своё имя виджета.
 *
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.AddTool", $.boolive.Widget, {
        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            this.element.on('click', '.more', function(e){
                e.preventDefault();
                e.stopPropagation();
                var is_link = $(this).hasClass('link');
                self.callParents('openWindow', [null,
                    {
                        url: "/",
                        data: {
                            direct: self.options.object, // uri выиджета выбора объекта
                            object: '/Library' //какой объект показать
                        }
                    },
                    function(result, params){
                        if (result == 'submit' && 'selected' in params){
                            self.selected(params.selected, self.callParents('getState').object, is_link);
                        }
                    }
                ]);
            });
            this.element.on('click', '.object', function(e){
                e.preventDefault();
                e.stopPropagation();
                self.selected($(this).attr('href'), self.callParents('getState').object);
            });
        },

        selected: function(selected, object, is_link){
            var self = this;
            self.callServer('selected',{
                direct: self.options.object, // Запрос к программе добавления, а не к AddTool
                object: object, // куда добавлять
                selected: selected, // что добавлять
                is_link: is_link
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