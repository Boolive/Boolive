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
        resize_rect: null,

        _create: function() {
            $.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;

            self.element.on('mousedown'+this.eventNamespace, function(e){
                self.element.attr('contentEditable', "false");
                self._select();
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
            });
        },

        _destroy: function(){
            this.call_setState({target: this, direct: 'children'}, {select:null}, {selected: true});
            $.boolive.AjaxWidget.prototype._destroy.call(this);
        },

        _make_resizer: function(){
            var self = this;
            var img_rect = self.element.offset();
            var width = self.element.width();
            var height = self.element.height();
            if (height==0) height = 0.01;
            var prop = width/height;
            var is_prop = true;
            var namespace = '.resizer'+Math.random();
            var dw, dh, w, h;
            if(self.resize_rect==null){
                self.resize_rect = $('<div> </div>').css({
                    position: 'absolute',
                   'z-index': 1000,
                   //'background-color': 'rgba(187, 219, 250, 0.5)',
                    top: img_rect.top,
                    border: '1px solid #006ad6',
                    width: width,
                    height: height,
                    left: img_rect.left
                });
               var resizetop =  $('<div id="resizetop"> </div>').appendTo(self.resize_rect);
               var resizebottom = $('<div id="resizebottom"> </div>').appendTo(self.resize_rect);
               var resizeleft =$('<div id="resizeleft"> </div>').appendTo(self.resize_rect);
               var resizeright = $('<div id="resizeright"> </div>').appendTo(self.resize_rect);
               var resizetopleft = $('<div id="resizetopleft"> </div>').appendTo(self.resize_rect);
               var resizetopright = $('<div id="resizetopright"> </div>').appendTo(self.resize_rect);
               var resizebottomleft = $('<div id="resizebottomleft"> </div>').appendTo(self.resize_rect);
               var resizebottomright = $('<div id="resizebottomright"> </div>').appendTo(self.resize_rect);
               var defaultlink = $('<span class="defaultlink">Сбросить размеры</span>').appendTo(self.resize_rect);
                $('body').append(self.resize_rect);
            }
            var DocumentMouseUp = function(e){
                $(this).off(namespace);

                self.element.width(self.resize_rect.width());
                self.element.height(self.resize_rect.height());
                self.callServer('saveStyle', {
                    saveStyle:{
                        width: self.resize_rect.width()+'px',
                        height:self.resize_rect.height()+'px'
                    },
                    object: self.options.object
                });
                //Чтоб разместить див на измененное изображение. Т.к. теперь он не удаляется
                img_rect = self.element.offset();
                width = self.element.width();
                height = self.element.height();
                self.resize_rect.css({
                  left: img_rect.left,
                  top: img_rect.top,
                  width: width,
                  height: height
                 });

                self._curent_action = false;
            };
            $(resizebottomright).on('mousedown'+this.eventNamespace, function(e){
                e.preventDefault();
                e.stopPropagation();
                pos = {x:e.pageX, y:e.pageY};
                //Изображение могло быть перед этим изменено не пропорционально
                width = self.element.width();
                height = self.element.height();
                prop = width/height;
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
                    self.resize_rect.css({
                        width: w+'px',
                        height: h+'px'
                    });

                }).on('mouseup'+namespace, function(e){
                    DocumentMouseUp.apply(this);
                });
            });
            $(resizebottomleft).on('mousedown'+this.eventNamespace, function(e){
                e.preventDefault();
                e.stopPropagation();
                pos = {x:e.pageX, y:e.pageY};
                //Изображение могло быть перед этим изменено не пропорционально
                width = self.element.width();
                height = self.element.height();
                prop = width/height;
                $(document).on('mousemove'+namespace, function(e){
                   dw = pos.x - e.pageX;
                   dh = e.pageY-pos.y;
                   if(width+dw>10){
                      var left = img_rect.left-dw;
                   }else{
                      var left = img_rect.left+width-10;
                   }
                   if (is_prop){
                      w = Math.max(10, width +  dw);
                      h = Math.round(w/prop);

                   }else{
                       w = Math.max(10, width+dw);
                       h = Math.max(10, height+dh);
                   }
                   self.resize_rect.css({
                       width: w+'px',
                       height: h+'px',
                       left: left
                   });
               }).on('mouseup'+namespace, function(e){
                  DocumentMouseUp.apply(this);
               });
           });
            $(resizetopleft).on('mousedown'+this.eventNamespace, function(e){
               e.preventDefault();
               e.stopPropagation();
               pos = {x:e.pageX, y:e.pageY};
                //Изображение могло быть перед этим изменено не пропорционально
               width = self.element.width();
               height = self.element.height();
               prop = width/height;
               $(document).on('mousemove'+namespace, function(e){
                  dw = pos.x - e.pageX;
                  dh = pos.y - e.pageY;
                  if(width+dw>10){
                     var left = img_rect.left-dw;
                  }else{
                     var left = img_rect.left+width-10;
                  }
                  if (is_prop){
                     w = Math.max(10, width +  dw);
                     h = Math.round(w/prop);
                  }else{
                      w = Math.max(10, width+dw);
                      h = Math.max(10, height+dh);
                  }
                  if(h>10){
                    var top = img_rect.top+height-h;
                  }else{
                    var top = img_rect.top+height-10;
                  }
                  self.resize_rect.css({
                      width: w+'px',
                      height: h+'px',
                      left: left,
                      top: top
                  });
              }).on('mouseup'+namespace, function(e){
                  DocumentMouseUp.apply(this);
              });
            });
            $(resizetopright).on('mousedown'+this.eventNamespace, function(e){
              e.preventDefault();
              e.stopPropagation();
              pos = {x:e.pageX, y:e.pageY};
              width = self.element.width();
              height = self.element.height();
              prop = width/height;
              $(document).on('mousemove'+namespace, function(e){
                 dw = e.pageX - pos.x ;
                 dh = pos.y - e.pageY;
                 if (is_prop){
                    w = Math.max(10, width +  dw);
                    h = Math.round(w/prop);

                 }else{
                     w = Math.max(10, width+dw);
                     h = Math.max(10, height+dh);
                 }
                 if(h>10){
                   var top = img_rect.top+height-h;
                 }else{
                     var top = img_rect.top+height-10;
                 }

                self.resize_rect.css({
                     width: w+'px',
                     height: h+'px',
                     top: top
                 });
             }).on('mouseup'+namespace, function(e){
               DocumentMouseUp.apply(this);
           });
         });
            $(resizetop).on('mousedown'+this.eventNamespace, function(e){
              e.preventDefault();
              e.stopPropagation();
              pos = {x:e.pageX, y:e.pageY};
              $(document).on('mousemove'+namespace, function(e){
                 dh = pos.y - e.pageY;
                 h = Math.max(10, height+dh);
                 if(h>10){
                   var top = img_rect.top-dh;
                 }else{
                   var top = img_rect.top+height-10;
                 }
                self.resize_rect.css({
                     height: h+'px',
                     top: top
                 });
             }).on('mouseup'+namespace, function(e){
               DocumentMouseUp.apply(this);
           });
         });
            $(resizebottom).on('mousedown'+this.eventNamespace, function(e){
                e.preventDefault();
                e.stopPropagation();
                pos = {x:e.pageX, y:e.pageY};
                $(document).on('mousemove'+namespace, function(e){
                   dh =  e.pageY - pos.y;
                   h = Math.max(10, height+dh);
                  self.resize_rect.css({
                       height: h+'px'
                   });
               }).on('mouseup'+namespace, function(e){
                 DocumentMouseUp.apply(this);
             });
           });
            $(resizeright).on('mousedown'+this.eventNamespace, function(e){
               e.preventDefault();
               e.stopPropagation();
               pos = {x:e.pageX, y:e.pageY};
                $(document).on('mousemove'+namespace, function(e){
                      dw =  e.pageX - pos.x;
                      w = Math.max(10, width+dw);
                      self.resize_rect.css({
                          width: w+'px'
                      });
                  }).on('mouseup'+namespace, function(e){
                    DocumentMouseUp.apply(this);
                  });
            });
            $(resizeleft).on('mousedown'+this.eventNamespace, function(e){
                   e.preventDefault();
                   e.stopPropagation();
                   pos = {x:e.pageX, y:e.pageY};
                  $(document).on('mousemove'+namespace, function(e){
                      dw = pos.x - e.pageX;
                      w = Math.max(10, width+dw);
                      if(w>10){
                         var left = img_rect.left-dw;
                        }else{
                         var left = img_rect.left+width-10;
                        }
                      self.resize_rect.css({
                          width: w+'px',
                          left: left
                      });
                  }).on('mouseup'+namespace, function(e){
                    DocumentMouseUp.apply(this);
                  });
            });
            //Сброс размеров
            $(defaultlink).on('mousedown'+this.eventNamespace, function(e){
                self.resize_rect.css({
                  width: self.element.get(0).naturalWidth,
                  height: self.element.get(0).naturalHeight
                });
                $(document).on('mouseup'+namespace, function(e){
                     DocumentMouseUp.apply(this);
                });
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
                this._make_resizer();
            }
        },

        /**
         * Отмена выделения (фокуса) изображения
         * @param state
         * @param changes
         */
        call_setState: function(caller, state, changes){ //after
            if ($.isPlainObject(changes) && 'selected' in changes && _.indexOf(state.selected, this.options.object)==-1){
                if(this.resize_rect!=null){
                    //Удалим еще контролы для ресайза
                    this.resize_rect.remove();
                    this.resize_rect = null;
                }
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
                    //Если есть ресайзер его тоже подвинем
                    $(this.resize_rect).remove();
                    this.resize_rect=null;
                    this._make_resizer();
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