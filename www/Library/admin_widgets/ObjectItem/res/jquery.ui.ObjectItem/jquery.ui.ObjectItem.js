(function($) {
	$.widget("boolive.ObjectItem", $.boolive.AjaxWidget, {
        // uri отображаемого объекта
        _object: '',

        _create: function() {
			$.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;
            // uri объекта
            self._object = this.element.attr('data-object');
            // Вход в объект
			self.element.find('.enter').click(function(e){
				e.stopPropagation();
                e.preventDefault();
				self.element.trigger('before-entry-object', [self._object]);
			});
            // Выделение объекта
			self.element.click(function(e){
                e.stopPropagation();
                e.preventDefault();
                self.element.trigger('before-entry-object', [self._object]);
                //self.element.trigger('before-select-object', [self._object]);
			});
        },

		destroy: function() {
			$.boolive.AjaxWidget.prototype.destroy.call(this);
		}
	})
})(jQuery);
