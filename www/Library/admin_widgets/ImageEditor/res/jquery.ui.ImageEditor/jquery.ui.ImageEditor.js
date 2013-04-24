/**
 * Виджет редактора изображения
 * Query UI widget
 * Copyright 2013 (C) Boolive
 */
(function($, _, document) {
    $.widget("boolive.ImageEditor", $.boolive.Widget, {
        _value: '',
        _resize_rect: null,
        _properties: null,

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
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
            $.boolive.Widget.prototype._destroy.call(this);
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
                var dw, dh, w, h, left, top, pos;
                var offset = {
                    left: parseInt(self.element.css('padding-left')),
                    top: parseInt(self.element.css('padding-top'))
                };

                self._resize_rect = $('<div class="resize"> </div>').css({
                    position:'absolute',
                    'z-index':1000,
                    //'background-color': 'rgba(187, 219, 250, 0.5)',
                    top:img_rect.top + offset.top,
                    border:'1px solid #006ad6',
                    width:width,
                    height:height,
                    left:img_rect.left + offset.left
                });
                var resizetop = $('<div class="top"> </div>').appendTo(self._resize_rect);
                var resizebottom = $('<div class="bottom"> </div>').appendTo(self._resize_rect);
                var resizeleft = $('<div class="left"> </div>').appendTo(self._resize_rect);
                var resizeright = $('<div class="right"> </div>').appendTo(self._resize_rect);
                var resizetopleft = $('<div class="topleft"> </div>').appendTo(self._resize_rect);
                var resizetopright = $('<div class="topright"> </div>').appendTo(self._resize_rect);
                var resizebottomleft = $('<div class="bottomleft"> </div>').appendTo(self._resize_rect);
                var resizebottomright = $('<div class="bottomright"> </div>').appendTo(self._resize_rect);
                var defaultlink = $('<span class="defaultlink"></span>').appendTo(self._resize_rect);
                var upload = $('<div class="upload"></div>').appendTo(self._resize_rect);
                var form = $('<form enctype="multipart/form-data" method="post"><input type="file" name="attrib[file]"></form>').appendTo(upload);
                var submit_message = $('<span class="submit-message"></span>').appendTo(self._resize_rect);
                $('body').append(self._resize_rect);

                var DocumentMouseUp = function (e) {
                    $(document).off(namespace);

                    self.element.width(self._resize_rect.width());
                    self.element.height(self._resize_rect.height());
                    self.call_setProperties({target: self._resize_rect, direct:'none'}, {style:{
                        width:self._resize_rect.width() + 'px',
                        height:self._resize_rect.height() + 'px'
                    }});
                    //Чтоб разместить див на измененное изображение. Т.к. теперь он не удаляется
                    img_rect = self.element.offset();
                    width = self.element.width();
                    height = self.element.height();
                    self._resize_rect.css({
                        left:img_rect.left + offset.left,
                        top:img_rect.top + offset.top,
                        width:width,
                        height:height
                    });
                };
                self._resize_rect.on('click mousedown moseup', function(e){
                    e.stopPropagation();
                    e.preventDefault();
                });
                resizebottomright.on('mousedown' + this.eventNamespace, function (e) {
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
                resizebottomleft.on('mousedown' + this.eventNamespace, function (e) {
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
                            left = img_rect.left - dw;
                        } else {
                            left = img_rect.left + width - 10;
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
                            left:left + offset.left
                        });
                    }).on('mouseup' + namespace, function (e) {
                            DocumentMouseUp.apply(this, [e]);
                        });
                });
                resizetopleft.on('mousedown' + this.eventNamespace, function (e) {
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
                            left = img_rect.left - dw;
                        } else {
                            left = img_rect.left + width - 10;
                        }
                        if (is_prop) {
                            w = Math.max(10, width + dw);
                            h = Math.round(w / prop);
                        } else {
                            w = Math.max(10, width + dw);
                            h = Math.max(10, height + dh);
                        }
                        if (h > 10) {
                            top = img_rect.top + height - h;
                        } else {
                            top = img_rect.top + height - 10;
                        }
                        self._resize_rect.css({
                            width:w + 'px',
                            height:h + 'px',
                            left:left + offset.left,
                            top:top + offset.top
                        });
                    }).on('mouseup' + namespace, function (e) {
                            DocumentMouseUp.apply(this, [e]);
                        });
                });
                resizetopright.on('mousedown' + this.eventNamespace, function (e) {
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
                            top = img_rect.top + height - h;
                        } else {
                            top = img_rect.top + height - 10;
                        }

                        self._resize_rect.css({
                            width:w + 'px',
                            height:h + 'px',
                            top:top + offset.top
                        });
                    }).on('mouseup' + namespace, function (e) {
                            DocumentMouseUp.apply(this, [e]);
                        });
                });
                resizetop.on('mousedown' + this.eventNamespace, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    pos = {x:e.pageX, y:e.pageY};
                    $(document).on('mousemove' + namespace,function (e) {
                        dh = pos.y - e.pageY;
                        h = Math.max(10, height + dh);
                        if (h > 10) {
                            top = img_rect.top - dh;
                        } else {
                            top = img_rect.top + height - 10;
                        }
                        self._resize_rect.css({
                            height:h + 'px',
                            top:top + offset.top
                        });
                    }).on('mouseup' + namespace, function (e) {
                        DocumentMouseUp.apply(this, [e]);
                    });
                });
                resizebottom.on('mousedown' + this.eventNamespace, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    pos = {x:e.pageX, y:e.pageY};
                    $(document).on('mousemove' + namespace, function(e) {
                        dh = e.pageY - pos.y;
                        h = Math.max(10, height + dh);
                        self._resize_rect.css({
                            height:h + 'px'
                        });
                    }).on('mouseup' + namespace, function (e) {
                        DocumentMouseUp.apply(this, [e]);
                    });
                });
                resizeright.on('mousedown' + this.eventNamespace, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    pos = {x:e.pageX, y:e.pageY};
                    $(document).on('mousemove' + namespace, function(e) {
                        dw = e.pageX - pos.x;
                        w = Math.max(10, width + dw);
                        self._resize_rect.css({
                            width:w + 'px'
                        });
                    }).on('mouseup' + namespace, function (e) {
                            DocumentMouseUp.apply(this, [e]);
                        });
                });
                resizeleft.on('mousedown' + this.eventNamespace, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    pos = {x:e.pageX, y:e.pageY};
                    $(document).on('mousemove' + namespace,function(e) {
                        dw = pos.x - e.pageX;
                        w = Math.max(10, width + dw);
                        if (w > 10) {
                            left = img_rect.left - dw;
                        } else {
                            left = img_rect.left + width - 10;
                        }
                        self._resize_rect.css({
                            width:w + 'px',
                            left:left + offset.left
                        });
                    }).on('mouseup' + namespace, function(e) {
                            DocumentMouseUp.apply(this, [e]);
                        });
                });
                //Сброс размеров
                defaultlink.on('mousedown' + this.eventNamespace, function() {
                    self._resize_rect.css({
                        width:self.element.get(0).naturalWidth,
                        height:self.element.get(0).naturalHeight
                    });
                    $(document).on('mouseup' + namespace, function(e) {
                        DocumentMouseUp.apply(this, [e]);
                    });
                });
                upload.on('click', function(e){
                    // Только действие по-умолчанию - открытие диалога выбора файла для загрузки
                    // Событие не передаётся родительским элементам
                    e.stopPropagation();
                });
                //Загрузка другого изображения
                form.on('change', '[type=file]', function () {
                    form.ajaxSubmit({
                        url:'/',
                        type:'post',
                        data:{
                            object:self.options.object,
                            direct:self.options.view,
                            call:'save',
                            attrib:{file:this.value}
                        },
                        dataType:'json',
                        success:function (responseText, statusText, xhr, $form) {
                            if (responseText.out.error) {
                                for (var e in responseText.out.error) {
                                    submit_message.css('opacity', 1);
                                    submit_message.css('display', 'block');
                                    submit_message.text(responseText.out.error.value);
                                    submit_message.animate({
                                        opacity:0
                                    }, 5000);
                                }
                            } else {
                                self.element.attr('src', responseText.out.attrib.file);
                            }
                        }
                    });

                })
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
         * Возвращение свойств объекта
         * @return {*}
         */
        call_getProperties: function(){
            if (this.element.hasClass('selected')){
                if (this._properties == null){
//                    var properties = this.element.css(["margin-top", "margin-right", "margin-bottom", "margin-left",
//                        "padding-top", "padding-right", "padding-bottom", "padding-left",
//                        "position", "display", "top", "right", "bottom", "left", "width", "height"
//                    ]);
//                    if (properties['display'] == 'block' && properties['position']!='absolute'){
//                        var pr = parseInt(this.element.parent().css('padding-right'));
//                        if (this.element.parent().innerWidth() - this.element.outerWidth(true) - this.element.position().left - pr <= 2){
//                            properties['margin-left'] = 'auto';
//                        }else
//                        if (this.element.parent().innerWidth() - this.element.outerWidth(true) - this.element.position().left*2 <= 2){
//                            properties['margin-left'] = 'auto';
//                            properties['margin-right'] = 'auto';
//                        }else{
//                            properties['margin-right'] = 'auto';
//                        }
//                    }
                    this._properties = {
                        element: this.element,
                        tag: 'img'
                    };
                }
                return this._properties;
            }
        },


        /**
         * Установка свойств объекту
         * @param caller
         * @param properties
         */
        call_setProperties: function(caller, properties){
            if (this.element.hasClass('selected')){
                if (_.isObject(properties)){
                    if (_.isObject(properties.style)) this.element.css(properties.style);
                    this._properties = _.extend(this._properties, properties);
                    this.callServer('saveProperties', {
                        object: this.options.object,
                        saveProperties: properties
                    });
                    if (caller.target != this._resize_rect){
                        //Если есть ресайзер его тоже подвинем
                        $(this._resize_rect).remove();
                        this._resize_rect=null;
                        this._make_resizer();
                    }
                }
            }
        }
    })
})(jQuery, _, document);