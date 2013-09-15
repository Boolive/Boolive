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
                self.callParents('save');
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
            console.log('Change '+object);
            this._changes[object] = true;
            this._setSaveState('s2');
        },
        call_nochange: function(caller, object){ //before
            delete this._changes[object];
            console.log('Nochange '+object);
            if (_.isEmpty(this._changes)){
                this._setSaveState('s1');
            }
        }
    })
})(jQuery, _);