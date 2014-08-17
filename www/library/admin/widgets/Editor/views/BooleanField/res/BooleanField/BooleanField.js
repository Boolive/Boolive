/**
 * Query UI boolive.Widget
 * Логика виджета на стороне клиента
 */
(function($, _, undefined) {
    $.widget("boolive.BooleanField", $.boolive.Field, {

        _create: function() {
            $.boolive.Field.prototype._create.call(this);
            var self = this;
            this._input.on('change', function(e) {
                self._change(e);
            });
        },

        getValue: function(){
            return this._input.prop("checked");
        },

        setValue: function(value){
            this._input.prop("checked", value?true:false);
        }
    })
})(jQuery, _);