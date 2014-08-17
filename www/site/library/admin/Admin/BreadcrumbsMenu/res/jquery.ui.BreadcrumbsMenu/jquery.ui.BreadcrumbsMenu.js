/**
 * Breadcrumb menu in admin
 * JQuery UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, window, _) {
	$.widget("boolive.BreadcrumbsMenu", $.boolive.Widget, {
        view: null,
        list: null,
        inline: null,
        editor: null,
        inline_input: null,
        tab: null,

        _create: function() {
			$.boolive.Widget.prototype._create.call(this);
            var self = this;
            self.view = self.element.find('.BreadcrumbsMenu__view:first');
            self.list = self.element.find('.BreadcrumbsMenu__list:first');
            self.inline = self.element.find('.BreadcrumbsMenu__inline:first');
            self.inline_input = self.inline.find('.BreadcrumbsMenu__list-inline-input');
            self.editor = self.element.find('.BreadcrumbsMenu__editor:first');
            self.tab = self.list.children('.BreadcrumbsMenu__list-item:first').removeClass('active').remove();
            // Режим строки
            self.element.on('click', '.BreadcrumbsMenu__btn-inline', function(e){
                e.preventDefault();
                e.stopPropagation();
                self.view.hide();
                self.inline.show();
                self.setFocuse(self.inline_input[0]);
                self.element.addClass('BreadcrumbsMenu-max');
            });
            // Режим переименования
            self.element.on('click', '.BreadcrumbsMenu__btn-editor', function(e){
                e.preventDefault();
                e.stopPropagation();
                self.view.hide();
                self.inline.hide();
                self.editor.show();
                self.element.addClass('BreadcrumbsMenu-max');
                $(document).on('click' + self.eventNamespace, function(e) {
                    if (self.element[0] == e.target/* && self.element.find(e.target).size() == 0*/){
                        self.inline.hide();
                        self.editor.hide();
                        self.view.show();
                        self.element.removeClass('BreadcrumbsMenu-max');
                        $(document).off('click' + self.eventNamespace);
                    }
                });
            });
            // Завершение редактирования
            self.inline_input.on('focusout', function(){
                self.element.removeClass('BreadcrumbsMenu-max');
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
			self.list.on('click', '.BreadcrumbsMenu__list-entity', function(e){
                e.preventDefault();
                e.stopPropagation();
                // Вход в объект
                self.callParents('setState', [{
                    object: $(this).attr('data-o')
                }]);
			}).on('mouseover', '.BreadcrumbsMenu__list-entity', function(){
//                console.log($(this));
                $(this).data('max-width', $(this).find('span').css('max-width'));
                $(this).find('span').css('max-width', 'none');
            }).on('mouseout', '.BreadcrumbsMenu__list-entity', function(){
                $(this).find('span').css('max-width', $(this).data('max-width'));
            });
            this.truncatePath();
            $(document).on('resize' + this.eventNamespace, function() {
                self.truncatePath();
            });
		},

        truncatePath: function(){
            var cnt = this.list.children().size();
            var w = (($(window).width() - 420) / cnt) - 30 + 'px' ;
            this.list.find('.BreadcrumbsMenu__list-entity > span').css('max-width', w);
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
                if (_.isString(uri)){
                    var self = this;
                    var item = this.element.find('.BreadcrumbsMenu__list-entity[data-o="'+uri+'"]');
                    if (item.size()==0){
                        this.call_('getBreadcrumbs', self.options.view, {object: uri}, function(data, textStatus, jqXHR){
                            if (_.isArray(data.result)){
                                var cnt = data.result.length;
                                self.list.empty();
                                for (var i=0; i<cnt; i++){
                                    self.tab.css('z-index', i).children('a').attr('data-o', data.result[i]['uri']).attr('href', data.result[i]['url']).children('span').text(data.result[i]['title']);
                                    self.tab.removeClass().addClass('BreadcrumbsMenu__list-item');
                                    if (data.result[i]['class']){
                                        self.tab.addClass(data.result[i]['class']);
                                    }
                                    self.list.prepend(self.tab.clone().show());
                                }
                            }
                            self.truncatePath();
                        });
                    }else{
                        self.list.find('.BreadcrumbsMenu__list-item').removeClass('active').removeClass('preactive').show();
                        item.parent().addClass('active');
                        self.truncatePath();
                    }
                    //if (!self.inline.is(":visible")){
                        self.inline_input.val(uri);
                        self.element.find('.BreadcrumbsMenu__btn-out').attr('href', uri||'/');
                    var info = self.getDirAndName(uri);
                    self.element.find('.BreadcrumbsMenu__editor-uri-parent').text(uri?info.dir + '/':'');
                        self.element.find('.BreadcrumbsMenu__editor-uri-name').text(info.name);
                    //}
                }
            }
        },

        call_updateURI: function(e, args){
            this.call_setState({}, {object: args.new_uri}, {object:true});
        }
	})
})(jQuery, window, _);
