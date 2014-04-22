/**
 * Query UI boolive.Widget
 * Логика виджета на стороне клиента
 */
(function($, _, undefined) {
    $.widget("boolive.LinkField", $.boolive.Field, {

        _create: function() {
            $.boolive.Field.prototype._create.call(this);

        }
    })
})(jQuery, _);