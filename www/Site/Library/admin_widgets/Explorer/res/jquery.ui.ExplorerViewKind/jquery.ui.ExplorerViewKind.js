/**
 * Меню вида обозревателя
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($) {
    $.widget("boolive.ExplorerViewKind", $.boolive.Widget, {

        _selected: null,

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;

            this._selected = self.element.find('.selected:first');
            this.show_icon();
            this.element.find('li > a').on('click', function(e){
                if (!$(this).parent().hasClass('selected')){
                    self._selected.removeClass('selected');
                    self._selected = $(this).parent();
                    self._selected.addClass('selected');
                    self.callParents('changeViewKind', [$(this).attr('data-view-kind')]);
                }
                e.stopPropagation();
                e.preventDefault();
            });
        },

        show_icon: function(){
            this.element.children('a').attr('data-view-kind', this._selected.children('a').attr('data-view-kind'));
        }
    });
})(jQuery);