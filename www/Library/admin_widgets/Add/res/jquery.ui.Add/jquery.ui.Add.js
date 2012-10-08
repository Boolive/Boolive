(function($) {
	$.widget("boolive.Add", $.boolive.AjaxWidget, {

        object: null,
        object_select: null,

        _create: function() {
			$.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;
            // uri объекта
            self.object = this.element.attr('data-object');

            // Выделение объекта
            self.element.on('before-entry-object', function(e, object){
                e.stopPropagation();
                self.select(object);
			}).on('before-select-object', function(e, object){
				e.stopPropagation();
                self.select(object);
			});

            // Отмена выделения при клике на свободную центральную область
            self.element.click(function(e){
                e.stopPropagation();
                self.select(null);
            });

            self.element.find('.submit').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                self._call('add', {
                    object: self.object,
                    proto: self.object_select
                }, function(result, textStatus, jqXHR){
                    self.element.parent().trigger('before-entry-object', [self.object]);
                });
            });
            self.element.find('.cancel').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                history.back();
            });
            self.element.find('.other').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                alert('Выбор других объектов нереализован');
            });
        },

        select: function(object){
            if (object != this.object_select){
                this.element.find('.selected').removeClass('selected');
                if (object !=null){
                    this.element.find('[data-object="'+self.escape(object)+'"]').parent().addClass('selected');
                    this.element.find('.submit').removeClass('btn-disable');
                }else{
                    this.element.find('.submit').addClass('btn-disable');
                }
                this.object_select = object;
            }
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
