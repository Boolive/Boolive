/**
 * Заготовка jQueryUI виджета с наследованием boolive.Widget
 * Переименуйте WidgetName на своё имя виджета.
 *
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.ProgressAction", $.boolive.Widget, {
        objects: null,
        process: false,
        bar: null,
        message: null,

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            // uri объекта
            self.options.object = $.parseJSON(this.element.attr('data-o'));
            self.bar = self.element.find('.progress .bar');
            self.message = self.element.find('.progress .message');

            self.element.find('.confirm .submit').click(function(e){
                e.preventDefault();
                self.start();
            });
            self.element.find('.confirm .cancel').click(function(e){
                e.preventDefault();
                history.back();
            });
            self.element.find('.progress .cancel').click(function(e){
                e.preventDefault();
                self.stop(true);
            });
        },

        start: function(){
            var self = this;
            this.process = true;
            this.bar.css('width', 0);
            this.message.text('');
            this.element.find('.progress .cancel').text('Отмена');
            this.element.find('.confirm').hide();
            this.element.find('.progress').show();
            this.callServer('progress_start', {
                object: this.options.object
            }, function(result, textStatus, jqXHR){
                if (result.out && result.out.progress_id){
                    self.progress(result.out.progress_id);
                }
            });
        },

        progress: function(progress_id){
            var self = this;
            this.callServer('progress', {
                object: this.options.object,
                id: progress_id
            }, function(result, textStatus, jqXHR){
                if (result.out){
                    if (self.process){
                        self.bar.css('width', result.out.progress+'%');
                        if (!_.isUndefined(result.out.message)){
                            self.message.text(result.out.message);
                        }
                        if (!result.out.complete){
                            self.progress(progress_id);
                        }else{
                            self.stop(false);
                        }
                    }else{
                        self.stop(true);
                    }
                }else{
                    console.log('ProgressAction Error');
                }
            });
        },

        stop: function(close){
            this.process = false;
            if (close){
//                this.element.find('.progress').hide();
//                this.element.find('.confirm').show();
                history.back();
            }else{
                //this.message.text('Завершено');
                this.element.find('.progress .cancel').text('Ok');
            }
        }
    })
})(jQuery, _);