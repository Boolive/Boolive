/**
 * Панель абзаца
 * Query UI widget
 * Copyright 2013 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.ToolParagraph", $.boolive.Widget, {
        _model_first: {},
        _model: {
            style: {
                'text-align': 'inherit',
                'line-height': 1,
                'font-size': '10pt'
            },
            'proto': null
        },
        _controls: {
            text_align_left: null,
            text_align_center: null,
            text_align_right: null,
            text_align_justify: null,
            font_size:null,
            line_height: null,
            proto: null
        },

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;

            (this._controls.text_align_left = this.element.find('.align-left:first')).on('click', function(e){
                self.set_text_align('left');
                self.sendingProperties();
            });
            (this._controls.text_align_center = this.element.find('.align-center:first')).on('click', function(e){
                self.set_text_align('center');
                self.sendingProperties();
            });
            (this._controls.text_align_right = this.element.find('.align-right:first')).on('click', function(e){
                self.set_text_align('right');
                self.sendingProperties();
            });
            (this._controls.text_align_justify = this.element.find('.align-justify:first')).on('click', function(e){
                self.set_text_align('justify');
                self.sendingProperties();
            });
            this._controls.line_height = this.element.find('.line-height-group');
            this._controls.line_height.find('li > a').on('click', function(e){
                self.set_line_height($(this).data('value'));
                self.sendingProperties();
            });
            this._controls.font_size = this.element.find('.font-size-group');
            this._controls.font_size.find('li > a').on('click', function(e){
                self.set_font_size($(this).data('value'));
                self.sendingProperties();
            });
            this._controls.proto = this.element.find('.proto-group');
            this._controls.proto.find('li > a').on('click', function(e){
                self.set_proto($(this).data('value'));
                self.sendingProperties();
            });
            this.gettingProperties();
        },

        call_setStateAfter: function(caller, state, changes){ //after
            this.gettingProperties();
        },

        /**
         * Получение стиля текущего объекта
         */
        gettingProperties: function(){
            var properties = this.callParents('getProperties');
            var enable = true;
            if ($.isArray(properties)){
                var s, element;
                for (s in properties){
                    element = properties[s]['element'];
                    enable = enable && (properties[s].tag == 'p');
                    this.set_text_align(element.css('text-align'));
                    var font_size = parseFloat(element.css('font-size'))||1;
                    var line_height = parseFloat(element.css('line-height'));
                    if (line_height!='normal') line_height = line_height / font_size;
                    this.set_line_height(line_height);
                    this.set_proto(('proto' in properties[s])? properties[s]['proto']:null);
                    this.set_font_size(Math.round(parseFloat(element.css('font-size')) * 0.75)+'pt');
                }
                this.changes(this._model_first, this._model, true);
                this.show();
            }else{
                enable = false;
            }
            this.callParents('changeToolbarState', {tool: this.options.view, enable: enable});
        },

        /**
         * Установка стиля для текущего объекта
         */
        sendingProperties: function(){
            this.callParents('setProperties', this.changes(this._model_first, this._model, true));
            this.show();
        },

        /**
         * Установка выравнивание текста
         * @param align
         */
        set_text_align: function(align){
            if (!(align == 'left' || align == 'center' || align == 'right' || align == 'justify')){
                align = 'inherit';
            }
            if (align != this._model.style['text-align']){
                this._model.style['text-align'] = align;
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
                if (i>=list.length) i = list.length-1;
                value = list[i];
            }
            if (value != this._model.style['line-height']){
                this._model.style['line-height'] = value;
            }
        },

        set_font_size: function(value){
            if (value != this._model.style['font-size']){
                this._model.style['font-size'] = value;
            }
        },

        /**
         * Установка прототипа абзацу
         * @param value
         */
        set_proto: function(value){
            if (value != this._model['proto']){
                this._model['proto'] = value;
            }
        },

        show_textalign: function(){
            if (this._model.style['text-align'] == 'left'){
                this._controls.text_align_left.addClass('selected');
            }else{
                this._controls.text_align_left.removeClass('selected');
            }
            if (this._model.style['text-align'] == 'center'){
                this._controls.text_align_center.addClass('selected');
            }else{
                this._controls.text_align_center.removeClass('selected');
            }
            if (this._model.style['text-align'] == 'right'){
                this._controls.text_align_right.addClass('selected');
            }else{
                this._controls.text_align_right.removeClass('selected');
            }
            if (this._model.style['text-align'] == 'justify'){
                this._controls.text_align_justify.addClass('selected');
            }else{
                this._controls.text_align_justify.removeClass('selected');
            }
        },

        show_line_height: function(){
            this._controls.line_height.find('.selected').removeClass('selected');
            this._controls.line_height.find('a[data-value="'+this._model.style['line-height']+'"]').parent().addClass('selected');
        },

        show_font_size: function(){
            var size = this._model.style['font-size'];
            if (size!==''){
                this._controls.font_size.find('.font-size').text(size);
                this._controls.font_size.find('.selected').removeClass('selected');
                this._controls.font_size.find('a[data-value="'+this._model.style['font-size']+'"]').parent().addClass('selected');
            }else{
                this.gettingProperties();
            }
        },


        show_proto: function(){
            this._controls.proto.find('.selected').removeClass('selected');
            var sel = this._controls.proto.find('a[data-value="'+this._model['proto']+'"]:first');
            if (sel.size()){
                sel.parent().addClass('selected');
                this._controls.proto.find('.proto').text(sel.text());
            }else{
                this._controls.proto.find('.proto').text('Элемент');
            }
        },

        show: function(){
            this.show_textalign();
            this.show_line_height();
            this.show_proto();
            this.show_font_size();
        }
    })
})(jQuery, _);