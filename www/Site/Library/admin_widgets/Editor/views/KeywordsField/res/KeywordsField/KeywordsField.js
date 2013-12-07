/**
 * Query UI boolive.Widget
 * Логика виджета на стороне клиента
 */
(function($, _, undefined) {
    $.widget("boolive.KeywordsField", $.boolive.Field, {
        _input: null,

        _create: function() {
            $.boolive.Item.prototype._create.call(this);
            this.init_sortable();
            var self = this;
            self._input = self.element.find('input[type=text]');
            self._input.autocomplete({
                appendTo: ".KeywordsField",
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
                    self.element.find('ul.ui-autocomplete').width(self._input.width());
                },
                select: function(e, ui){
                    self._input.val(ui.item.value);
                    self._add();
                    e.preventDefault();
                }
            });
            //Нужно, для фокусирования на поле в случае, если клик по форме или диву
            this.element.on('click',function(e) {
                self._input.focus();
            });

            self.element.find('.KeywordsField__keywords').on('click',function(e) {
                self._input.focus();
            });
            self._input.on('keypress', function(e) {
                if (e.keyCode==13 || e.keyCode==44){
                    e.preventDefault();
                    self._add();
                }
            })

        },
        _add: function(){
            var self = this;
            $.ajax({
                url: "&direct="+self.options.view+'&call=save',
                owner: self.eventNamespace,
                type: 'post',
                data: {Keyword:{value: self._input.val()} , object: self.options.object},
                dataType: 'json',
                success: function(responseText, statusText, xhr){
                    if (responseText.out.error){
                        for(var e in responseText.out.error){
                            self.element.find('.Field__error').text(responseText.out.error[e]);
                        }
                    }else{
                        self.element.find('.Field__error').text('');
                        self._input.val('');
                        if(responseText.out!==false){
                            self.load(self.element.find('..KeywordsField__keywords-old'), 'append',  self.options.view+'/views',{object: responseText.out}, {url:'/'});
                        }
                    }
                }
            });
        },

        init_sortable: function(){
           var self = this;
            self.element.find('.KeywordsField__keywords-old').sortable({
                update: function(event, ui) {
                    var object_uri = {};
                    var next_uri = {};
                    var next;
                    object_uri['uri'] = ui.item.attr('data-o');
                    if(ui.item.next().length>0){
                        next = ui.item.next();
                        next_uri['uri'] = next.attr('data-o');
                        next_uri['next'] =1;
                    }else{
                        next  = ui.item.prev();
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
    })
})(jQuery, _);