/**
 * User: pauline
 * Виджет для редактирования ключевых слов
 */
(function($) {
    $.widget("boolive.PageKeywordsEditor", $.boolive.Widget, {
        _create: function() {
            $.boolive.Widget.prototype._create.call(this);

            this.init_sortable();
            var self = this;
            var form = self.element.find('form.add');
            var input = form.find('.value[type=text]');
            input.autocomplete({
                appendTo: ".PageKeywordsEditor",
                source: function( request, response ) {
                    if(request.term.charCodeAt(0)!=44 || request.term.charCodeAt(0)!=13){
                        $.ajax({
                            url: "&direct="+self.options.view+'&call=find',
                            type: 'post',
                            owner: self.eventNamespace,
                            data: {request: request.term, object: self.options.object},
                            dataType: 'json'
                        }).done(function(responseText){
                            response(responseText.out);
                            request.term='';
                        });
                    }
                },
                open: function(){
                    self.element.find('ul.ui-autocomplete').width(input.width());
                }
            });
            //Нужно, для фокусирования на поле в случае, если клик по форме или диву
            form.on('click',function(e) {
                input.focus();
            });
            
            self.element.find('.keywords').on('click',function(e) {
                input.focus();
            });
            input.on('keypress', function(e) {
                if (e.keyCode==13 || e.keyCode==44){
                    e.preventDefault();
                    $.ajax({
                        url: "&direct="+self.options.view+'&call=save',
                        owner: self.eventNamespace,
                        type: 'post',
                        data: {Keyword:{value: $(this).val()} , object: self.options.object},
                        dataType: 'json',
                        success: function(responseText, statusText, xhr){
                            if (responseText.out.error){
                                for(var e in responseText.out.error){
                                    form.find('div.error').text(responseText.out.error[e]);
                                }
                            }else{
                                form.find('div.error').text('');
                                input.val('');
                                if(responseText.out!==false){
                                    self.load(self.element.find('.old'), 'append',  self.options.view+'/switch_views',{object: responseText.out}, {url:'/'});
                                }

                            }
                        }
                    });
                }
            })

        },
        init_sortable: function(){
           var self = this;
            self.element.find('.old').sortable({
                update: function(event, ui) {
                    var object_uri = {};
                    var next_uri = {}
                    object_uri['uri'] = ui.item.attr('data-o');
                    if(ui.item.next().length>0){
                        var next = ui.item.next();
                        next_uri['uri'] = next.attr('data-o');
                        next_uri['next'] =1;
                    }else{
                        var next  = ui.item.prev();
                        next_uri['uri'] = next.attr('data-o');
                        next_uri['next'] =0;
                    }
                    self.callServer('saveOrder', {
                        saveOrder:{objectUri:object_uri, nextUri:next_uri},
                        object: self.options.object
                    });
                }
            });

        }


    });
})(jQuery);