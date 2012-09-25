(function($) {
	$.widget("boolive.Explorer", $.boolive.AjaxWidget, {

        _create: function() {
			$.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;

            // Выделение объекта
            $(document).on('after-select-object', function(e, state){
                self.element.find('.selected').removeClass('selected');
                if (state.select){
                    self.element.find('[data-object="'+self.escape(state.select)+'"]').parent().addClass('selected');
                }
			});
        },

		destroy: function() {
			$.boolive.AjaxWidget.prototype.destroy.call(this);
		}
	})
})(jQuery);
