/**
 * Виджет ключевого слова в редакторе ключевых слов.
 * @author: polinа Putrolaynen
 */
(function($) {
    $.widget("boolive.Keyword", $.boolive.Widget, {
        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            self.element.find('.Keyword__remove').on('click', function(e){
                e.preventDefault();
                var result = self.callServer('Delete', {
                    object: self.options.object
                }, function(result, textStatus, jqXHR){
                    if(result){
                      self.element.remove();
                    }
                });
            });
        }
    });
})(jQuery);
