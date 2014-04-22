/**
 * Заготовка jQueryUI виджета с наследованием boolive.Widget
 * Переименуйте WidgetName на своё имя виджета.
 *
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.SaveTool", $.boolive.Widget, {
        _changes: null,
        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            this._changes = {};
            this.element.find('.s2').on('click', function(){
                self._setSaveState('s3');
                self.callParents('save', null, null, true);
            });
        },

        _setSaveState: function(state){
            this.element.find('.active').removeClass('active');
            this.element.find('.'+state).addClass('active');
        },
        call_setState: function(caller, state, changes){
            if (caller.direct == 'children' && _.isObject(changes) && ('object' in changes || 'view_name' in changes)){
                this._changes = [];
                this._setSaveState('s1');
            }
        },

        call_change: function(caller, object){ //before
            this._changes[object] = true;
            this._setSaveState('s2');
        },
        call_nochange: function(caller, object){ //before
            delete this._changes[object];
            if (_.isEmpty(this._changes)){
                this._setSaveState('s1');
            }
        },
        call_updateURI: function(e, args){
            var uris = _.keys(this._changes);
            var reg = new RegExp('^'+args.uri+'(\/|$)');
            var i,cnt=uris.length;
            for (i=0; i<cnt; i++){
                if (reg.test(uris[i])){
                    this._changes[uris[i].replace(args.uri, args.new_uri)] = true;
                    delete this._changes[uris[i]];
                }
            }
        }
    })
})(jQuery, _);