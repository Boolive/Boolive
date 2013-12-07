/**
 * Query UI boolive.Widget
 * Логика виджета на стороне клиента
 */
(function($, _, undefined) {
    $.widget("boolive.Field", $.boolive.Item, {
        _input: null,
        _value: '',

        _create: function() {
            $.boolive.Item.prototype._create.call(this);
            var self = this;
            self._error = '';
            self._input = self.element.find('.Field__input');
            self._value = self.getValue();
            self._input.on('input propertychange', function(e) {
                self._change(e);
            });
         },

        _change: function(e){
            if (this._isChanged()){
                this.callParents('change', [this.options.object]);
            }else{
                this.callParents('nochange', [this.options.object]);

            }
        },

        _isChanged: function(){
            return this._value != this.getValue();
        },

        getValue: function(){
            return this._input.val()
        },

        setValue: function(value){
            this._input.val(value);
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
                            value: self.getValue()
                        }
                    },
                    success: function(result, textStatus, jqXHR){
                        self.element.find('.Field__error').text('');
                        self._value = self.getValue();
                        self._change();
                    },
                    error: function(jqXHR, textStatus){
                        var result = $.parseJSON(jqXHR.responseText);
                        if (typeof result.error !== 'undefined'){
                            var getErrorMessage = function(error){
                                var message = '';
                                _.each(error.children, function(item){
                                    message += getErrorMessage(item);
                                });
                                if (!message){
                                    message = error.message;
                                }
                                return message;
                            };
                            self.element.find('.Field__error').text(getErrorMessage(result.error));
                        }
                        self._change();
                    }
                });
            }
        },

        call_cancel: function(e){
            this.setValue(this._value);
        }
    });
})(jQuery, _);