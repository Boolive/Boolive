(function($) {
	$.widget("boolive.Programs", $.boolive.AjaxWidget, {

        _create: function() {
			$.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;

            // Вход в объект
            $(document).on('after-entry-object', function(e, object){
                self.reload('/admin', {object:object});
			});
        },

		destroy: function() {
			$.boolive.AjaxWidget.prototype.destroy.call(this);
		}
	})
})(jQuery);