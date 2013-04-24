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
            self._form = self.element.find('.form_title');
            self._value = self._form.find('.title').val();
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
                        self.element.find('.title').text(responseText.out.title);
                    }
                }
            });
        },
        call_cancel: function(e){
            this.element.find('.title').val(this._value);
        }
    });
})(jQuery);