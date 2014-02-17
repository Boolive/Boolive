/**
 * Breadcrumb menu in admin
 * JQuery UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, window, _) {
	$.widget("boolive.BreadcrumbsMenu", $.boolive.Widget, {
        view: null,
        inline: null,
        tab: null,

        _create: function() {
			$.boolive.Widget.prototype._create.call(this);
            var self = this;
            self.view = self.element.find('.view');
            self.inline = self.element.find('.inline');
            self.tab = self.view.children('li:first').removeClass('active').remove();
            // Режим редактирование
            self.element.on('click', function(e){
                e.preventDefault();
                e.stopPropagation();
                var input = self.element.find('.inline input')[0];
                if (e.target != input){
                    self.view.hide();
                    self.inline.show();
                    self.setFocuse(input);
                }
            });
            // Завершение редактирования
            self.inline.find('input').on('focusout', function(){
                self.inline.hide();
                self.view.show();
            }).on('keyup', function(e){
                if (e.keyCode == 13){
                    // Вход в объект
                    self.callParents('setState', [{
                        object: $(this).val()
                    }]);
                }
            });
            // Нажатие по пункту меню
			self.view.on('click', 'li a', function(e){
                e.preventDefault();
                e.stopPropagation();
                // Вход в объект
                self.callParents('setState', [{
                    object: $(this).attr('data-o')
                }]);
			}).on('mouseover', 'li a', function(){
//                console.log($(this));
                $(this).data('max-width', $(this).find('span').css('max-width'));
                $(this).find('span').css('max-width', 'none');
            }).on('mouseout', 'li a', function(){
                $(this).find('span').css('max-width', $(this).data('max-width'));
            });
            this.truncatePath();
		},

        truncatePath: function(){
            var cnt = this.view.children().size();
            var w = (($(window).width() - 420) / cnt) - 20 + 'px' ;
            this.view.find('li a > span').css('max-width', w);
        },

        /**
         * Установка курсора в конец строки
         * @param input
         */
        setFocuse: function(input) {
            var length = input.value.length;
            // For IE Only
            if (document.selection) {
                input.focus();
                // Use IE Ranges
                var oSel = document.selection.createRange();
                // Reset position to 0 & then set at end
                oSel.moveStart('character', -length);
                oSel.moveStart('character', length);
                oSel.moveEnd('character', 0);
                oSel.select();
            }
            else if (input.selectionStart || input.selectionStart == '0') {
                // Firefox/Chrome
                input.selectionStart = length;
                input.selectionEnd = length;
                input.focus();
            }
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
                var self = this;
                var item = this.element.find('li a[data-o="'+uri+'"]');
                if (item.size()==0){
                    this.call_('getBreadcrumbs', self.options.view, {object: uri}, function(data, textStatus, jqXHR){
                        if (_.isArray(data.result)){
                            var cnt = data.result.length;
                            self.view.empty();
                            for (var i=0; i<cnt; i++){
                                self.tab.css('z-index', i).children('a').attr('data-o', data.result[i]['uri']).attr('href', data.result[i]['url']).children('span').text(data.result[i]['title']);
                                self.tab.removeClass();
                                if (data.result[i]['class']){
                                    self.tab.addClass(data.result[i]['class']);
                                }
                                self.view.prepend(self.tab.clone().show());
                            }
                        }
                        self.truncatePath();
                    });
                }else{
                    self.view.find('li').removeClass('active').removeClass('preactive').show();
                    item.parent().addClass('active');
                    self.truncatePath();
                }
                //if (!self.inline.is(":visible")){
                    self.inline.find('input').val(uri);
                //}

            }
        },

        call_updateURI: function(e, args){
            this.call_setState({}, {object: args.new_uri}, {object:true});
//            var reg = new RegExp('^'+args.uri+'(\/|$)');
//            if (reg.test(this.options.object)){
//                this.options.object = this.options.object.replace(args.uri, args.new_uri);
//            }
//            this.element.find('li a[data-o^="'+args.uri+'"]').each(function(){
//                if (reg.test($(this).attr('data-o'))){
//                    $(this).attr('data-o', $(this).attr('data-o').replace(args.uri, args.new_uri));
//                    $(this).attr('href', $(this).attr('data-o').replace(/^\//,''));
//                }
//            })
        }
	})
})(jQuery, window, _);
