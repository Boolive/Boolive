/**
 * Логика формы c CKEditor на основе jQueryUI
 *
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.HTMLField", $.boolive.Field, {
        _editor: null,

        _create: function() {
            // Конструктор полностью переопределяется, поэтому вызов $.boolive.Item вместо $.boolive.Field
            $.boolive.Item.prototype._create.call(this);
            var self = this;
            self._error = '';
            self._input = self.element.find('.Field__input');
            self._value = self.getValue();
            self._editor = self._input.ckeditor();
            // Проверка измененеий при визуальном редактировании
            self._editor.editor.on('change', function(e) {
                self._change(e);
            });
            // Проверка изменений при смене режима редактировании
            self._editor.editor.on('mode', function(e){
                self._change(e);
            });
            self._editor.editor.on('contentDom', function(e) {
                this.document.on('click', function(event){
                    var s = self.callParents('getState');
                    if (_.size(s.selected)>1 || _.indexOf(s.selected, self.options.object)==-1){
                        self.callParents('setState', [{selected: self.options.object}]);
                    }
                });
            });
        },

        setValue: function(value){
            this._editor.val(this._value);
        }
    })
})(jQuery, _);