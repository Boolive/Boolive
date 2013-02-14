/**
 * Виджет линейки отступов форматированного текста
 * Query UI widget
 * Copyright 2013 (C) Boolive
 */
(function($, undefined) {
	$.widget("boolive.RichTextRule", $.boolive.AjaxWidget, {

        _model: {
            'width': 0,
            'padding-left': 30,
            'padding-right': 30,
            'margin-left': 0,
            'margin-right': 0,
            'text-indent': 0
        },
        _controls: {
            nums: null,
            pleft: null,
            pright: null,
            mleft: null,
            mright: null,
            client: null
        },

        _round: 1,

        _create: function() {
			$.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;
            this._controls.nums = this.element.find('.nums:first');
            this._controls.pleft = this.element.find('.pleft:first');
            this._controls.pright = this.element.find('.pright:first');
            this._controls.mleft = this.element.find('.mleft:first');
            this._controls.mright = this.element.find('.mright:first');
            this._controls.tindent = this.element.find('.tindent:first');
            this._controls.client = this.element.find('.client:first');
            this._model.width = this._controls.client.outerWidth(true);
            this._make_nums();
            //this._show();
            this.gettingStyle();
            this.element.click(function(e){
                e.preventDefault();
                e.stopPropagation();
            });
            this._controls.mleft.on('mousedown', function(e){
                var start = self._model['margin-left'];
                self._make_resizer(e, $(this).offset().left - 1, $(this).css('border-bottom-color'), function(dx){
                    self.set_mleft(dx + start);
                    self._show();
                    return self._model['margin-left'] - start;
                });
                e.preventDefault();
                e.stopPropagation();
            });

            this._controls.mright.on('mousedown', function(e){
                var start = self._model['margin-right'];
                self._make_resizer(e, $(this).offset().left - 1, $(this).css('border-bottom-color'), function(dx){
                    self.set_mright(-dx + start);
                    self._show();
                    return -self._model['margin-right'] + start;
                });
                e.preventDefault();
                e.stopPropagation();
            });

            this._controls.tindent.on('mousedown', function(e){
                var start = self._model['text-indent'];
                self._make_resizer(e, $(this).offset().left -1, $(this).css('border-top-color'), function(dx){
                    self.set_tindent(dx + start);
                    self._show();
                    return self._model['text-indent'] - start;
                });
                e.preventDefault();
                e.stopPropagation();
            });

            this._controls.pleft.on('mousedown', function(e){
                var start = self._model['padding-left'];
                self._make_resizer(e, $(this).offset().left + start, '#CCC', function(dx){
                    self.set_pleft(dx + start);
                    self._show();
                    return self._model['padding-left'] - start;
                });
                e.preventDefault();
                e.stopPropagation();
            });

            this._controls.pright.on('mousedown', function(e){
                var start = self._model['padding-right'];
                self._make_resizer(e, $(this).offset().left - 8, '#CCC', function(dx){
                    self.set_pright(-dx + start);
                    self._show();
                    return -self._model['padding-right'] + start;
                });
                e.preventDefault();
                e.stopPropagation();
            });
        },

         _make_resizer: function(e, pos, color, setCallback){
             var self = this;
             var namespace = '.resizer'+Math.random();
             var start = e.pageX;
             var rect = this.element.offset();
             var resize_rect = $('<div></div>').css({
                 'position': 'fixed',
                 'z-index': 1000,
                 'border-left': '1px dashed '+color,
                 'width': '0px',
                 'bottom': 0,
                 'margin-left': '9px',
                 'margin-right': '9px',
                 'top': rect.top - $(document).scrollTop()+16+'px',
                 'left': pos+'px'
            });

            $('body').prepend(resize_rect);
            $(document).on('mousemove'+namespace, function(e){
                if (e.ctrlKey){
                    self._round = 1;
                }else{
                    self._round = 10;
                }
                var dx = e.pageX - start;
                if (typeof setCallback == 'function'){
                    dx = setCallback(dx);
                }
                resize_rect.css('left', dx + pos+'px');
            }).on('mouseup'+namespace, function(e){
                $(this).off(namespace);
                resize_rect.hide();
                self.sendingStyle();
            });
        },

        /**
         * Отображение модели
         * @private
         */
        _show: function(){
            this._controls.client.css({
                'margin-left': this._model['padding-left'],
                'margin-right': this._model['padding-right']
            });
            this._controls.pleft.css('width', this._model['padding-left']).attr('title', 'Поле слева '+this._model['padding-left']+'px');
            this._controls.pright.css('width', this._model['padding-right']).attr('title', 'Поле справа '+this._model['padding-right']+'px');
            this._controls.nums.css('left', this._model['padding-left']-1000);
            this._controls.mleft.css('left', this._model['padding-left'] + this._model['margin-left']).attr('title', 'Отступ слева '+this._model['margin-left']+'px');
            this._controls.mright.css('right', this._model['padding-right'] + this._model['margin-right']).attr('title', 'Отступ справа '+this._model['margin-right']+'px');
            this._controls.tindent.css('left', this._model['padding-left'] + this._model['margin-left'] + this._model['text-indent']).attr('title', 'Отступ первой строки '+this._model['text-indent']+'px');
        },

        /**
         * Разметка линейки
         * @private
         */
        _make_nums: function(){
            var v_max = 2000;
            var v_step = 40;
            var v = -1000;
            this._controls.nums.empty();

            while (v < v_max){
                this._controls.nums.append('<div class="sep-num"><div>'+Math.abs(v)+'</div></div>'+
                         '<div class="sep-small"></div><div class="sep-big"></div>'+
                         '<div class="sep-small"></div>');
                v += v_step;
            }
        },

        set_mleft: function(v){
            v = Math.round(v/this._round)*this._round;
            var min = -this._model['padding-left'];
            if (this._model['text-indent'] < 0) min = min - this._model['text-indent'];
            var max = this._controls.client.width() - this._model['margin-right'] - 9;
            if (this._model['text-indent'] > 0) max = max - this._model['text-indent'];
            v = Math.min(Math.max(v, min), max);
            this._model['margin-left'] = v;
        },

        set_mright: function(v){
            v = Math.round(v/this._round)*this._round;
            var min = -this._model['padding-right'];
            var max = this._controls.client.width() - this._model['margin-left'] - 9;
            if (this._model['text-indent'] > 0) max = max - this._model['text-indent'];
            v = Math.min(Math.max(v, min), max);
            this._model['margin-right'] = v;
        },

        set_tindent: function(v){
            v = Math.round(v/this._round)*this._round;
            var min = -this._model['padding-left'] - this._model['margin-left'];
            var max = this._controls.client.width() - this._model['margin-right'] - 9 - this._model['margin-left'];
            v = Math.min(Math.max(v, min), max);
            this._model['text-indent'] = v;
        },

        set_pleft: function(v){
            var m = this._model;
            v = Math.round(v/this._round)*this._round;
            var min = 0;
            if (m['margin-left'] < 0) min -= m['margin-left'];
            if (m['text-indent'] < 0) min -= Math.min(0,Math.max(0,m['margin-left']) + m['text-indent']);
            var max1 = m.width - m['padding-right'] - 9;
            var max2 = m.width - m['padding-right'] - m['margin-right'] - m['margin-left'] - 9;
            if (m['text-indent'] > 0) max2 -= m['text-indent'];
            v = Math.min(Math.max(v, min), max1, max2);
            m['padding-left'] = v;
        },

        set_pright: function(v){
            var m = this._model;
            v = Math.round(v/this._round)*this._round;
            var min = 0;
            if (m['margin-right'] < 0) min -= m['margin-right'];
            var max1 = m.width - m['padding-left'] - 9;
            var max2 = m.width - m['padding-left'] - m['margin-right'] - m['margin-left'] - 9;
            if (m['text-indent'] > 0) max2 -= m['text-indent'];
            v = Math.min(Math.max(v, min), max1, max2);
            m['padding-right'] = v;
        },

        call_setStateAfter: function(caller, state, changes){ //after
            this.gettingStyle();
        },

        gettingStyle: function(object){
            var styles = this.callParents('getStyle');
            if ($.isArray(styles)){
                var s, style;
                for (s in styles){
                    style = styles[s];
                    if ('padding-left' in style){
                        this.set_pleft(parseInt(style['padding-left']));
                    }
                    if ('padding-right' in style){
                        this.set_pright(parseInt(style['padding-right']));
                    }
                    if ('margin-left' in style){
                        this.set_mleft(parseInt(style['margin-left']));
                    }
                    if ('margin-right' in style){
                        this.set_mright(parseInt(style['margin-right']));
                    }
                    if ('text-indent' in style){
                        this.set_tindent(parseInt(style['text-indent']));
                    }
                }
                this._show();
            }
        },

        sendingStyle: function(){
            var style = $.extend({}, this._model);
            delete style['width'];
            var s;
            for (s in style) style[s] = style[s] + 'px';
            this.callParents('setStyle', [style]);
        }
    })
})(jQuery);