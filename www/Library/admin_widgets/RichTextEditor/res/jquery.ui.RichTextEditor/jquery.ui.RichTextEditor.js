/**
 * Виджет редактора форматированного текста
 * Query UI widget
 * Copyright 2013 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.RichTextEditor", $.boolive.AjaxWidget, {
        // объекты кнопок
        _buttons: {},
        _content: null,
        // uri измененных подчиенных
        _changes: {},
        _changes_cnt: 0,
        _save_inteval: null,
        _hide: true,



        _create: function() {
            $.boolive.AjaxWidget.prototype._create.call(this);
            this.call_program_show();
            var self = this;

            this._buttons['save'] = this.element.find('.save');
            this._content = this.element.children('.content').first();
//            if (!this._content.children().length){
//                this._content[0].childNodes[0].textContent = '';
//            }
            // Отмена встроенного изменения размера
            document.execCommand("enableObjectResizing", false, false);
            // Изменения текстового выделения. Если не поддерживается, то обрабатываются mouseup, keyup
            var selection_change = false;
            $(document).on('selectionchange'+this.eventNamespace, function(){
                if (!selection_change) selection_change = true;
                self._onselect();
            });
            var selection_start = false;
            this._content.on('mousedown'+this.eventNamespace, function(e){
                selection_start = true;
//                e.stopPropagation();
            }).on('mouseup'+this.eventNamespace, function(e){
                if (!selection_change && selection_start){
                    selection_start = false;
                    self._onselect();
                    e.stopPropagation();
                }
            }).on('keyup'+this.eventNamespace, function(e){
                if (!selection_change) self._onselect();
                var sel = window.getSelection();
                self.callChildren('keyup', [e, sel]);
//                e.stopPropagation();
            }).on('click', function(e){
                e.preventDefault();
                e.stopPropagation();
            });


            this._buttons['save'].on('click', function(e){
                var sel = window.getSelection();
                e.preventDefault();
                e.stopPropagation();
                self._save();
            });
//            this.element.on('click', function(e){
//
//                if ($(e.target).is(self._content)){
//
//                    if (!_.size(self._children)){
////                        var sel = window.getSelection();
////                        var range = document.createRange();
////                        var p = self._content[0];
////                        range.setStart(p, 0);
////                        range.collapse(true);
////                        sel.removeAllRanges();
////                        sel.addRange( range );
//                        self.cursor_to(self._content.children()[0], 0);
//                    }else{
//                        var sel = window.getSelection();
////                        if (sel.anchorNode.nodeType==1){
////                            self.cursor_to(sel.anchorNode.lastChild, 0);
////                        }else{
////                        var x = self._content.children();
////                        var a = self._content.children()[0].lastChild;
//                        //while (a.nodeType!=3) a = a.lastChild;
//                        //if (!a) a =
//                        //self.cursor_to(a, 0/*a.textContent.length*/);
////                        }
////                        e.preventDefault();
////                        e.stopPropagation();
//                    }
//                }
//            });

            this.element.on('keydown'+this.eventNamespace, function(e){
                var sel = window.getSelection();
                self.callChildren('keydown', [e, sel]);
                if (e.keyCode == 13){
                    var d = 5;
                }
                if (e.can_print === undefined){
                    //console.log(e.keyCode);
                    if (e.keyCode >=37 && e.keyCode <=40){

                    }else
                    if (e.keyCode >=112 && e.keyCode <=123){

                    }else
                    if (e.keyCode == 67 && e.ctrlKey){

                    }else
                    if (!_.size(self._children)){
                        // Текст пустой. Добавим абзац
                        if (!self.new_p) self.insert_new_p();
                    }else
                    /*if (!self._content.is(sel.anchorNode))*/{
                        e.stopPropagation();
                        e.preventDefault();
                    }
                }
            }).on('blur'+this.eventNamespace+' paste'+this.eventNamespace, function(e){
                var sel = window.getSelection();
                self.callChildren(e.type, [e, sel]);

            }).on('cut'+this.eventNamespace, function(e){
                e.preventDefault();
            });
        },

        _destroy: function(){
            this.call_program_hide();
            this.callChildren('save');
            $.boolive.AjaxWidget.prototype._destroy.call(this);
        },

        _save: function(){
            if (!$(this).hasClass('btn-frozen') && !$(this).hasClass('btn-disable') && this._changes_cnt){
                $(this).addClass('btn-frozen');
                this.callChildren('save');
            }
        },

        _onselect: function(){
            var sel = window.getSelection();
            if (sel.rangeCount>0){
                var container = this._content[0];
                var range = sel.getRangeAt(0);
                console.log(range);
                var start = range.startContainer;
                var end = range.endContainer;
                if (!range.collapsed){
                    // Поиск начального элемента
                    if (start.nodeType == 3){
                        // Если позиция в конце строки, то в выделение попадает следующий элемент
                        if (range.startOffset == start.textContent.length){
                            while (!start.nextSibling) start = start = start.parentNode;
                            start = start.nextSibling;
                        }
                    }else
                    if (start.nodeType == 1){
                        // Если есть следующий элемент, то он начало выделения. Иначе поиск следующего в родителе
                        if (range.startOffset < start.childNodes.length){
                            start = start.childNodes[range.startOffset];
                        }else{
                            while (!start.nextSibling) start = start.parentNode;
                            start = start.nextSibling;
                        }
                    }
                    while (start.parentNode != container) start = start.parentNode;

                    // Поиск конечного элемента
                    end = range.endContainer;
                    if (end.nodeType == 3){
                        // Если позиция в конце строки, то в выделение попадает следующий элемент
                        if (range.endOffset == 0){
                            while (!end.previousSibling) end = end.parentNode;
                            end = end.previousSibling;
                        }
                    }else
                    if (end.nodeType == 1){
                        // Если индекс элемента не нулевой, то он конец выделения. Иначе поиск предыдущего в родителе
                        if (range.endOffset > 0){
                            end = end.childNodes[range.endOffset-1];
                        }else{
                            while (!end.previousSibling) end = end.parentNode;
                            end = end.previousSibling;
                        }
                    }
                    while (end.parentNode != container) end = end.parentNode;

                }else{
                    if (start.nodeType == 1){
                        // Если есть следующий элемент, то он начало выделения. Иначе поиск следующего в родителе
                        if (range.startOffset < start.childNodes.length){
                            start = start.childNodes[range.startOffset];
                        }else{
                            while (!start.nextSibling) start = start.parentNode;
                            start = start.nextSibling;
                        }
                    }
                    while (start && start.parentNode != container) start = start.parentNode;
                    end = start;
                }
                // Получение списка объектов от начала до конца выделения
                var start_index = _.indexOf(container.childNodes, start);
                var end_index = _.indexOf(container.childNodes, end);
                var selected = [];
                if (start_index!=-1 && end_index!=-1){
                    var o;
                    var i = start_index;
                    for (i; i<=end_index; i++){
                        if (o = $(container.childNodes[i]).attr('data-o')){
                            selected.push(o);
                        }
                    }
                }
            }else{
                selected = [];
            }
            console.log(selected);
            this.callParents('setState', {selected: selected});
            return selected;
        },

        call_program_show: function(){
            if (this._hide){
                this._hide = false;
                $('.Admin').addClass('RichTextBG');
                //this._save_inteval = setInterval(function(){self._save()}, 10000);
            }
        },

        call_program_hide: function(){
            if (!this._hide){
                this._hide = true;
                //clearInterval(this._save_inteval);
                this._save();
                $('.Admin').removeClass('RichTextBG');
            }
        },

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

        cursor_to: function(element, pos){
            var sel = window.getSelection();
            var range = document.createRange();
            range.setStart(element, pos);
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange( range );
        },

        call_change: function(caller, object){ //before
            if (!this._changes[object]){
                this._buttons['save'].removeClass('btn-disable');
                this._changes[object] = true;
                this._changes_cnt++;
            }
        },

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

        call_getStyle: function(){
            var styles = this.callChildren('getStyle');
            if (styles === undefined){
                return [this._content.css(["padding-left", "padding-right"])];
            }else{
                return styles;
            }
        },

        call_setStyle: function(caller, style){
            if (!$.isEmptyObject(style)){
                // Для страницы отбираем paddings
                var pstyle = {'padding-left':0, 'padding-right':0};
                var n;
                for (n in pstyle){
                    if (n in style){
                        pstyle[n] = style[n];
                        delete style[n];
                    }else{
                        delete pstyle[n];
                    }
                }
                // Установка и сохранение стиля страницы
                this._content.css(pstyle);
                this.callServer('saveStyle', {
                    object: this.options.object,
                    saveStyle: pstyle
                });
                // Установка стилей подчиенных (сами определят кому)
                this.callChildren('setStyle', [style]);
            }
        },

        call_reloadChild: function(caller, data, child){
            this.load(child.element, 'replace', this.options.view+'/switch_views', data);
        }
    })
})(jQuery, _);