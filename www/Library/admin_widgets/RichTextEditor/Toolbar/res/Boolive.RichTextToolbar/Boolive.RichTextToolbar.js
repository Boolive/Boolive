/**
 * Панель настройки стиля
 * Query UI widget
 * Copyright 2013 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.RichTextToolbar", $.boolive.Widget, {

        bars: null,
        tabs: null,

        _styles: null,

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;

            this.element.on('click', function(e){
                e.preventDefault();
                e.stopPropagation();
            }).on('mousedown moseup', function(e){
                e.preventDefault();
                e.stopPropagation();
            });

            self.bars = this.element.find('.bars');
            self.tabs = this.element.find('.tabs');
            self.tabs.on('click', 'a', function(e){
                e.preventDefault();
                if (!$(this).parent().hasClass('disable')){
                    self.showBar($(this).parent().attr('data-bar'));
                }
            });
        },

        call_changeToolbarState: function(caller, args){
            var bar = this.bars.children('[data-v="'+ args.tool +'"]:first');
            if (bar.size() > 0){
                // Поиск таба
                var tab = this.tabs.children('[data-bar="' + bar.attr('data-v') + '"]');
                if (args.enable){
                    if (tab.hasClass('disable')){
                        bar.removeClass('disable');
                        tab.removeClass('disable');
                        this.showFirstBar();
                    }
                }else{
                    if (!tab.hasClass('disable')){
                        bar.addClass('disable');
                        tab.addClass('disable');
                        this.showFirstBar();
                    }
                }
            }
        },

        showFirstBar: function(){
            var bar = this.bars.children().not('.disable').first().attr('data-v');
            this.showBar(bar);
        },

        showBar: function(bar_name){
            if (bar_name){
                this.tabs.children('.selected').removeClass('selected');
                this.bars.children('.selected').removeClass('selected').hide();
                this.tabs.children('[data-bar="' + bar_name + '"]').addClass('selected');
                this.bars.children('[data-v="' + bar_name + '"]').addClass('selected').show();
            }
        }
    })
})(jQuery, _);