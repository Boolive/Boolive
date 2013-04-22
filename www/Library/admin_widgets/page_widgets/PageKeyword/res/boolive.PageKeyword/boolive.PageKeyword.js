/**
 * Виджет ключевого слова в редакторе.
 * User: pauline
 */
(function($) {
    $.widget("boolive.PageKeyword", $.boolive.Widget, {
        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            self.element.find('.remove').on('click', function(e){
                e.preventDefault();
                var result = self.callServer('Delete', {
                    object: self.options.object
                }, function(result, textStatus, jqXHR){
                    console.log(result);
                    if(result){
                      self.element.remove();
                    }
                });
            });
        }
    });
})(jQuery);
