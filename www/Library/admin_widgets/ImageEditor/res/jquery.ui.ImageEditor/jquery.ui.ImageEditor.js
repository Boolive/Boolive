/**
 * Виджет редактора изображения
 * Query UI widget
 * Copyright 2013 (C) Boolive
 */
(function($, _, document) {
    $.widget("boolive.ImageEditor", $.boolive.AjaxWidget, {
        _value: '',
        _mouse_action: null, // Ожидаемое действие мышки
        _curent_action: null, // Текущее действие
        _resize_rect: null,

        _create: function() {
            $.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;

            self.element.on('mousedown'+this.eventNamespace, function(e){
                if (self._mouse_action == 'resize'){
                    self._make_selection(e);
                }else{
                    self._select();
                }
                self.element.attr('contentEditable', "false");
                e.preventDefault();
                e.stopPropagation();
            }).on('mouseup'+this.eventNamespace, function(e){
                e.preventDefault();
                self.element.attr('contentEditable', "true");
//                var sel = window.getSelection();
//                sel.removeAllRanges();
            }).on('click', function(e){
                e.preventDefault();
                e.stopPropagation();
            }).on('mousemove', function(e){

                if (self.element.hasClass('selected')){
                    if (!self._curent_action){
                        // Проверка попадания курсора в область ресайза
                        var img_rect = self.element.offset();
                        var rect = {
                            top: img_rect.top + self.element.height() - 20,
                            right: img_rect.left + self.element.width(),
                            bottom: img_rect.top + self.element.height(),
                            left: img_rect.left + self.element.width() - 20
                        };
                        if (self._isItersection(rect, {x: e.pageX, y: e.pageY})){
                            // Доступен ресайз
                            self.element.addClass('resizing');
                            self._mouse_action = 'resize';
                        }else{
                            self.element.removeClass('resizing');
                            self._mouse_action = false;
                        }
                    }
                }else{
                    self.element.removeClass('resizing');
                    self._mouse_action = false;
                }
            });
        },

        _make_selection: function(e){
            var self = this;
            var img_rect = self.element.offset();
            var width = self.element.width();
            var height = self.element.height();
            var pos = {x:e.pageX, y:e.pageY};
            if (height==0) height = 0.01;
            var prop = width/height;
            var is_prop = true;
            var namespace = '.resizer'+Math.random();
            var dw, dh, w, h;
            var resize_rect = $('<div> </div>').css({
                position: 'absolute',
                'z-index': 1000,
                //border: '1px solid #000000',
                'background-color': 'rgba(187, 219, 250, 0.5)',
                top: img_rect.top,
                width: width,
                height: height,
                left: img_rect.left,
                cursor: 'se-resize'
            });
            $('body').append(resize_rect);
            $(document).on('mousemove'+namespace, function(e){
                dw = e.pageX - pos.x;
                dh = e.pageY - pos.y;
                if (is_prop){
                    w = Math.max(10, width + Math.min(dh, dw));
                    h = Math.round(w/prop);
                }else{
                    w = Math.max(10, width+dw);
                    h = Math.max(10, height+dh);
                }
                resize_rect.css({
                    width: w+'px',
                    height: h+'px'
                });
            }).on('mouseup'+namespace, function(e){
                $(this).off(namespace);
                resize_rect.hide();
                self.element.width(resize_rect.width());
                self.element.height(resize_rect.height());

                self.callServer('saveStyle', {
                        object: self.options.object,
                        saveStyle: {
                            width: resize_rect.width()+'px',
                            height: resize_rect.height()+'px'
                        }
                    }
                );

                self._curent_action = false;
                self._select();
            });
        },

        _isItersection: function(rect, point){
             return rect.left<=point.x && rect.right>=point.x && rect.top<=point.y && rect.bottom>=point.y;
        },

        /**
         * В режиме contenteditable своё событие клавитауры не получить,
         * поэтому реагируем на событие родителя
         * @param e Событие
         * @param sel Выделение
         */
        call_keydown: function(caller, e, sel){ //afetr
//            if (this._isInSelection(sel)){
//                e.preventDefault();
//                e.stopPropagation();
//            }
        },

        call_keyup: function(caller, e, selection){ //after
            if (this._isInSelection(selection)){
                this._select();
            }
        },

        /**
         * Выделение (фокус) изображения
         * @private
         */
        _select: function(){
            this.element.attr('contentEditable', "true");
            if (!this.element.hasClass('selected')){
                this.element.addClass('selected');
                this.callParents('setState', [{selected:  this.options.object}]);
                var sel = window.getSelection();
                var range = document.createRange();
                range.setStart(this.element.parent()[0], this.element.index());
                range.collapse(true);
                sel.removeAllRanges();
                sel.addRange( range );
            }
        },

        /**
         * Отмена выделения (фокуса) изображения
         * @param state
         * @param changes
         */
        call_setState: function(caller, state, changes){ //after
            if ($.isPlainObject(changes) && 'selected' in changes && _.indexOf(state.selected, this.options.object)==-1){
                //state.selected!=this.options.object){
                this.element.removeClass('selected');
                this.element.removeClass('resizing');
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
         * Проверка, задевает ли текущее выделение элемент
         * @param selection
         * @return boolean
         */
        _isInSelection: function(selection){
            if (selection.anchorNode.nodeType == 1 && selection.anchorOffset && selection.isCollapsed){
                var node = selection.anchorNode.childNodes[selection.anchorOffset];
                return this.element.is(node);
            }
            return false;
        }
    })
})(jQuery, _, document);