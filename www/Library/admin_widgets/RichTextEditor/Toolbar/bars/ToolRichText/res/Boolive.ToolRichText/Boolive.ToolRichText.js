/**
 * Панель форматированного текста
 * Query UI widget
 * Copyright 2013 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.ToolRichText", $.boolive.Widget, {

        _model_first: {},
        _model:{
            filter: {
                real: 0,
                hidden: 0,
                deleted: 0
            }
        },
        _controls: {
            filter_real: null,
            filter_hidden: null,
            filter_deleted: null
        },

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            (this._controls.filter_real = this.element.find('.filter-real:first')).on('click', function(e){
                self.set_filter('real', !self._controls.filter_real.hasClass('selected'));
                self.sendingProperties();
            });
            (this._controls.filter_hidden = this.element.find('.filter-hidden:first')).on('click', function(e){
                self.set_filter('hidden', !self._controls.filter_hidden.hasClass('selected'));
                self.sendingProperties();
            });
            (this._controls.filter_deleted = this.element.find('.filter-deleted:first')).on('click', function(e){
                self.set_filter('deleted', !self._controls.filter_deleted.hasClass('selected'));
                self.sendingProperties();
            });
            this.gettingProperties();
        },

        call_setStateAfter: function(caller, state, changes){ //after
            this.gettingProperties();
        },

        set_filter: function(name, value){
            this._model['filter'][name] = value;
        },

        gettingProperties: function(){
            var properties = this.callParents('getProperties');
            var enable = true;
            if ($.isArray(properties)){
                var s, style;
                for (s in properties){
                    enable = enable && (_.isUndefined(properties[s].tag));
                    if ('filter' in properties[s]) this._model.filter = properties[s]['filter'];
//                    this.set_text_align(('text-align' in style)? style['text-align']:null);
//                    this.set_line_height(('line-height' in style)? style['line-height']:1);
//                    this.set_paragraph_proto(('paragraph-proto' in style)? style['paragraph-proto']:null);
                }
                this.changes(this._model_first, this._model, true);
                this.show();
                this._changes = {};
            }else{
                enable = false;
            }
            this.callParents('changeToolbarState', {tool: this.options.view, enable: enable});
        },

        sendingProperties: function(){
            this.callParents('setProperties', this.changes(this._model_first, this._model, true));
            this.show();
        },

        show_filter: function(){
            if (this._model.filter['real']){
                this._controls.filter_real.addClass('selected');
            }else{
                this._controls.filter_real.removeClass('selected');
            }
            if (this._model.filter['hidden']){
                this._controls.filter_hidden.addClass('selected');
            }else{
                this._controls.filter_hidden.removeClass('selected');
            }
            if (this._model.filter['deleted']){
                this._controls.filter_deleted.addClass('selected');
            }else{
                this._controls.filter_deleted.removeClass('selected');
            }
        },

        show: function(){
            this.show_filter();
        }
    })
})(jQuery, _);