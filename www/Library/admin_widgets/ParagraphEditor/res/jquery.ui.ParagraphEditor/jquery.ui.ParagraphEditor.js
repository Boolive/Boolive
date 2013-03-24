/**
 * Виджет редактора абзаца текста
 * Query UI widget
 * Copyright 2013 (C) Boolive
 */
(function($, _, document, undefined) {
    $.widget("boolive.ParagraphEditor", $.boolive.Widget, {

        options: {
            oject_proto: undefined // Идентификатор отображаемого объекта
        },
        _value: '',
        _is_change: false,

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            // Коррекция пустого значения
//            if (this.element.text()=='1'){
//                this.element.context.childNodes[0].textContent = '1';
//            }
            // Прототип отображаемого объекта
            if (!this.options.oject_proto){
                this.options.oject_proto = this.element.attr('data-o-proto');
            }
            this._value = self.element.html();

            this.element.on('click', function(e){
                // Отмена клика в редакторе, иначе сбросится выделение
                e.preventDefault();
                e.stopPropagation();
            });
        },

        call_keydown: function(caller, e, selection){
            if (!e.isPropagationStopped()){
                var e1 = selection.anchorNode;
                while (e1.nodeType!=1) e1 = e1.parentNode;
                var e2 = selection.focusNode;
                while (e2.nodeType!=1) e2 = e2.parentNode;
                if (this.element.is(e1) || this.element.is(e2) ||
                    this.element.has(e1).length || this.element.has(e2).length){
                    this._keydown(e, selection);
                }
            }
        },

        call_keyup: function(caller, e, selection){
            if (this.element.hasClass('selected')){
                // Проверка изменений в тексте
                this._change(e);
            }
        },

        call_blur: function(caller, e, selection){
            if (this.element.hasClass('selected')){
                this._change(e);
            }
        },

        call_paste: function(caller, e, selection){
            if (this.element.hasClass('selected')){
                this._change(e);
            }
        },

        /**
         * Сохранение значения текстового блока
         */
        call_save: function(caller){
            if (this._is_change){
                var self = this;
                self.callServer('save', {
                        object: self.options.object,
                        save: _.unescape(self.element.html().replace(/[\u200B-\u200D\uFEFF]/g, ''))
                }, {
                    success: function(result, textStatus, jqXHR){
                        self._value = self.element.html();
                        self._is_change = false;
                        self.callParents('nochange', [self.options.object]);
                    }
                });
            }
        },

        /**
         * Выделение и отмена выделения
         */
        call_setState: function(caller, state, changes){
            if ($.isPlainObject(changes) && 'selected' in changes){
                var select = _.indexOf(state.selected, this.options.object)!=-1;
                var is_selected = this.element.hasClass('selected');
                if (is_selected && !select){
                    this.element.removeClass('selected');
                }else
                if (!is_selected && select){
                    this.element.addClass('selected');
                }
            }
        },

        /**
         * Возвращение стиля
         * @return {*}
         */
        call_getStyle: function(){
            if (this.element.hasClass('selected')){
                var css = this.element.css(["margin-left", "margin-right", "text-indent", "text-align", "line-height", "font-size"]);
                if (css['line-height']!='normal'){
                    css['line-height'] = (parseFloat(css['line-height']) / parseFloat(css['font-size']));
                }
                css['paragraph-proto'] = this.options.oject_proto;
                //console.log('LINE HEIGHT: ' + css['line-height']+' FONT SIZE: ' + css['font-size']);
                //console.log(css['line-height']);
                return css;
            }
        },

        /**
         * Установка стиля
         * @param caller
         * @param style
         */
        call_setStyle: function(caller, style){
            if (this.element.hasClass('selected')){
                var self = this;
                if (!$.isEmptyObject(style)){
                    this.element.css(style);
                    this.callServer('saveStyle', {
                        object: this.options.object,
                        saveStyle: style
                    }, function(){
                        if ('paragraph-proto' in style){
                            self.callParents('reloadChild', [{object:self.options.object}, self]);
                        }
                    });
                }
            }
        },

        /**
         * Проверка, задевает ли текущее выделение элемент
         * @param selection
         * @return boolean
         */
        _isInSelection: function(selection){

            return this.element.is(selection.anchorNode.parentNode) || this.element.is(selection.focusNode.parentNode) ||
                   this.element.has(selection.anchorNode.parentNode).length || this.element.has(selection.focusNode.parentNode).length;
        },

        /**
         * В режиме contenteditable своё событие клавитауры не получить,
         * поэтому реагируем на событие родителя
         * @param e Событие
         * @param sel Выделение
         */
        _keydown: function(e, sel){
            var node = sel.anchorNode;
            var offset = sel.anchorOffset;
            var have_selection = sel.toString()!='';
            var text = {
                left: node.textContent.substring(0, offset),
                right: node.textContent.substring(offset)
            };

            e.can_print = false;
            if (node === this){
                e.preventDefault();
            }else
//            if ((offset == 1 && node.textContent.length == 1 && e.keyCode == 8) ||
//                (offset == 0 && node.textContent.length == 1 && e.keyCode == 46)){
//                node.textContent = '';
//                e.preventDefault();
//            }else
            // BACKSPACE
            if ((offset == 0 || (text.left.charCodeAt(0) == 8203 && text.left.length == 1)) && e.keyCode == 8 && !have_selection){
                console.log(text.left.charCodeAt(0));
                console.log(text.left.length);
                console.log(have_selection);
                // Поиск родительского элемента узла в this
                while (!node.previousSibling && !this.element.is(node.parentNode)) node = node.parentNode;
                // Если первый, то отмена BACKSPACE. Действие соединения с предыдущим элементом
                if (!node.previousSibling && this.element.is(node.parentNode)){
                    e.preventDefault();
                    this._merge(this.element.prev('[data-o]'), this.element);
                }
            }else
            // DELETE
            if ((offset == node.textContent.length || (text.right.charCodeAt(0) == 8203 && text.right.length == 1)) && e.keyCode == 46 && !have_selection){
                // Поиск родительского элемента узла в this
                while (!node.nextSibling && !this.element.is(node.parentNode)) node = node.parentNode;
                // Если последний, то отмена DELETE. Действие соединения со следующим элементом
                if (!node.nextSibling && this.element.is(node.parentNode)){
                    e.preventDefault();
                    this._merge(this.element, this.element.next('[data-o]'));
                }
            }else
            // ENTER
            if (e.keyCode == 13 && !e.shiftKey){
                e.preventDefault();
                e.stopPropagation();
                this._devide(sel);
            }else{
                // Разрешить ввод символа
                e.can_print = true;
            }
        },

        /**
         * Фиксирование изменений в текстовом блоке
         * @param e
         * @private
         */
        _change: function(e){
            if (this._value != this.element.html()){
                this._is_change = true;
                this.callParents('change', [this.options.object]);
            }else{
                this._is_change = false;
                this.callParents('nochange', [this.options.object]);
            }
        },

        /**
         * Объединение текстовых блоков.
         * Курсор устанавливается в начало добавляемого блока
         * @param primary Блок, к которому добавляется второй блок.
         * @param secondary Блок, который добавляется к первому блоку и после удаляется
         * @private
         */
        _merge: function(primary, secondary){
            if (primary.length && secondary.length){
                var p = primary.data('o');
                var s = secondary.data('o');
                this.callServer('merge', {
                        object: this.options.object,
                        merge: {
                            primary: primary.data('o'),
                            secondary: secondary.data('o')
                        }
                    }, {
                    success: function(result, textStatus, jqXHR){
                        if (result.out){
                            var cursor_node = primary[0].lastChild;
                            var nodes = secondary[0].childNodes;
                            var i;
                            for (i=0; i<nodes.length; i++){
                                primary[0].appendChild(nodes[i].cloneNode(true));
                            }
                            secondary.remove();

                            // Установка позиции курсора
                            if (cursor_node.nextSibling) cursor_node = cursor_node.nextSibling;
                            var sel = window.getSelection();
                            var range = document.createRange();
                            range.setStart(cursor_node, 0);
                            range.collapse(true);
                            sel.removeAllRanges();
                            sel.addRange( range );
                        }
                    }
                });
            }
        },

        /**
         * Разделение текстового блока на два
         * @param sel Текущее выделение в текстовом блоке по которому он делится
         * @private
         */
        _devide: function(sel){
            var node = sel.anchorNode;
            var offset = sel.anchorOffset;
            // Вставка разделительного символа
            var text = node.textContent;
            var textBefore = text.slice(0, offset);
            var textAfter = text.slice(offset);
            node.textContent = textBefore + '%devide%' + textAfter;
            text = this.element.html();
            // Удаление символов
            node.textContent = textBefore + '' + textAfter;

            var range = document.createRange();
            range.setStart(node, offset);
            range.collapse(true);

            // Разделение текста
            var m = text.split('%devide%',2);
            //var m = /^(.*)%devide%(.*)/im.exec(text);
            if (m.length == 2){
                node = node.parentNode;
                var tag;
                while (!this.element.is(node)){
                    tag = node.tagName.toLowerCase();
                    m[0] = m[0] + '</'+tag+'>';
                    m[0] = '<'+tag+'>' + m[1];
                    node = node.parentNode;
                }

                var self = this;
                self.callServer('devide', {
                        object: self.options.object,
                        devide: {
                            value1: m[0],
                            value2: m[1]
                        }
                    }, {
                    success: function(result, textStatus, jqXHR){
                        console.log(result);
                        if (!result.links) result.links = [];
                        $.include(result.links, function(){
                            var p = self.element.parent();
                            self.element.after(result.out[1]['html']);
                            self.element.replaceWith(result.out[0]['html']);

                            $(document).trigger('load-html', [p]);

                            var sel = window.getSelection();
                            var range = document.createRange();
                            var next = p.find('[data-o="'+result.out[1]['uri']+'"]').first()[0].firstChild;
                            range.setStart(next, 0);
                            range.collapse(true);
                            sel.removeAllRanges();
                            sel.addRange( range );
                        });
                    }
                });
            }
        }
    })
})(jQuery, _, document);