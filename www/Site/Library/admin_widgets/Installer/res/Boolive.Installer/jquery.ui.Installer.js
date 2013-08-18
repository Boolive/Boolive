/**
 * Виджет установки выбранного объекта
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _) {
    $.widget("boolive.Installer", $.boolive.Widget, {
        // Удаляемый объект
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


            var objects = _.isArray(this.options.object) ? this.options.object : [this.options.object];
            this.progress(0, objects);
        },

        progress: function(index, objects){
            var self = this;
            if (objects.length > index){
                this.callServer('install', {object: objects[index]},
                    function(result, textStatus, jqXHR){
                        if (result.out){
                            if (self.process){
                                index++;
                                self.bar.css('width', Math.round(index/objects.length*100)+'%');
                                if (!_.isUndefined(result.out.message)){
                                    self.message.text(result.out.message);
                                }
                                self.progress(index, objects);
                            }else{
                                self.stop(true);
                            }
                        }else{
                            console.log('Error');
                            console.log(result);
                        }
                    }
                );
            }else{
                self.stop(false);
            }
        },

        stop: function(close){
            this.process = false;
            if (close){
                this.element.find('.progress').hide();
                this.element.find('.confirm').show();
            }else{
                this.message.text('Завершено');
                this.element.find('.progress .cancel').text('Ok');
            }
        }
    })
})(jQuery, _);
