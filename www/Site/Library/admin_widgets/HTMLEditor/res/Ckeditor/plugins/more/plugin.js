var pluginName = 'more';
var moreCmd =
{
    exec : function( editor )
    {
        var newElement = new CKEDITOR.dom.element('div');
        newElement.setAttribute('class', 'more');
        newElement.setText(" ");
        editor.insertHtml("<!-more->");
    }
};
CKEDITOR.plugins.add( 'more',
    {
        init : function( editor )
        {
            editor.addCommand( pluginName,moreCmd);
// Добавляем кнопочку
            editor.ui.addButton( 'more',
                {
                    label : 'Разделить короткое и полное содержание',//Title кнопки
                    command : pluginName,
                    icon : this.path + 'icons/more.png',//Путь к иконке
                    toolbar : 'insert'
                });
        }
    });