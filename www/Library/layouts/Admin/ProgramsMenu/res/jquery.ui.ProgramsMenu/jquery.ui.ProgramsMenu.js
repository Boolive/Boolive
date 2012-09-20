(function($) {
	$.widget("boolive.ProgramsMenu", $.boolive.AjaxWidget, {

        _create: function() {
			$.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;
            self.element.find('li a').click(function(e){
                e.preventDefault();
                //select
				if (self._active_items) self._active_items.removeClass('active');
				self._active_items = $(this).parent();
				self._active_items.addClass('active');
				//
                alert($(this).attr('href'));
				self.element.trigger('before-choice-view', $(this).attr('href'));
            });
        },

		destroy: function() {
			$.boolive.AjaxWidget.prototype.destroy.call(this);
		}
	})
})(jQuery);