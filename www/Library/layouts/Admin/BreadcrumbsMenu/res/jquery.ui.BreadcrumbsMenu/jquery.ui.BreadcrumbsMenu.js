/**
 * Breadcrumb menu in admin
 * JQuery UI widget
 * Copyright 2012 (C) Boolive
 */
(function($) {
	$.widget("boolive.BreadcrumbsMenu", $.boolive.AjaxWidget, {

        _create: function() {
			var self = this;
			$.boolive.AjaxWidget.prototype._create.call(this);

            $(document).on('after-entry-object', function(e, state, callback){
                var uri = state.object;
                var item = self.element.find('li a[href="'+uri+'"]');
                if (item.size()==0){
                    var path = '/';
                    var names = uri.split('/');
                    var cnt = names.length;
                    var ul = self.element.find('> ul:first');
                    var tab = ul.children('li:first').removeClass('active');
                    ul.empty();
                    ul.append(tab.css('z-index', cnt+1).clone());
                    for (var i = 1; i < cnt; i++){
                        path += names[i];
                        tab.css('z-index', cnt-i).children('a').attr('href', path).text(names[i]);
                        if (i+1 == cnt) tab.addClass('preactive');
                        ul.append(tab.clone());
                        path += '/';
                    }
                    self.element.find('li a[href="'+uri+'"]').parent().addClass('active');
                }else{
                    self.element.find('li').removeClass('active').removeClass('preactive');
				    self.element.find('li a[href="'+uri+'"]').parent().addClass('active');
                }
            });

            // Нажатие по пункту
			self.element.on('click', 'li a', function(e){
                e.preventDefault();
				self.element.trigger('before-entry-object', [$(this).attr('href')]);
			});
		},

		destroy: function() {
			$.boolive.wgWidget.prototype.destroy.call(this);
		}
	})
})(jQuery);
