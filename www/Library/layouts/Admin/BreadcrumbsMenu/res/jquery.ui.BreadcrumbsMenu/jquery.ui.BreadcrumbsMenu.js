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
            // Нажатие по пункту
			this.element.on('click', 'li a', function(e){
                e.preventDefault();
                // Вход в объект
                self.before('setState', [{
                    object: $(this).attr('href')
                }]);
			});
		},

        /**
         * При входе в объект - обновление элементов пути
         * @param state
         * @param changes
         */
        after_setState: function(state, changes){
            if ('object' in changes){
                var uri = state.object;
                var item = this.element.find('li a[href="'+uri+'"]');
                if (item.size()==0){
                    var path = '/';
                    var names = uri.split('/');
                    var cnt = names.length;
                    var ul = this.element.find('> ul:first');
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
                    item = this.element.find('li a[href="'+uri+'"]');
                    item.parent().addClass('active');
                }else{
                    this.element.find('li').removeClass('active').removeClass('preactive');
                    item.parent().addClass('active');
                }
            }
        }
	})
})(jQuery);
