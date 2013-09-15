/**
 * User: pauline
 * Date: 16.04.13
 * Скрипт для виджета редактирования описания
 */
(function($) {
    $.widget("boolive.PageDescriptionEditor", $.boolive.Widget, {
        _input: null,
        _value:'',
        _changed: false,
        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            self._error =
            self._input = self.element.find('textarea');
            self._value = self._input.val();
            self._input.on('input propertychange', function(e) {
                self._change(e);
            });
         },
        _change: function(e){
            if (this._value != this._input.val()){
                this.callParents('change', [this.options.object]);
            }else{
                this.callParents('nochange', [this.options.object]);
            }
        },
        _isChanged: function(){
            return this._value != this._input.val();
        },

        call_save: function(e){
            if (this._isChanged()){
                var self = this;
                var url = /^[a-z]+:\/\//i.test(self.options.object) ? self.options.object : window.location.protocol + '//' + window.location.host + self.options.object;
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    accepts: {json: 'application/json'},
                    url: url,
                    data: {
                        method: 'PUT',
                        entity:{
                            value: self._input.val()
                        }
                    },
                    success: function(result, textStatus, jqXHR){
    //                    if (result.error){
    //                        for(var e in result.error){
    //                            self.element.find('.error').text(result.error[e]);
    //                        }
    //                    }else{
                            self.element.find('.error').text('');
                            self._value = self._input.val();
    //                    }
                        self._change();
                    }
                });
            }
        },
        call_cancel: function(e){
            this._input.val(this._value);
        }
    });
})(jQuery);