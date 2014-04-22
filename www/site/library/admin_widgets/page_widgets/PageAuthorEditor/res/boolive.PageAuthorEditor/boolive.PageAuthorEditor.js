/**
 * User: pauline
 * Скрипт для виджета смены автора страницы
 */
(function($, _) {
    $.widget("boolive.PageAuthorEditor", $.boolive.Widget, {
        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            var a = self.element.find('.authorlink');
            a.click(function(e){
                e.preventDefault();
                self.callParents('openWindow', [null,
                    {
                        url: "/",
                        data: {
                            direct: self.options.view+'/SelectObject', // uri выиджета выбора объекта
                            object: self.options.object
                        }
                    },
                    function(result, params){
                        if (result == 'submit' && 'selected' in params){
                            self.callServer('changeAuthor',{
                                                    user:{user: _.first(params.selected)},
                                                    object: self.options.object
                                                },function(){
                                self.load(self.element, 'replace', self.options.view, {object: self.options.object}, {url:'/'});
                            });

                        }
                    }
                ]);
            });
        }
    });
})(jQuery, _);