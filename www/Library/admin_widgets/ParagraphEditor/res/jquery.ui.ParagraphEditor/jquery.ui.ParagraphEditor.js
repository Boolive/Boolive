/**
 * Виджет редактора абзаца текста
 * Query UI widget
 * Copyright 2013 (C) Boolive
 */
(function($, document) {
    $.widget("boolive.ParagraphEditor", $.boolive.AjaxWidget, {

        _value: '',

        _create: function() {
            $.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;
            // Коррекция пустого значения
            if (this.element.html()==' '){
                this.element.context.childNodes[0].textContent = '';
            }
            // начальное значение
            this._value = self.element.html();

            this.element.on('mousedown'+this.eventNamespace, function(e){
                self._select();
            }).on('click', function(e){
                e.stopPropagation();
            });
        },

        call_keydown: function(caller, e, selection){ //after
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

        call_keyup: function(caller, e, selection){ //after
            if (this._isInSelection(selection)){
                this._select();
                this._change(e);
            }
        },

//        call_blur: function(caller, e, selection){
//            if (this._isInSelection(selection)){
//                this._change(e);
//            }
//        },

        call_paste: function(caller, e, selection){
            if (this._isInSelection(selection)){
                this._change(e);
            }
        },

        /**
         * Сохранение значения текстового блока
         */
        call_save: function(caller){
            var self = this;
            self.callServer('save', {
                    object: self.options.object,
                    save: self.element.html()
            }, {
                success: function(result, textStatus, jqXHR){
                    self._value = self.element.html();
                    self.callParents('nochange', [self.options.object]);
                }
            });
        },

        /**
         * Отмена выделения (фокуса) текстового блока
         * @param state
         * @param changes
         */
        call_setState: function(caller, state, changes){ //after
            if ($.isPlainObject(changes) && 'selected' in changes && _.indexOf(state.selected, this.options.object)==-1){
                this.element.removeClass('selected');
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
            e.can_print = false;
            if (node === this){
                e.preventDefault();
            }else
            if ((offset == 1 && node.textContent.length == 1 && e.keyCode == 8) ||
                (offset == 0 && node.textContent.length == 1 && e.keyCode == 46)){
                node.textContent = '';
                e.preventDefault();
            }else
            // BACKSPACE
            if (offset == 0 && e.keyCode == 8 && !have_selection){
                // Поиск родительского элемента узла в this
                while (!node.previousSibling && !this.element.is(node.parentNode)) node = node.parentNode;
                // Если первый, то отмена BACKSPACE. Действие соединения с предыдущим элементом
                if (!node.previousSibling && this.element.is(node.parentNode)){
                    e.preventDefault();
                    this._megre(this.element.prev('[data-o]'), this.element);
                }
            }else
            // DELETE
            if (offset == node.textContent.length && e.keyCode == 46 && !have_selection){
                // Поиск родительского элемента узла в this
                while (!node.nextSibling && !this.element.is(node.parentNode)) node = node.parentNode;
                // Если последний, то отмена DELETE. Действие соединения со следующим элементом
                if (!node.nextSibling && this.element.is(node.parentNode)){
                    e.preventDefault();
                    this._megre(this.element, this.element.next('[data-o]'));
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
                this.callParents('change', [this.options.object]);
            }else{
                this.callParents('nochange', [this.options.object]);
            }
        },

        /**
         * Выделение (фокус) текстового блока
         * @private
         */
        _select: function(){
            if (!this.element.hasClass('selected')){
                this.element.addClass('selected');
                this.callParents('setState', [{selected:  this.options.object}]);
            }
        },

        /**
         * Объединение текстовых блоков.
         * Курсор устанавливается в начало добавляемого блока
         * @param primary Блок, к которому добавляется второй блок.
         * @param secondary Блок, который добавляется к первому блоку и после удаляется
         * @private
         */
        _megre: function(primary, secondary){
            if (primary.length && secondary.length){
                this.callServer('merge', {
                        object: this.options.object,
                        merge: {
                            primary: primary.data('object'),
                            secondary: secondary.data('object')
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

        call_getStyle: function(){
            if (this.element.hasClass('selected')){
                return this.element.css(["margin-left", "margin-right", "text-indent"]);
            }
        },

        call_setStyle: function(caller, style){
            if (this.element.hasClass('selected')){
                if (!$.isEmptyObject(style)){
                    this.element.css(style);
                    this.callServer('saveStyle', {
                        object: this.options.object,
                        saveStyle: style
                    });
                }
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
})(jQuery, document);