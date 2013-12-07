/**
 * Логика формы на основе jQueryUI
 *
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.TextField", $.boolive.Widget, {
        _input: null,
        _value: '',
        _changed: false,

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            self._error = '';
            self._input = self.element.find('.TextField__input');
            self._value = self._input.val();
            self._input.on('input propertychange', function(e) {
                self._change(e);
            });
         },
        _change: function(e){
            if (this._value != this._input.val()){
                console.log(this.options.object);
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
                        self.element.find('.item__error').text('');
                        self._value = self._input.val();
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
                            self.element.find('.item__error').text(getErrorMessage(result.error));
                        }
                        self._change();
                    }
                });
            }
        },
        call_cancel: function(e){
            this._input.val(this._value);
        }
    })
})(jQuery, _);