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
                self.callParents('setState', [{
                    object: $(this).attr('data-o')
                }]);
			});
            this.element.on('click', function(e){
                e.preventDefault();
                e.stopPropagation();
            });
		},

        /**
         * При входе в объект - обновление элементов пути
         * @param caller Информация, кто вызывал {target, direct}
         * @param state Объект текущего состояния.
         * @param changes Что изменилось в объекте состояния
         */
        call_setState: function(caller, state, changes){ //after
            if ($.isPlainObject(changes) && ('object' in changes)){
                var uri = state.object;
                var item = this.element.find('li a[data-o="'+uri+'"]');
                if (item.size()==0){
//                    var path = '/';
//                    var names = uri.split('/');
//                    var cnt = names.length;
//                    var ul = this.element.find('> ul:first');
//                    var tab = ul.children('li:first').removeClass('active');
//                    ul.empty();
//                    ul.append(tab.css('z-index', cnt+1).clone());
//                    for (var i = 1; i < cnt; i++){
//                        path += names[i];
//                        tab.css('z-index', cnt-i).children('a').attr('data-o', path).attr('href', '/admin'+path).text(names[i]);
//                        if (i+1 == cnt) tab.addClass('preactive');
//                        ul.append(tab.clone());
//                        path += '/';
//                    }
//                    item = this.element.find('li a[data-o="'+uri+'"]');
//                    item.parent().addClass('active');
                    this.reload({direct: this.options.view, object: uri});
                }else{
                    this.element.find('li').removeClass('active').removeClass('preactive');
                    item.parent().addClass('active');
                }
            }
        }
	})
})(jQuery);
