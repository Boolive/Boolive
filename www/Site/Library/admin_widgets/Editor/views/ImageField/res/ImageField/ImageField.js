/**
 * Query UI boolive.Widget
 * Логика виджета на стороне клиента
 */
(function($, _, undefined) {
    $.widget("boolive.ImageField", $.boolive.Field, {
        _reset: null,
        _filename: null,
        _img: null,
        _form: null,
        _isempty: true,

        _create: function() {
            $.boolive.Item.prototype._create.call(this);
            var self = this;
            self._reset = this.element.find('.ImageField__reset').hide();
            self._filename = this.element.find('.ImageField__filename').hide();
            self._img = this.element.find('.ImageField__image');
            self._img.on('click', function(e){
                $(this).toggleClass('maximize');
            });
            self._form = this.element.find('.ImageField__form');
            self._form.on('change', '[type=file]', function() {
                self._isempty = false;
                self._reset.show();
                self._filename.text(self.getLastParam($(this).val())).show();
                self._change();
            });
            self._reset.on('click', function(e){
                e.preventDefault();
                self.call_cancel();
                self._change();
            });
            self.element.on('focus', 'input', function(e){
                self._select();
            })
        },

        _isChanged: function(){
            return !this._isempty;
        },

        call_save: function(e){
            if (this._isChanged()){
                var self = this;
                var url = /^[a-z]+:\/\//i.test(self.options.object) ? self.options.object : window.location.protocol + '//' + window.location.host + self.options.object;
                self._form.ajaxSubmit({
                    type: 'POST',
                    dataType: 'json',
                    accepts: {json: 'application/json'},
                    url: url,
                    data: {
                        method: 'PUT'
                    },
                    owner: self.eventNamespace,
                    beforeSubmit: function(arr, form, options){

                    },
                    uploadProgress: function(e, position, total, percent){

                    },
                    success: function(responseText, statusText, xhr, $form){
                        self.element.find('.ImageField__image').attr('src', responseText.result.file+'?'+Math.random());
                        self.call_cancel();
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

        call_cancel: function(){
            this._isempty = true;
            this._reset.hide();
            this._filename.hide();
            var file_input = this._form.find('input[type="file"]:first');
            this.element.find('.Field__error').text('');
            file_input.replaceWith(file_input.val('').clone(true));
        },

        /**
         * Последний параметр в pathname URI
         * @param uri
         * @return {*}
         */
        getLastParam: function(uri){
            var m = uri.match(/([^\\\/]*)$/);
            if (m){
                return m[1];
            }else{
                return '';
            }
        }
    });
})(jQuery, _);