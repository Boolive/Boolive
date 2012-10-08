(function($) {
	$.widget("boolive.Delete", $.boolive.AjaxWidget, {

        object: null,

        _create: function() {
			$.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;
            // uri объекта
            self.object = this.element.attr('data-object');

            self.element.find('.submit').click(function(e){
                e.preventDefault();
                self._call('delete', {object: self.object}, function(result, textStatus, jqXHR){
                    self.element.trigger('before-entry-object', [self.getParentOfUri(self.object)]);
                });
            });
            self.element.find('.cancel').click(function(e){
                e.preventDefault();
                history.back();
            });
        },

        getParentOfUri: function(uri){
            var m = uri.match(/^(.*)\/[^\\\/]*$/);
            if (m){
                return m[1];
            }else{
                return '';
            }
        },

		destroy: function() {
			$.boolive.AjaxWidget.prototype.destroy.call(this);
		}
	})
})(jQuery);
