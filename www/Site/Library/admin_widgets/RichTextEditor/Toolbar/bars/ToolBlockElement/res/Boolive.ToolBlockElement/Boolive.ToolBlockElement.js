/**
 * Панель блочного элемента
 * Query UI widget
 * Copyright 2013 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.ToolBlockElement", $.boolive.Widget, {
        _model_first: {},
        _model: {
            style: {
                'display': 'block',
                //'position': 'static',
                'margin-left': 0,
                'margin-right': 0,
                'float': 'none'
            }
        },
        _controls: {
            align_left: null,
            align_center: null,
            align_right: null,
            float_left: null,
            float_right: null
        },

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            (this._controls.align_left = this.element.find('.align-left:first')).on('click', function(e){
                self.set_align_left();
                self.sendingProperties();
            });
            (this._controls.align_center = this.element.find('.align-center:first')).on('click', function(e){
                self.set_align_center();
                self.sendingProperties();
            });
            (this._controls.align_right = this.element.find('.align-right:first')).on('click', function(e){
                self.set_align_right();
                self.sendingProperties();
            });
            (this._controls.float_left = this.element.find('.float-left:first')).on('click', function(e){
                self.set_float_left();
                self.sendingProperties();
            });
            (this._controls.float_right = this.element.find('.float-right:first')).on('click', function(e){
                self.set_float_right();
                self.sendingProperties();
            });
            this.gettingProperties();
        },

        set_align_left: function(){
            if (this._model.style['margin-left']=='auto') this._model.style['margin-left'] = 0;
            this._model.style['margin-right'] = 'auto';
            this._model.style['display'] = 'block';
            this._model.style['float'] = 'none';
        },
        set_align_center: function(){
            this._model.style['margin-left'] = 'auto';
            this._model.style['margin-right'] = 'auto';
            this._model.style['display'] = 'block';
            this._model.style['float'] = 'none';
        },
        set_align_right: function(){
            this._model.style['margin-left'] = 'auto';
            if (this._model.style['margin-right']=='auto') this._model.style['margin-right'] = 0;
            this._model.style['display'] = 'block';
            this._model.style['float'] = 'none';
        },
        set_float_left: function(){
            if (this._model.style['margin-left']=='auto') this._model.style['margin-left'] = 0;
            if (this._model.style['margin-right']=='auto') this._model.style['margin-right'] = 0;
            this._model.style['display'] = 'block';
            this._model.style['float'] = 'left';
        },
        set_float_right: function(){
            if (this._model.style['margin-left']=='auto') this._model.style['margin-left'] = 0;
            if (this._model.style['margin-right']=='auto') this._model.style['margin-right'] = 0;
            this._model.style['display'] = 'block';
            this._model.style['float'] = 'right';
        },

        show_block_position: function(){
            if(this._model.style['float'] == 'none'){
                this._controls.float_left.removeClass('selected');
                this._controls.float_right.removeClass('selected');
                if (this._model.style['margin-left'] == 'auto' && this._model.style['margin-right'] == 'auto'){
                    this._controls.align_center.addClass('selected');
                }else{
                    this._controls.align_center.removeClass('selected');
                }
                if (this._model.style['margin-left'] == 'auto' && this._model.style['margin-right'] != 'auto'){
                    this._controls.align_right.addClass('selected');
                }else{
                    this._controls.align_right.removeClass('selected');
                }
                if (this._model.style['margin-left'] != 'auto'){
                    this._controls.align_left.addClass('selected');
                }else{
                    this._controls.align_left.removeClass('selected');
                }
            }else{
                this._controls.align_center.removeClass('selected');
                this._controls.align_right.removeClass('selected');
                this._controls.align_left.removeClass('selected');
                if (this._model.style['float'] == 'left'){
                    this._controls.float_left.addClass('selected');
                }else{
                    this._controls.float_left.removeClass('selected');
                }
                if (this._model.style['float'] == 'right'){
                    this._controls.float_right.addClass('selected');
                }else{
                    this._controls.float_right.removeClass('selected');
                }
            }
        },

        show: function(){
            this.show_block_position();
        },

        call_setStateAfter: function(caller, state, changes){ //after
            this.gettingProperties();
        },

        gettingProperties: function(){
            var properties = this.callParents('getProperties');
            var enable  = true;
            if ($.isArray(properties)){
                var s, element, style;
                for (s in properties){
                    element = properties[s]['element'];
                    enable = enable && (properties[s].tag == 'img' || properties[s].tag == 'p');
                    style = element.css(['display', 'float', 'position', 'margin-left', 'margin-right']);
                    if (style['float'] == 'none' && style['display'] == 'block' && style['position']!='absolute'){
                        var info = {
                            left: element.offset().left - element.parent().offset().left - parseFloat(element.parent().css('padding-left')) - parseFloat(element.parent().css('margin-left')) - parseFloat(element.parent().css('border-left-width')),
                            padding_right: parseInt(element.parent().css('padding-right')),
                            parent_w: element.parent().width(),
                            element_w: element.outerWidth()
                        };
                        if ((info.parent_w - info.element_w - info.left < 1) && info.left > 1){
                            style['margin-left'] = 'auto';
                        }else
                        if ((info.parent_w - info.element_w - info.left*2 < 1) && info.left > 1){
                            style['margin-left'] = 'auto';
                            style['margin-right'] = 'auto';
                        }else{
                            style['margin-right'] = 'auto';
                        }
                    }
                    delete style['position'];
                    this._model.style = style;
                }
                this.changes(this._model_first, this._model, true);
                this.show();
            }else{
                enable = false;
            }
            this.callParents('changeToolbarState', {tool: this.options.view, enable: enable});
        },

        sendingProperties: function(){
            this.callParents('setProperties', this.changes(this._model_first, this._model, true));
            this.show();
        }
    })
})(jQuery, _);