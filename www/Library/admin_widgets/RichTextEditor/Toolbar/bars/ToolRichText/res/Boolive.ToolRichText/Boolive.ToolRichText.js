/**
 * Панель форматированного текста
 * Query UI widget
 * Copyright 2013 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.ToolRichText", $.boolive.Widget, {

        _model_first: {},
        _model:{

        },
        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            this.gettingStyle();
        },

        call_setStateAfter: function(caller, state, changes){ //after
            this.gettingStyle();
        },

        gettingStyle: function(){
            var properties = this.callParents('getProperties');
            var enable = true;
            if ($.isArray(properties)){
                var s, style;
                for (s in properties){
                    enable = enable && (_.isUndefined(properties[s].tag));
//                    this.set_text_align(('text-align' in style)? style['text-align']:null);
//                    this.set_line_height(('line-height' in style)? style['line-height']:1);
//                    this.set_paragraph_proto(('paragraph-proto' in style)? style['paragraph-proto']:null);
                }
                this.changes(this._model_first, this._model, true);
//                this.show();
//                this._changes = {};
            }else{
                enable = false;
            }
            this.callParents('changeToolbarState', {tool: this.options.view, enable: enable});
        },

        sendingStyle: function(){
            this.callParents('setProperties', this.changes(this._model_first, this._model, true));
//            this.show();
        }
    })
})(jQuery, _);