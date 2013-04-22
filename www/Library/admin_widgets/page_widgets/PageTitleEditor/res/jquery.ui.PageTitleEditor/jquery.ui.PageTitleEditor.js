/**
 * User: pauline
 * Виджет для редактирования заголовка страницы
 */
(function($) {
    $.widget("boolive.PageTitleEditor", $.boolive.Widget, {
        _form: null,
        _value:'',
        _is_change: false,

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            if(self._form==null){
                self._form = $('<form method="POST" action=""></form>');
                var input = $('<input type="text" class="title" name="Page[title]" value="">');
                var obj = $('<input type="hidden" name="object" value="">');
                var error = $('<div class="error"></div>');
            }
            var a = this.element.find('.titlelink');
            self._value = a.text();
            a.click(function(e){
                console.log(self._form.hasClass('hide'));
                if(!self._form.hasClass('hide')){
                    e.preventDefault();
                    $(input).val($(this).text());
                    $(obj).val(self.options.object);
                    $(self._form).append(obj);
                    $(self._form).append(input);
                    $(self._form).append(error);
                    $(this).parent().append(self._form);
                    $(this).addClass('hide');

                }else{
                    e.preventDefault();
                    $(this).addClass('hide');
                    self._form.removeClass('hide');
                }

            });

            self._form.on('keyup', '.title[type=text]', function(e) {
                if(self._form.find('.title[type=text]').val()!=''){
                    self._change(e);
                }
            })
         },
        _change: function(e){
            if (this._value != this._form.find('.title[type=text]').val()){
                this._is_change = true;
                this.callParents('change', [this.options.object]);
            }else{
                this._is_change = false;
                this.callParents('nochange', [this.options.object]);
            }
        },
        call_save: function(e){
            var self = this;
            $.ajax({
                url: "&direct="+self.options.view+'&call=save',
                owner: self.eventNamespace,
                type: 'post',
                data: {Page:{title: self._form.find('.title').val()} , object: self.options.object},
                dataType: 'json',
                success: function(responseText, statusText, xhr){
                    if (responseText.out.error){
                        for(var e in responseText.out.error){
                            self._form.find('div.error').text(responseText.out.error[e]);
                        }
                    }else{
                        self._form.find('div.error').text('');
                        self.element.find('a.titlelink').text(responseText.out.title);
                        self.element.find('a.titlelink').removeClass('hide');
                        self._form.addClass('hide');
                    }
                }
            });
        },
        call_cancel: function(e){
            if(!this._form.hasClass('hide') &&  this.element.has('form').get(0)){
                this._form.addClass('hide');
            }
            this.element.find('a.titlelink').removeClass('hide').text(this._value);
        }
    });
})(jQuery);