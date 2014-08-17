/**
 * Query UI boolive.Widget
 * Логика виджета на стороне клиента
 */
(function($, _, undefined) {
    $.widget("boolive.Sidebar", $.boolive.Widget, {

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            this.element.children('.Sidebar__views').touchScroll();
            var self = this;
            var views = this.element.find('.Sidebar__views:first');
            var tabs = this.element.find('.Sidebar__tabs:first');
            this.element.find('.Sidebar__tab').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                if (!$(this).hasClass('Sidebar__tab_active')){
                    var name = $(this).attr('data-name');
                    views.find('.Sidebar__view_active').removeClass('Sidebar__view_active');
                    tabs.find('.Sidebar__tab_active').removeClass('Sidebar__tab_active');
                    views.find('[data-name="'+name+'"]').addClass('Sidebar__view_active');
                    tabs.find('[data-name="'+name+'"]').addClass('Sidebar__tab_active');
                }
            })
        }
    })
})(jQuery, _);