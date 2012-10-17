/**
 * Auto bind jquery plugins
 * Plugin name set to attribute "data-plugin"
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
(function($) {
    var autobind = function(context){
        context.find('[data-plugin][data-pluginHas!="1"]').each(function(){
            //alert($(this).attr('data-pluginHas'));
            var plugin = $(this).attr('data-plugin');
            if (typeof $(this)[plugin] == 'function'){
                $(this)[plugin]().attr('data-pluginHas', 1);
            }
        });
    };
    $(document).ready(function(){
        autobind($(document));
    }).on('load-html', function(e, context){
        autobind(context || $(document));
    });
})(jQuery);