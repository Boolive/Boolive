var pluginName = 'more';
var moreCmd = {
    exec:
    function(a){
        var b = CKEDITOR.dom.element.createFromHtml('<a class="more"> </a>',a.document);
        a.insertElement(b)
    },

    context:
        "a",
        allowedContent: 'a(more)',
        requiredContent:"a"
};
CKEDITOR.plugins.add( 'more',
    {
        init : function( editor )
        {
            editor.addCommand(pluginName,moreCmd);
            editor.ui.addButton( 'more',
                {
                    label : 'Разделить короткое и полное содержание',
                    command : pluginName,
                    icon : this.path + 'icons/more.png',
                    toolbar : 'insert'
                });
        }
    });


