/**
 * Виджет объекта меню фильтра
 * формирует меню фильтра и запоминает состояние
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($) {
    $.widget("boolive.ExplorerFilter", $.boolive.AjaxWidget, {
        filter: null,
        filter_changed: false,

        _create: function() {
            $.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;
            self.filter = {};
            var options = self.element.find('.dropdown-menu > li > a');
            for(i=0; i<options.length; i++){
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
                    self.callParents('changeFilter', [self.filter]);
                }
            });
        }
    });
   })(jQuery);