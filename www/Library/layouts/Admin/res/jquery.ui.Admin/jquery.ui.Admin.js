(function($) {
	$.widget("boolive.Admin", $.boolive.AjaxWidget, {

        _object: '', // отображаемый объект
        _object_selected: '', // выделенный объект

        _create: function() {
			$.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;
            // uri отображаемого объекта
            var result = /^#!(.+)/.exec(location.hash);
            if (result){
                self.entry(result[1]);
            }
            // Выделение объекта
            self.element.on('before-select-object', function(e, object){
				self.select(object);
			});
            // Вход в объект
            self.element.on('before-entry-object', function(e, object){
				self.entry(object);
			});
            // Отмена выделения при клике на свободную центральную область
            self.element.find('.center').click(function(e){
               self.element.trigger('object-select-before', [null]);
            });
        },

        /**
         * Выделение объекта
         * Если объект не указан, то выделенным становится отображаемый объект
         * @param object URI выделенного объекта
         */
        select: function(object){
            if (object == null){
                this._object_selected = this._object;
            }else{
                this._object_selected = object;
            }
            $(document).trigger('object-select-after', [this._object_selected, this._object]);
        },

        /**
         * Вход в объект
         * @param object URI объекта
         */
        entry: function(object){
            this._object = this._object_selected = object;
            location.hash = '#!'+object;
            $(document).trigger('after-entry-object', [this._object]);
        },

		destroy: function() {
			$.boolive.AjaxWidget.prototype.destroy.call(this);
		}
	})
})(jQuery);
