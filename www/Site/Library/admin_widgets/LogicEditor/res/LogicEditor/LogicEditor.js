/**
 * Query UI boolive.Widget
 * Логика виджета на стороне клиента
 */
(function($, _, undefined) {
    $.widget("boolive.LogicEditor", $.boolive.Widget, {
        _input: null,
        _value: '',
        _changed: false,
        _editor: null,

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            self._error = '';
            self._input = self.element.find('.LogicEditor__text');
            self._editor = ace.edit(self._input[0]);
            self._editor.setTheme("ace/theme/boolive");
            self._editor.getSession().setMode("ace/mode/php");
            self._editor.setFontSize(14);
            self._editor.on('change', function (){
                self._change();
                self._autoheight();
            });
//            self._editor.setShowPrintMargin(false);
            self._autoheight();
            self._value = self._editor.getValue();
         },

        _autoheight: function(){
            var doc = this._editor.getSession().getDocument();
            this._input.css('height', 14 * doc.getLength() + 22 + 'px');
            this._input.css('min-height', $(document).height()-65 + 'px');
            this._editor.resize();
        },

        _change: function(e){
            if (this._value != this._editor.getValue()){
                this.callParents('change', [this.options.object], null, true);
            }else{
                this.callParents('nochange', [this.options.object], null, true);

            }
        },
        _isChanged: function(){
            return this._value != this._editor.getValue();
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
                            logic:{
                                content: self._editor.getValue()
                            }
                        }
                    },
                    success: function(result, textStatus, jqXHR){
                        self.element.find('.item__error').text('');
                        self._value = self._editor.getValue();
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
            this._editor.setValue(this._value);
        }
    })
})(jQuery, _);