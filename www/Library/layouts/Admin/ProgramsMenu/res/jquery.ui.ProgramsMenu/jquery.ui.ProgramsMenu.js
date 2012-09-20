(function($) {
	$.widget("boolive.ProgramsMenu", $.boolive.AjaxWidget, {
        _state: {object: null, select: null, view_name: null},

        _create: function() {
			$.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;
            self.element.on('click', 'li a', function(e){
                e.preventDefault();
				self.element.trigger('before-choice-view', $(this).attr('href'));
            });
            $(document).on('after-select-object', function(e, state){
                if (self._state.select != state.select){
                    self._state = $.extend({}, state);
                    self.reload('/', {object:state.select}, function(){self.select();});
                }else{
                    self._state = $.extend({}, state);
                    self.select();
                }
            });
        },

        select: function(){
            //alert(this._state.object+' | '+this._state.select);
            // Выделение, если не выделен подчиенный объект
            if (this._state.object == this._state.select){
                var sel = null;
                // Если view_name не указан, то выделяется первая закладка
                if (!this._state.view_name){
                    sel = this.element.find('> ul > li:first-child');
                }else{
                    sel = this.element.find('> ul > li a[href="'+this._state.view_name+'"]').parent();
                }
                if (sel!=this._active_items){
                    if (this._active_items) this._active_items.removeClass('active');
                    this._active_items = sel;
                    this._active_items.addClass('active');
                }
            }else{
                if (this._active_items) this._active_items.removeClass('active');
                this._active_items = null;
            }
        },

		destroy: function() {
			$.boolive.AjaxWidget.prototype.destroy.call(this);
		}
	})
})(jQuery);