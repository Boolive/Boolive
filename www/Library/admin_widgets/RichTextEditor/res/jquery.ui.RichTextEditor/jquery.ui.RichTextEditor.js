/**
 * Виджет редактора форматированного текста
 * Выбор из часто используемых или среди всех сущесвтующих
 * Query UI widget
 * Copyright 2013 (C) Boolive
 */
(function($) {
	$.widget("boolive.RichTextEditor", $.boolive.AjaxWidget, {
        // Объект, в который добавлять (родитель)
        object: null,
        // Выделенный объект (прототип для новового)
        object_select: null,

        _create: function() {
			$.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;
            self.element.live('focus', function() {
                var $this = $(this);
                $this.data('before', $this.html());
                return $this;
            }).live('blur paste', function(e) {
//                    e.preventDefault();
//                    e.stopPropagation();
                    console.log(e);
                var $this = $(this);
                if ($this.data('before') !== $this.html()) {
                    $this.data('before', $this.html());
                    $this.trigger('change');
                    //alert('change');
                }
                //return false;
                return $this;
            }).on('dragover', function(e){
                e.preventDefault();
                //return false;
            }).on('keydown', function(e){
                console.log(e);
                if (e.keyCode == 13 && !e.shiftKey){
                    e.preventDefault();
                    //console.log(document.getSelection().anchorOffset);
                }
            });
        }

	})
})(jQuery);