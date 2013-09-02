/**
 * Auto bind jquery plugins
 * Plugin name set to attribute "data-p"
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
(function($) {
    var autobind = function(context){
        context.find('[data-p]').each(function(){
            var plugin = $(this).attr('data-p');
            if (typeof $(this)[plugin] == 'function'){
                $(this)[plugin]().removeAttr('data-p');
            }
        });
    };
    $(document).ready(function(){
        autobind($(document));
    }).on('load-html', function(e, context){
        autobind(context || $(document));
    });
})(jQuery);