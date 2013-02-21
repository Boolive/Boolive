/**
 * Виджет редактора изображения
 * Query UI widget
 * Copyright 2013 (C) Boolive
 */
(function($, _, document) {
    $.widget("boolive.ImageEditor", $.boolive.AjaxWidget, {
        _value: '',
        _resize_rect: null,

        _create: function() {
            $.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;

            self.element.on('click', function(e){
                // Коррекция области выделения (некоторые браузеры не выделяют картинку по клику)
                var sel = window.getSelection();
                var range = document.createRange();
                var index = _.indexOf(self.element[0].parentNode.childNodes, self.element[0]);
                range.setStart(self.element[0].parentNode, index);
                range.setEnd(self.element[0].parentNode, index+1);
                range.collapse(true);
                sel.removeAllRanges();
                sel.addRange( range );
                // Отмена клика в редакторе, иначе сбросится выделение
                e.preventDefault();
                e.stopPropagation();
            }).on('onresizestart', function(e){
                // Отмена встроенного изменения размера картинки в IE
                e.returnValue = false;
            }).on('mousedown', function(e){
                // Для отмены системного drag-and-drop
                e.preventDefault();
            });
        },

        _destroy: function(){
            this.call_setState({target: this, direct: 'children'}, {select:null}, {selected: true});
            $.boolive.AjaxWidget.prototype._destroy.call(this);
        },

        /**
         * Создание элемента для изменения размера картинки
         * @private
         */
        _make_resizer: function(){
            var self = this;
            if (self._resize_rect == null) {
                var img_rect = self.element.offset();
                var width = self.element.width();
                var height = self.element.height();
                if (height == 0) height = 0.01;
                var prop = width / height;
                var is_prop = true;
                var namespace = '.resizer' + Math.random();
                var dw, dh, w, h;
                var pos;
                self._resize_rect = $('<div> </div>').css({
                    position:'absolute',
                    'z-index':1000,
                    //'background-color': 'rgba(187, 219, 250, 0.5)',
                    top:img_rect.top,
                    border:'1px solid #006ad6',
                    width:width,
                    height:height,
                    left:img_rect.left
                });
                var resizetop = $('<div id="resizetop"> </div>').appendTo(self._resize_rect);
                var resizebottom = $('<div id="resizebottom"> </div>').appendTo(self._resize_rect);
                var resizeleft = $('<div id="resizeleft"> </div>').appendTo(self._resize_rect);
                var resizeright = $('<div id="resizeright"> </div>').appendTo(self._resize_rect);
                var resizetopleft = $('<div id="resizetopleft"> </div>').appendTo(self._resize_rect);
                var resizetopright = $('<div id="resizetopright"> </div>').appendTo(self._resize_rect);
                var resizebottomleft = $('<div id="resizebottomleft"> </div>').appendTo(self._resize_rect);
                var resizebottomright = $('<div id="resizebottomright"> </div>').appendTo(self._resize_rect);
                var defaultlink = $('<span class="defaultlink">Сбросить размеры</span>').appendTo(self._resize_rect);
                $('body').append(self._resize_rect);

                var DocumentMouseUp = function (e) {
                    $(document).off(namespace);

                    self.element.width(self._resize_rect.width());
                    self.element.height(self._resize_rect.height());
                    self.callServer('saveStyle', {
                        saveStyle:{
                            width:self._resize_rect.width() + 'px',
                            height:self._resize_rect.height() + 'px'
                        },
                        object:self.options.object
                    });
                    //Чтоб разместить див на измененное изображение. Т.к. теперь он не удаляется
                    img_rect = self.element.offset();
                    width = self.element.width();
                    height = self.element.height();
                    self._resize_rect.css({
                        left:img_rect.left,
                        top:img_rect.top,
                        width:width,
                        height:height
                    });
                };
                self._resize_rect.on('click mousedown moseup', function(e){
                    e.stopPropagation();
                    e.preventDefault();
                });
                $(resizebottomright).on('mousedown' + this.eventNamespace, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    pos = {x:e.pageX, y:e.pageY};
                    //Изображение могло быть перед этим изменено не пропорционально
                    width = self.element.width();
                    height = self.element.height();
                    prop = width / height;
                    $(document).on('mousemove' + namespace,function (e) {
                        dw = e.pageX - pos.x;
                        dh = e.pageY - pos.y;
                        if (is_prop) {
                            w = Math.max(10, width + Math.min(dh, dw));
                            h = Math.round(w / prop);
                        } else {
                            w = Math.max(10, width + dw);
                            h = Math.max(10, height + dh);
                        }
                        self._resize_rect.css({
                            width:w + 'px',
                            height:h + 'px'
                        });

                    }).on('mouseup' + namespace, function (e) {
                            DocumentMouseUp.apply(this, [e]);
                        });
                });
                $(resizebottomleft).on('mousedown' + this.eventNamespace, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    pos = {x:e.pageX, y:e.pageY};
                    //Изображение могло быть перед этим изменено не пропорционально
                    width = self.element.width();
                    height = self.element.height();
                    prop = width / height;
                    $(document).on('mousemove' + namespace,function (e) {
                        dw = pos.x - e.pageX;
                        dh = e.pageY - pos.y;
                        if (width + dw > 10) {
                            var left = img_rect.left - dw;
                        } else {
                            var left = img_rect.left + width - 10;
                        }
                        if (is_prop) {
                            w = Math.max(10, width + dw);
                            h = Math.round(w / prop);

                        } else {
                            w = Math.max(10, width + dw);
                            h = Math.max(10, height + dh);
                        }
                        self._resize_rect.css({
                            width:w + 'px',
                            height:h + 'px',
                            left:left
                        });
                    }).on('mouseup' + namespace, function (e) {
                            DocumentMouseUp.apply(this, [e]);
                        });
                });
                $(resizetopleft).on('mousedown' + this.eventNamespace, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    pos = {x:e.pageX, y:e.pageY};
                    //Изображение могло быть перед этим изменено не пропорционально
                    width = self.element.width();
                    height = self.element.height();
                    prop = width / height;
                    $(document).on('mousemove' + namespace,function (e) {
                        dw = pos.x - e.pageX;
                        dh = pos.y - e.pageY;
                        if (width + dw > 10) {
                            var left = img_rect.left - dw;
                        } else {
                            var left = img_rect.left + width - 10;
                        }
                        if (is_prop) {
                            w = Math.max(10, width + dw);
                            h = Math.round(w / prop);
                        } else {
                            w = Math.max(10, width + dw);
                            h = Math.max(10, height + dh);
                        }
                        if (h > 10) {
                            var top = img_rect.top + height - h;
                        } else {
                            var top = img_rect.top + height - 10;
                        }
                        self._resize_rect.css({
                            width:w + 'px',
                            height:h + 'px',
                            left:left,
                            top:top
                        });
                    }).on('mouseup' + namespace, function (e) {
                            DocumentMouseUp.apply(this, [e]);
                        });
                });
                $(resizetopright).on('mousedown' + this.eventNamespace, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    pos = {x:e.pageX, y:e.pageY};
                    width = self.element.width();
                    height = self.element.height();
                    prop = width / height;
                    $(document).on('mousemove' + namespace,function (e) {
                        dw = e.pageX - pos.x;
                        dh = pos.y - e.pageY;
                        if (is_prop) {
                            w = Math.max(10, width + dw);
                            h = Math.round(w / prop);

                        } else {
                            w = Math.max(10, width + dw);
                            h = Math.max(10, height + dh);
                        }
                        if (h > 10) {
                            var top = img_rect.top + height - h;
                        } else {
                            var top = img_rect.top + height - 10;
                        }

                        self._resize_rect.css({
                            width:w + 'px',
                            height:h + 'px',
                            top:top
                        });
                    }).on('mouseup' + namespace, function (e) {
                            DocumentMouseUp.apply(this, [e]);
                        });
                });
                $(resizetop).on('mousedown' + this.eventNamespace, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    pos = {x:e.pageX, y:e.pageY};
                    $(document).on('mousemove' + namespace,function (e) {
                        dh = pos.y - e.pageY;
                        h = Math.max(10, height + dh);
                        if (h > 10) {
                            var top = img_rect.top - dh;
                        } else {
                            var top = img_rect.top + height - 10;
                        }
                        self._resize_rect.css({
                            height:h + 'px',
                            top:top
                        });
                    }).on('mouseup' + namespace, function (e) {
                        DocumentMouseUp.apply(this, [e]);
                    });
                });
                $(resizebottom).on('mousedown' + this.eventNamespace, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    pos = {x:e.pageX, y:e.pageY};
                    $(document).on('mousemove' + namespace,function (e) {
                        dh = e.pageY - pos.y;
                        h = Math.max(10, height + dh);
                        self._resize_rect.css({
                            height:h + 'px'
                        });
                    }).on('mouseup' + namespace, function (e) {
                        DocumentMouseUp.apply(this, [e]);
                    });
                });
                $(resizeright).on('mousedown' + this.eventNamespace, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    pos = {x:e.pageX, y:e.pageY};
                    $(document).on('mousemove' + namespace, function (e) {
                        dw = e.pageX - pos.x;
                        w = Math.max(10, width + dw);
                        self._resize_rect.css({
                            width:w + 'px'
                        });
                    }).on('mouseup' + namespace, function (e) {
                        DocumentMouseUp.apply(this, [e]);
                    });
                });
                $(resizeleft).on('mousedown' + this.eventNamespace, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    pos = {x:e.pageX, y:e.pageY};
                    $(document).on('mousemove' + namespace,function (e) {
                        dw = pos.x - e.pageX;
                        w = Math.max(10, width + dw);
                        if (w > 10) {
                            var left = img_rect.left - dw;
                        } else {
                            var left = img_rect.left + width - 10;
                        }
                        self._resize_rect.css({
                            width:w + 'px',
                            left:left
                        });
                    }).on('mouseup' + namespace, function (e) {
                        DocumentMouseUp.apply(this, [e]);
                    });
                });
                //Сброс размеров
                $(defaultlink).on('mousedown' + this.eventNamespace, function (e) {
                    self._resize_rect.css({
                        width:self.element.get(0).naturalWidth,
                        height:self.element.get(0).naturalHeight
                    });
                    $(document).on('mouseup' + namespace, function (e) {
                        DocumentMouseUp.apply(this);
                    });
                });
            }
        },

        /**
         * В режиме contenteditable своё событие клавитауры не получить,
         * поэтому реагируем на событие родителя
         */
        call_keydown: function(caller, e, sel){
        },

        call_keyup: function(caller, e, sel){

        },

        /**
         * Выделение и отмена выделения
         */
        call_setState: function(caller, state, changes){ //after
            if ($.isPlainObject(changes) && 'selected' in changes){
                var select = _.indexOf(state.selected, this.options.object)!=-1;
                var is_selected = this.element.hasClass('selected');
                if (is_selected && !select){
                    if(this._resize_rect!=null){
                        //Удалим еще контролы для ресайза
                        this._resize_rect.remove();
                        this._resize_rect = null;
                    }
                    this.element.removeClass('selected');
                    this.element.removeClass('resizing');
                }else
                if (!is_selected && select){
                    this.element.addClass('selected');
                    this._make_resizer();
                }
            }
        },

        /**
         * Возвращение стиля
         * @return {*}
         */
        call_getStyle: function(){
            if (this.element.hasClass('selected')){
                return this.element.css(["margin-left", "margin-right", "text-indent"]);
            }
        },

        /**
         * Установка стиля
         * @param caller
         * @param style
         */
        call_setStyle: function(caller, style){
            if (this.element.hasClass('selected')){
                if (!$.isEmptyObject(style)){
                    this.element.css(style);
                    this.callServer('saveStyle', {
                        object: this.options.object,
                        saveStyle: style
                    });
                    //Если есть ресайзер его тоже подвинем
                    $(this._resize_rect).remove();
                    this._resize_rect=null;
                    this._make_resizer();
                }
            }
        }
    })
})(jQuery, _, document);