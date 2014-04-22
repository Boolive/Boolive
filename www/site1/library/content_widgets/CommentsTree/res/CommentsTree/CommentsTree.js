/**
 * Query UI boolive.Widget
 * Логика виджета на стороне клиента
 */
(function($, _, undefined) {
    $.widget("boolive.CommentsTree", $.boolive.Widget, {
        parent_comment:null,
        form: null,
        message: null,
        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            this.parent_comment = this.options.object;
            this.form = this.element.find('.CommentsTree__new');
            this.message = self.form.find('.CommentsTree__item-message');
            this.element.on('click', '.CommentsTree__submit', function(e){
                e.preventDefault();
                if (!$(this).hasClass('CommentsTree__submit_disable')){
                    $(this).addClass('CommentsTree__submit_disable');
                    self.post(self.parent_comment, self.message.val());
                }
            }).on('click', '.Comment__link-answer', function(e){
                e.preventDefault();
                var parent = self.form.parent();
                parent.children('.Comment__link-answer_hide').removeClass('Comment__link-answer_hide');
                $(this).addClass('Comment__link-answer_hide').parent();
                $(this).parent().append(self.form);
                self.form.find('.CommentsTree__submit_disable').removeClass('CommentsTree__submit_disable');
                self.form.show();
                self.message.val('');
                self.parent_comment = $(this).attr('data-o');
            });
        },

        post: function(parent, message, success){
            var self = this;
            self.callServer('add',{
                direct: self.options.view,
                object: self.options.object,
                add: {
                    parent: parent,
                    message: message
                }
            },{
                success: function(result, textStatus, jqXHR){
                    if (!result.links) result.links = [];
                    $.include(result.links, function(){
                        var sub = self.form.parent().parent().children('.Comment__sub:first');
                        console.log(self.form);
                        sub.append(result.out);
                        self.form.parent().find('.Comment__link-answer_hide').removeClass('Comment__link-answer_hide');
                        self.form.hide();
                        $(document).trigger('load-html', [sub]);
                        // Обратный вызов при удачном обновлении виджета
                        if (success) success(result, textStatus, jqXHR);
                    });
                }
            });
        }
    })
})(jQuery, _);