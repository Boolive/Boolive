/**
 * Query UI boolive.Widget
 * Логика виджета на стороне клиента
 */
(function($, _, ace, undefined) {
    $.widget("boolive.FileEditor", $.boolive.Widget, {

        _input: null,
        _value: '',
        _changed: false,
        _editor: null,
        _is_default: null,
        _contents: null,
        _menu: null,

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            self._error = '';
            self._input = self.element.find('.FileEditor__text');
            self._editor = ace.edit(self._input[0]);
            self._editor.setTheme("ace/theme/boolive");
            var mode = 'text';
            if (typeof this.element.attr('data-mode') !== 'undefined'){
                switch (this.element.attr('data-mode')){
                    case 'php':
                    case 'tpl': mode = 'php';
                        break;
                    case 'js': mode = 'javascript';
                        break;
                    default: mode = this.element.attr('data-mode');
                }
            }
            self._editor.getSession().setMode("ace/mode/"+mode);
            self._editor.setFontSize(14);
            self._editor.setShowPrintMargin(false);
            self._editor.renderer.on('afterRender', function(e) {
                $('.ace_editor').css("opacity", self._is_default?"0.5":"1");
                self.element.css("background-color", self._is_default?"#f5f5f5":"#fff");
            });

            var contents = this.element.find('.FileEditor__contents');
            self._contents = {
                default: contents.children('#default').text(),
                self: contents.children('#self').text()
            };
            self._setIsDeafult(this.element.attr('data-is_default')==='1', true);

            self._editor.on('change', function (){
                self._change();
                self._autoheight();

            });
            self._autoheight();
            self._value = self._editor.getValue();

            self._menu = self.element.find('.FileEditor__default-menu');
            self._menu.children().click(function(){
                if (!$(this).hasClass('FileEditor__btn_selected')){
                    self._menu.find('.FileEditor__btn_selected').removeClass('FileEditor__btn_selected');
                    $(this).addClass('FileEditor__btn_selected');
                    self._setIsDeafult($(this).hasClass('FileEditor__btn_default'));
                    self._autoheight();
                }
            });
         },

        _setIsDeafult: function(is_default, not_changes)
        {
            this._is_default = is_default;
            this._editor.setReadOnly(this._is_default);
            if (this._is_default){
                if (!not_changes) this._contents.self = this._editor.getValue();
                this._editor.setValue(this._contents.default);
            }else{
                if (!not_changes) this._contents.default = this._editor.getValue();
                this._editor.setValue(this._contents.self);
            }
            this._editor.clearSelection();
            this._editor.moveCursorTo(0,0);
        },

        _autoheight: function(){
            var doc = this._editor.getSession().getDocument();
            this._input.css('height', 17 * doc.getLength() + 22 + 'px');
            this._input.css('min-height', $(document).height()-this._input.offset().top-38 + 'px');

            this._editor.resize();
        },

        _change: function(e){
            if (this._isChanged()){
                this.callParents('change', [this.options.object], null, true);
            }else{
                this.callParents('nochange', [this.options.object], null, true);

            }
        },
        _isChanged: function(){
            return this._value != this._editor.getValue() || this.element.data('is_default') != this._is_default;
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
                            file:{
                                content: self._editor.getValue()
                            },
                            is_default_value: this._is_default
                        }
                    },
                    success: function(result, textStatus, jqXHR){
                        self.element.find('.item__error').text('');
                        self._value = self._editor.getValue();
                        self.element.data('is_default', self._is_default);
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
            this._is_default = this.element.data('is_default');
        }
    })
})(jQuery, _, ace);