(function($) {
	$.widget("boolive.Programs", $.boolive.AjaxWidget, {

        _create: function() {
			$.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;

            // Вход в объект
            $(document).on('after-entry-object', function(e, state, callback){
                self.reload('/', state, callback);
			});

            $(document).on('after-choice-view', function(e, object, select, view){

                self.reload('/', {object:select, view_name:view});
			});
        },

		destroy: function() {
			$.boolive.AjaxWidget.prototype.destroy.call(this);
		}
	})
})(jQuery);