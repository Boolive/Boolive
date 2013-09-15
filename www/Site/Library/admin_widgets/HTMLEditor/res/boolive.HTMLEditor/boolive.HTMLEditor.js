/**
 * Заготовка jQueryUI виджета с наследованием boolive.Widget
 * Переименуйте WidgetName на своё имя виджета.
 *
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.HTMLEditor", $.boolive.Widget, {
        _value: '',
        _textarea: null,
        _editor: null,
        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            self._textarea = self.element.find('textarea:first');
            self._value = self._textarea.val();
            self._editor = self._textarea.ckeditor();
            // Проверка измененеий при визуальном редактировании
            self._editor.editor.on('change', function() {
                if (self._value != self._textarea.val()){
                    self.callParents('change', [self.options.object]);
                }else{
                    self.callParents('nochange', [self.options.object]);
                }
            });
            // Проверка изменений при смене режима редактировании
            self._editor.editor.on('mode', function(){
                if (self._isChanged()){
                    self.callParents('change', [self.options.object]);
                }else{
                    self.callParents('nochange', [self.options.object]);
                }
            });
        },

        _isChanged: function(){
            return this._value != this._textarea.val();
        },

        /**
         * Сохранение
         * @param e
         */
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
                            value: self.element.find('textarea').val()
                        }
                    },
                    success: function(result, textStatus, jqXHR){
                        // @todo Если есть ошибки, то отобразить их
                        self._value = self._editor.val();
                        self.callParents('nochange', [self.options.object]);
                    }
                });
            }
        },
        /**
         * Отмена несохраненных изменений
         */
        call_cancel: function(e){
            this._editor.val(this._value);
        }
    })
})(jQuery, _);