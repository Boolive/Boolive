/**
 * User: pauline
 * Date: 16.04.13
 * Скрипт для виджета редактирования описания
 */
(function($) {
    $.widget("boolive.PageDescriptionEditor", $.boolive.Widget, {
        _form: null,
        _value: '',
        _is_change: false,

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            self._form = self.element.find('.form_description');
            self._value = self._form.find('.description').text();
            self._form.on('keyup', '.description', function(e) {
                if(self._form.find('.description').val()!=''){
                    self._change(e);
                }

            })
        },
        _change: function(e){
            if (this._value != this.element.html()){
                this._is_change = true;
                this.callParents('change', [this.options.object]);
            }else{
                this._is_change = false;
               this.callParents('nochange', [this.options.object]);
            }
        },
        call_save: function(e){
            var self=this;
            $.ajax({
                url: "&direct="+self.options.view+'&call=save',
                owner: self.eventNamespace,
                type: 'post',
                data: {Page:{description: self._form.find('.description').val()} , object: self.options.object},
                dataType: 'json',
                success: function(responseText, statusText, xhr){
                    if (responseText.out.error){
                        for(var e in responseText.out.error){
                            self._form.find('div.error').text(responseText.out.error[e]);
                        }
                    }else{
                        self._form.find('div.error').text('');
                        self.element.find('description').val(responseText.out.description);
                    }
                }
            });
        },
        call_cancel: function(e){
            this._form.find('.description').val(this._value);
        }
    });
})(jQuery);