(function($) {
	$.widget("boolive.Explorer", $.boolive.AjaxWidget, {

        _create: function() {
			$.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;

            // Выделение объекта
            $(document).on('object-select-after', function(e, object){
                self.element.find('.selected').removeClass('selected');
                if (object != null){
                    self.element.find('[data-object="'+self.escape(object)+'"]').parent().addClass('selected');
                }
			});
        },

		destroy: function() {
			$.boolive.AjaxWidget.prototype.destroy.call(this);
		}
	})
})(jQuery);
