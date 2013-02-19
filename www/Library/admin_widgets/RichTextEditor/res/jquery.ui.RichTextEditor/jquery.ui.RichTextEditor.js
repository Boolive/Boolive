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

            this._buttons['save'].on('click', function(e){
                e.preventDefault();
                e.stopPropagation();
                self._save();
                self._content.focus();
            });
            this.element.on('click', function(e){
                if ($(e.target).is(self._content)){

                    if (!_.size(self._children)){
//                        var sel = window.getSelection();
//                        var range = document.createRange();
//                        var p = self._content[0];
//                        range.setStart(p, 0);
//                        range.collapse(true);
//                        sel.removeAllRanges();
//                        sel.addRange( range );
                        self.cursor_to(self._content.children()[0], 0);
                    }else{
                        var sel = window.getSelection();
//                        if (sel.anchorNode.nodeType==1){
//                            self.cursor_to(sel.anchorNode.lastChild, 0);
//                        }else{
//                        var x = self._content.children();
//                        var a = self._content.children()[0].lastChild;
                        //while (a.nodeType!=3) a = a.lastChild;
                        //if (!a) a =
                        //self.cursor_to(a, 0/*a.textContent.length*/);
//                        }
//                        e.preventDefault();
//                        e.stopPropagation();
                    }
                }
            });

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
            }).on('blur'+this.eventNamespace+' keyup'+this.eventNamespace+' paste'+this.eventNamespace, function(e){
                var sel = window.getSelection();
                self.callChildren(e.type, [e, sel]);

            }).on('mouseup'+this.eventNamespace, function(e){
                self._onselect();
            });
        },

        _destroy: function(){
            this.call_program_hide();
            //self.callChildren('save');
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
            // 1) Поиск общего родителя начала и конца области выделения
            // 2) Индексы начала и конца в родителе
            // 3) Получение списка объектов от начала до конца выделения
            // Выбор всех родителей от начальной позиции курсора
            var common_index = -1;
            var node0 = sel.anchorNode;
            var nodes0 = [];
            while (node0 && node0.nodeType==3) node0 = node0.parentNode;
            while (node0 && !this.element.is(node0)){
                nodes0.unshift(node0);
                node0 = node0.parentNode;
            }
            // Выбор родителей от конечной позиции курсора пока не будет найден общий родитель с node0
            var node1 = sel.focusNode;
            var nodes1 = [];
            while (node1 && node1.nodeType==3) node1 = node1.parentNode;
            while (node1 && !this.element.is(node1) && common_index==-1){
                nodes1.unshift(node1);
                common_index = _.indexOf(nodes0, node1);
                node1 = node1.parentNode;
            }
            // Получение элементов начала и конца выделения в общем родителе
            if (common_index!=-1){
                node0 = (nodes0.length>common_index+1)? nodes0[common_index+1] : nodes0[common_index];
                node1 = (nodes1.length>1)? nodes1[1] : nodes1[0];
//                console.log(node0);
//                console.log(node1);
            }
            console.log(sel);
//            console.log(nodes0);
//            console.log(nodes1);
            //this.callChildren('textSelect', [e, sel]);
        },

        call_program_show: function(){
            var self = this;
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
        }
    })
})(jQuery, _);