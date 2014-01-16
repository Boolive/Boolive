/**
 * Query UI boolive.Widget
 * Логика виджета на стороне клиента
 */
(function($, _, undefined) {
    $.widget("boolive.Start", $.boolive.Widget, {

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            this.element.on('click', '.Start__show-object', function(e){
                e.preventDefault();
                self.callParents('setState', [{
                    object: '/'+$(this).attr('href'),
                    select: 'property'
                }]);
            });
            this.element.on('click', '.Start__add-object', function(e){
                e.preventDefault();
                var a = $(this);
                self.callServer('add',{
                    direct: self.options.view,
                    object: self.callParents('getState').object,
                    add: {
                        parent: a.data('parent'),
                        proto: a.data('proto'),
                        is_link: false
                    }
                },{
                    url: window.location.protocol + '//' + window.location.host,
                    success: function(result, textStatus, jqXHR){
                        if (_.isObject(result.out) && !_.isEmpty(result.out.uri)){
                            self.callParents('setState', [{
                                object: result.out.uri,
                                select: 'property'
                            }]);
                        }
                    }
                });
            });
        }
    })
})(jQuery, _);