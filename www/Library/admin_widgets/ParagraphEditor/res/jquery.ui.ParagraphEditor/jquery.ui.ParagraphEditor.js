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
        _properties: null,
        _selinfo: null,

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
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

        /**
         * Опредление попадания в область выделения
         * @param caller
         * @param selection Selection
         * @param selinfo Object
         * @returns {*}
         */
        call_selectionchange: function(caller, selection, selinfo){
            this._selinfo = null;
            // Проверка вхождения в область выделения
            if (selection.isCollapsed){
                if (selinfo.end == selinfo.start && (this.element[0] == selinfo.start || (this.element[0].compareDocumentPosition(selinfo.start) & Node.DOCUMENT_POSITION_CONTAINED_BY))){
                    this._selinfo = {type: 'cursor'};
                }
            }else{
                var left = this.element[0].compareDocumentPosition(selinfo.start);
                var right = this.element[0].compareDocumentPosition(selinfo.end);
                if ((left & Node.DOCUMENT_POSITION_CONTAINS)){
                    if (selinfo.start.childNodes[selinfo.start_offset] === this.element[0]) this._selinfo = {type: 'is'};
                }else
                if ((right & Node.DOCUMENT_POSITION_CONTAINS)){
                    if (selinfo.end.childNodes[selinfo.end_offset-1] === this.element[0]) this._selinfo = {type: 'is'};
                }else
                if ((left & Node.DOCUMENT_POSITION_CONTAINED_BY) && (right & Node.DOCUMENT_POSITION_CONTAINED_BY)){
                    this._selinfo = {type: 'contain'};
                }else
                if ((left & Node.DOCUMENT_POSITION_PRECEDING) && (right & Node.DOCUMENT_POSITION_CONTAINED_BY) && selinfo.end_offset>0){
                    this._selinfo = {type: 'left'};
                }else
                if ((left & Node.DOCUMENT_POSITION_CONTAINED_BY) && (right & Node.DOCUMENT_POSITION_FOLLOWING)&& selinfo.start_offset < selinfo.start.textContent.length){
                    this._selinfo = {type: 'right'};
                }else
                if ((left & Node.DOCUMENT_POSITION_PRECEDING) && (right & Node.DOCUMENT_POSITION_FOLLOWING) && selinfo.end_offset>0 && selinfo.start_offset < selinfo.start.textContent.length){
                    this._selinfo = {type: 'in'};
                }
            }
            if (this._selinfo){
                this.changes(this._selinfo, selinfo, true);
                return this.options.object;
            }
            return false;
        },

        call_keydown: function(caller, e){
            if (this._selinfo){
                var self = this;
                if (this._selinfo.type == 'in' || this._selinfo.type == 'is'){
                    // Удаление абзаца
                    this.callServer('delete', {object: this.options.object}, {
                        success: function(result, textStatus, jqXHR){
                            self.element.remove();
                        }
                    });
//                    console.log('DELETE ALL'+this.options.object);
                }else
                if (this._selinfo.type == 'left'){
                    // Удаление текста в конце
                    var range = document.createRange();
                    range.setStart(this.element[0].firstChild, 0);
                    range.setEnd(this._selinfo.end, this._selinfo.end_offset);
                    range.deleteContents();
                    this._change(e);
//                    console.log('DELETE LEFT '+this.options.object);
                }else
                if (this._selinfo.type == 'right'){
                    // Удаление текста в начале
                    var range = document.createRange();
                    range.setStart(this._selinfo.start, this._selinfo.start_offset);
                    var lastChild = this.element[0].lastChild;
                    var lastChildoffset = lastChild.nodeType == Node.ELEMENT_NODE ? lastChild.childNodes.length : lastChild.textContent.length;
                    range.setEnd(lastChild, lastChildoffset);
                    range.deleteContents();
                    this._change(e);
//                    console.log('DELETE RIGHT'+this.options.object);
                }else
                if (this._selinfo.type == 'contain'){
                    // Удаление текста в центре
                    e.can_print = true;
//                    console.log('DELETE CENTER'+this.options.object);
                }else
                if (this._selinfo.type == 'cursor'){
                    // Курсор в абзаце. Учёт его позици и кода клавиши
                    var node = this._selinfo.start;
                    var offset = this._selinfo.start_offset;
                    var text = {
                        left: node.textContent.substring(0, offset),
                        right: node.textContent.substring(offset)
                    };
                    // BACKSPACE в начале абзаца - соединение с предыдущим абзацем если есть
                    if ((offset == 0 || (text.left.charCodeAt(0) == 8203 && text.left.length == 1)) && e.keyCode == 8){
                        // Поиск родительского элемента узла в this
                        while (!node.previousSibling && !this.element.is(node.parentNode)) node = node.parentNode;
                        // Если первый, то отмена BACKSPACE. Действие соединения с предыдущим элементом
                        if (!node.previousSibling && this.element.is(node.parentNode)){
                            //e.preventDefault();
//                            console.log('MERGE PREV '+this.options.object);
                            this._merge(this.element.prev('[data-o]'), this.element);
                        }
                        e.can_print = false;
                    }else
                    // DELETE в конце абзаца - присоединение следущего абзаца если есть
                    if ((offset == node.textContent.length || (text.right.charCodeAt(0) == 8203 && text.right.length == 1)) && e.keyCode == 46){
                        // Поиск родительского элемента узла в this
                        while (!node.nextSibling && !this.element.is(node.parentNode)) node = node.parentNode;
                        // Если последний, то отмена DELETE. Действие соединения со следующим элементом
                        if (!node.nextSibling && this.element.is(node.parentNode)){
                            //e.preventDefault();
//                            console.log('MERGE NEXT '+this.options.object);
                            this._merge(this.element, this.element.next('[data-o]'));
                        }
                        e.can_print = false;
                    }else
                    // ENTER - разделение абзаца на два
                    if (e.keyCode == 13 && !e.shiftKey){
                        this._devide(window.getSelection());
//                        console.log('DEVIDE '+this.options.object);
                    }else{
                        e.can_print = true;
                    }
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
         * Возвращение свойств абзаца
         *
         * @return {*}
         */
        call_getProperties: function(){
            if (this.element.hasClass('selected')){
                if (this._properties == null){
                    this._properties = {
                        element: this.element,
                        proto: this.options.oject_proto,
                        tag: 'p'
                    };
                }
                return this._properties;
            }
        },

        /**
         * Установка свойств абзаца
         * @param caller
         * @param properties
         */
        call_setProperties: function(caller, properties){
            if (this.element.hasClass('selected')){
                var self = this;
                if (_.isObject(properties)){
                    if (_.isObject(properties.style)) this.element.css(properties.style);
                    this.changes(this._properties, properties, true);
                    this.callServer('saveProperties', {
                        object: this.options.object,
                        saveProperties: properties
                    }, function(){
                        // Если сменился прототип, то перегрузить объект
                        if ('proto' in properties && properties.proto != self.options.oject_proto){
//                            self.load(self.element, 'replace', self.options.view, {object:self.options.object}, {
//                                url:'/',
//                                success: function(){
//                                    self._parent.callParents('refreshState');
//                                }
//                            });
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
                // Поиск родительского элемента узла в this
                while (!node.previousSibling && !this.element.is(node.parentNode)) node = node.parentNode;
                // Если первый, то отмена BACKSPACE. Действие соединения с предыдущим элементом
                if (!node.previousSibling && this.element.is(node.parentNode)){
                    e.preventDefault();
                    this._merge(this.element.prev('[data-o]'), this.element);
                }
                e.can_print = false;
            }else
            // DELETE
            if ((offset == node.textContent.length || (text.right.charCodeAt(0) == 8203 && text.right.length == 1)) && e.keyCode == 46 && !have_selection){
                // Поиск родительского элемента узла в this
                while (!node.nextSibling && !this.element.is(node.parentNode)) node = node.parentNode;
                // Если последний, то отмена DELETE. Действие соединения со следующим элементом
                if (!node.nextSibling && this.element.is(node.parentNode) && !have_selection){
                    e.preventDefault();
                    //this._merge(this.element, this.element.next('[data-o]'));
                }
                e.can_print = false;
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