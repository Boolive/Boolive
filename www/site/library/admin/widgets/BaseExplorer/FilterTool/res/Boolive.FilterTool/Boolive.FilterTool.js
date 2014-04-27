/**
 * Заготовка jQueryUI виджета с наследованием boolive.Widget
 * Переименуйте WidgetName на своё имя виджета.
 *
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.FilterTool", $.boolive.Widget, {
        filter: null,
        filter_changed: false,

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            self.filter = {};
            var options = self.element.find('.dropdown-menu > li > a');
            var l = options.length;
            for(var i=0; i<l; i++){
                if(options.eq(i).parent().hasClass('selected')){
                    self.filter[options.eq(i).attr('data-filter')] = 1
                }else{
                    self.filter[options.eq(i).attr('data-filter')] = 0
                }
            }
            self.element.click(function(e){
                e.stopPropagation();
                e.preventDefault();
            });
            options.on('click', function(e){
                self.filter_changed = true;
                $(this).parent().toggleClass('selected');
                if($(this).parent().hasClass('selected')){
                    self.filter[$(this).attr('data-filter')] = 1;
                }else{
                    self.filter[$(this).attr('data-filter')] = 0;
                }
            });

            self.element.on('mouseleave',function(e){
                if(self.filter_changed){
                    self.callServer('saveFilter', {
                        //direct: self.options.view,
                        filter:self.filter
                    },function(){
                        self.callParents('changeFilter', [self.filter]);
                    });
                }
            });
        }
    })
})(jQuery, _);