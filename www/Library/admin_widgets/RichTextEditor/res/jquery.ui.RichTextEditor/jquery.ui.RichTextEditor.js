/**
 * Виджет редактора форматированного текста
 * Query UI widget
 * Copyright 2013 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.RichTextEditor", $.boolive.Widget, {
        // объекты кнопок
        _buttons: {},
        _content: null,
        // uri выделенных объектов текста
        _selected: null,
        // uri измененных подчиенных
        _changes: {},
        _changes_cnt: 0,
        _save_inteval: null,
        _hide: true,
        _filter: null,


        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            this.call_program_show();
            var self = this;
            this._filter = JSON.parse(this.element.attr('data-filter'));
            this._buttons['save'] = this.element.find('.save');
            this._content = this.element.children('.content').first();
            this._selected = [];
            // Отмена встроенного изменения размера
            document.execCommand("enableObjectResizing", false, false);
            // Изменения текстового выделения. Если не поддерживается, то обрабатываются mouseup, keyup
            var selectionchange = false;
            $(document).on('selectionchange'+this.eventNamespace, function(){
                if (!selectionchange) selectionchange = true;
                self._selectionchange();
            });
            var selection_start = false;
            this._content.on('mousedown'+this.eventNamespace, function(e){
                selection_start = true;
//                e.stopPropagation();
            }).on('mouseup'+this.eventNamespace, function(e){
                // Если выделение не обработано событием selectionchange
                if (!selectionchange && selection_start){
                    selection_start = false;
                    self._selectionchange();
                    e.stopPropagation();
                }
            }).on('keydown'+this.eventNamespace, function(e){
                // Arrows, Home, End, PgUp, PgDn, F1-F12
                if ((e.keyCode >=33 && e.keyCode <=40) || (e.keyCode >=112 && e.keyCode <=123) ||
                    // NumLk, Esc, CapsLk, Shift, Ctrl, Alt
                    e.keyCode == 144 || e.keyCode == 27 || e.keyCode == 20 || e.keyCode == 16 || e.keyCode == 17 || e.keyCode == 18 ||
                    // Del, Ins, Backspace
                    e.keyCode == 46 || e.keyCode == 45 || e.keyCode == 46 || e.keyCode == 8 ||
                    // ShortCuts
                    e.ctrlKey || e.altKey)
                {
                    e.key_print = false;
                }else{
                    e.key_print = true;
                }
                self.callChildren('keydown', [e]);
                if (!e.can_print){
                    e.stopPropagation();
                    e.preventDefault();
                }
            }).on('keyup'+this.eventNamespace, function(e){
                if (!selectionchange) self._selectionchange();
                var sel = window.getSelection();
                self.callChildren('keyup', [e, sel]);
//                e.stopPropagation();
            }).on('click'+this.eventNamespace, function(e){
                e.preventDefault();
                e.stopPropagation();
            });

            this._buttons['save'].on('click', function(e){
                e.preventDefault();
                e.stopPropagation();
                self._save();
            });

            this.element.on('blur'+this.eventNamespace+' paste'+this.eventNamespace, function(e){
                var sel = window.getSelection();
                self.callChildren(e.type, [e, sel]);
            }).on('cut'+this.eventNamespace, function(e){
                e.preventDefault();
            }).on('paste'+this.eventNamespace, function(e){
                e.preventDefault();
            })
        },

        _destroy: function(){
            this.call_program_hide();
            this.callChildren('save');
            $.boolive.Widget.prototype._destroy.call(this);
        },

        _save: function(){
            if (!$(this).hasClass('btn-frozen') && !$(this).hasClass('btn-disable') && this._changes_cnt){
                $(this).addClass('btn-frozen');
                this.callChildren('save');
            }
        },

        /**
         * Определение объектов в области выделения
         * @returns {Array}
         * @private
         */
        _selectionchange: function(){
            var selection = window.getSelection();
            if (selection.rangeCount > 0 && !(this._content[0].compareDocumentPosition(selection.anchorNode) & Node.DOCUMENT_POSITION_CONTAINS)
                && !(this._content[0].compareDocumentPosition(selection.focusNode) & Node.DOCUMENT_POSITION_CONTAINS)){
                var info;
//                console.log(selection);
                // Определение начала и конца области выделения.
                var anchor = selection.anchorNode;
                if (selection.anchorNode.nodeType == Node.ELEMENT_NODE){
                    anchor = selection.anchorOffset > 0 ? selection.anchorNode.childNodes[selection.anchorOffset-1] : selection.anchorNode.childNodes[0];
                }
                var focus = selection.focusNode;
                if (selection.focusNode.nodeType == Node.ELEMENT_NODE){
                    focus = selection.focusOffset > 0 ? selection.focusNode.childNodes[selection.focusOffset-1] : selection.focusNode.childNodes[0];
                }
                if (anchor.compareDocumentPosition(focus) & Node.DOCUMENT_POSITION_PRECEDING){
                    info = {
                        start: selection.focusNode,
                        start_offset: selection.focusOffset,
                        end: selection.anchorNode,
                        end_offset: selection.anchorOffset
                    };
                }else{
                    info = {
                        start: selection.anchorNode,
                        start_offset: selection.anchorOffset,
                        end: selection.focusNode,
                        end_offset: selection.focusOffset
                    };
                }
                if (/*info.start.nodeType == Node.ELEMENT_NODE && */info.start == this._content[0]){
                    info.start = info.start.childNodes[info.start_offset];
                }
                if (/*info.end.nodeType == Node.ELEMENT_NODE*/info.end == this._content[0]){
                    info.end = info.end.childNodes[info.end_offset-1];
                }
                if (info.start.compareDocumentPosition(info.end) & Node.DOCUMENT_POSITION_PRECEDING){
                    var tmp = info.start;  info.start = info.end; info.end = tmp;
                    tmp = info.start_offset; info.start_offset = info.end_offset; info.end_offset = tmp;
                }
                // Передача обработки keydown подчиненным
                this._selected = this.callChildren('selectionchange', [selection, info]);
                this._selected = _.filter(this._selected, function(uri){ return uri !== false; });
                this.callParents('setState', {selected: this._selected});
            }else{
                this._selected = [];
            }
//            console.log(this._selected);
            return this._selected;
        },

        call_setState: function(caller, state, changes){
            if (caller.direct == 'children'){
                if (_.indexOf(state.selected, this.option.object)){
                    this._selectionchange();
                }
            }
        },

        /**
         * Обработка открытия редактора
         */
        call_program_show: function(){
            if (this._hide){
                this._hide = false;
                $('.Admin').addClass('RichTextBG');
                //this._save_inteval = setInterval(function(){self._save()}, 10000);
            }
        },

        /**
         * Обработка закрытия редактора
         */
        call_program_hide: function(){
            if (!this._hide){
                this._hide = true;
                //clearInterval(this._save_inteval);
                this._save();
                $('.Admin').removeClass('RichTextBG');
            }
        },

        /**
         * Вставка нового абзаца
         */
        insert_new_p: function(){
            var self = this;
            self.new_p = true;
            this.callServer('new_p', {object: self.options.object}, {
                success: function(result, textStatus, jqXHR){
                    if (!result.links) result.links = [];
                    $.include(result.links, function(){
                        var t = self._content.text();
                        self._content.empty();
                        self._content.append(result.out);
                        $(document).trigger('load-html', [self._content]);
                        var p = self._content.children()[0];
                        p.firstChild.textContent+=t; // Текст введенный до появления абзаца
                        self.cursor_to(p.firstChild, p.firstChild.textContent.length);
                    });
                }
            });

        },

        /**
         * Перемещение курсора в указанный элемент и позицию внутри элемента
         * @param element
         * @param pos
         */
        cursor_to: function(element, pos){
            var sel = window.getSelection();
            var range = document.createRange();
            range.setStart(element, pos);
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange( range );
        },

        /**
         * Обработка изменений в редакторе (изменение текста)
         * @param caller
         * @param object
         */
        call_change: function(caller, object){ //before
            if (!this._changes[object]){
                this._buttons['save'].removeClass('btn-disable');
                this._changes[object] = true;
                this._changes_cnt++;
            }
        },

        /**
         * Обработка отсутствия изменений после сохранения
         * @param caller
         * @param object
         */
        call_nochange: function(caller, object){ //before
            if (this._changes[object]){
                delete this._changes[object];
                this._changes_cnt--;
                if (!this._changes_cnt){
                    this._buttons['save'].addClass('btn-disable').removeClass('btn-frozen');
                }
            }
        },

        call_replace: function(caller, element){ //before


            return true;
        },

        /**
         * Обработка команды обновить с сервера подчиненный элемент
         * @param caller
         * @param data
         * @param child
         */
        call_reloadChild: function(caller, data, child){
            var self = this;
            this.load(child.element, 'replace', this.options.view+'/switch_views', data, {
                url:'/',
                success: function(){
                    self.callParents('refreshState');
                }
            });
        },

        /**
         * Получение свойств текущего выделенного объекта
         * Если не выделен ни один объект текста, то выделенным считается весь текст (страница)
         * @returns {*}
         */
        call_getProperties: function(){
            // Если нет выделенных объектов, то возвращаем свойства текста
            if (!this._selected.length){
                return [{
                    element: this._content,
                    filter: this._filter
                }];
            }
            return this.callChildren('getProperties');
        },

        /**
         * Установка свойств текущему выделнному элементу
         * @param caller
         * @param properties
         */
        call_setProperties: function(caller, properties){
            var self = this;
            if (_.isObject(properties)){
                if (!this._selected.length){
                    // Установка и сохранение стиля страницы
                    if (_.isObject(properties.style)) this._content.css(properties.style);
                    this.callServer('saveProperties', {
                        object: this.options.object,
                        saveProperties: properties
                    }, function(){
                        if ('filter' in properties){
                            self.load(self.element, 'replace', self.options.view, {object: self.options.object}, {url:'/'});
                        }
                    });
                }else{
                    // Установка стилей подчиенных (сами определят кому)
                    this.callChildren('setProperties', [properties]);
                }
            }
        }
    })
})(jQuery, _);