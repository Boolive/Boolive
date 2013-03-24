/**
 * Панель настройки стиля
 * Query UI widget
 * Copyright 2013 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.RichTextToolbar", $.boolive.Widget, {

        _model: {
            'text-align': 'inherit',
            'line-height': 1,
            'paragraph-proto': null
        },
        _controls: {
            text_align_left: null,
            text_align_center: null,
            text_align_right: null,
            text_align_justify: null,
            line_height: null,
            paragraph_proto: null
        },

        _changes: null,

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            this._changes = {};


            this.element.on('click', function(e){
                e.preventDefault();
                e.stopPropagation();
                self.sendingStyle();
            }).on('mousedown moseup', function(e){
                e.preventDefault();
                e.stopPropagation();
            });

            (this._controls.text_align_left = this.element.find('.align-left:first')).on('click', function(e){
                self.set_text_align('left');
            });
            (this._controls.text_align_center = this.element.find('.align-center:first')).on('click', function(e){
                self.set_text_align('center');
            });
            (this._controls.text_align_right = this.element.find('.align-right:first')).on('click', function(e){
                self.set_text_align('right');
            });
            (this._controls.text_align_justify = this.element.find('.align-justify:first')).on('click', function(e){
                self.set_text_align('justify');
            });
            this._controls.line_height = this.element.find('.line-height-group');
            this._controls.line_height.find('li > a').on('click', function(e){
                self.set_line_height($(this).data('value'));
            });
            this._controls.paragraph_proto = this.element.find('.paragraph-proto-group');
            this._controls.paragraph_proto.find('li > a').on('click', function(e){
                self.set_paragraph_proto($(this).data('value'));
            });

            this.gettingStyle();
        },

        /**
         * Установка выравнивание текста
         * @param align
         */
        set_text_align: function(align){
            if (!(align == 'left' || align == 'center' || align == 'right' || align == 'justify')){
                align = 'inherit';
            }
            if (align != this._model['text-align']){
                this._model['text-align'] = align;
                this._changes['text-align'] = true;
            }
        },

        /**
         * Установка высоты строк
         * @param value
         */
        set_line_height: function(value){
            if (value!='normal'){
                var list = [0.8, 1, 1.48, 2, 3];
                var i = _.sortedIndex(list, value);
                if (i>=0){
                    //уточняем
                    if (i>0 && value-list[i-1] < list[i]-value){
                        i--;
                    }else
                    if (i<list.length-1 && list[i+1]-value < value-list[i]){
                        i++;
                    }
                }
                console.log(value);
                if (i>=list.length) i = list.length-1;
                value = list[i];
                console.log(value + ' ' +i);
            }
            if (value != this._model['line-height']){
                this._model['line-height'] = value;
                this._changes['line-height'] = true;
            }
        },

        /**
         * Установка прототипа абзацу
         * @param value
         */
        set_paragraph_proto: function(value){
            if (value != this._model['paragraph-proto']){
                this._model['paragraph-proto'] = value;
                this._changes['paragraph-proto'] = true;
            }
        },

        show_textalign: function(){
            if ('text-align' in this._changes){
                if (this._model['text-align'] == 'left'){
                    this._controls.text_align_left.addClass('selected');
                }else{
                    this._controls.text_align_left.removeClass('selected');
                }
                if (this._model['text-align'] == 'center'){
                    this._controls.text_align_center.addClass('selected');
                }else{
                    this._controls.text_align_center.removeClass('selected');
                }
                if (this._model['text-align'] == 'right'){
                    this._controls.text_align_right.addClass('selected');
                }else{
                    this._controls.text_align_right.removeClass('selected');
                }
                if (this._model['text-align'] == 'justify'){
                    this._controls.text_align_justify.addClass('selected');
                }else{
                    this._controls.text_align_justify.removeClass('selected');
                }
            }
        },

        show_line_height: function(){
            this._controls.line_height.find('.selected').removeClass('selected');
            this._controls.line_height.find('a[data-value="'+this._model['line-height']+'"]').parent().addClass('selected');
        },

        show_paragraph_proto: function(){
            this._controls.paragraph_proto.find('.selected').removeClass('selected');
            var sel = this._controls.paragraph_proto.find('a[data-value="'+this._model['paragraph-proto']+'"]:first');
            if (sel.size()){
                sel.parent().addClass('selected');
                this._controls.paragraph_proto.find('.paragraph-proto').text(sel.text());
            }else{
                this._controls.paragraph_proto.find('.paragraph-proto').text('Элемент');
            }
        },

        show: function(){
            this.show_textalign();
            this.show_line_height();
            this.show_paragraph_proto();
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
                    this.set_text_align(('text-align' in style)? style['text-align']:null);
                    this.set_line_height(('line-height' in style)? style['line-height']:1);
                    this.set_paragraph_proto(('paragraph-proto' in style)? style['paragraph-proto']:null);
                }
                this.show();
                this._changes = {};
            }
        },

        sendingStyle: function(){
            var s, style = {};
            for (s in this._changes){
                if (this._changes[s]){
                    style[s] = this._model[s];
                }
            }
            this.callParents('setStyle', [style]);
            this.show();
            this._changes = {};
        }
    })
})(jQuery, _);