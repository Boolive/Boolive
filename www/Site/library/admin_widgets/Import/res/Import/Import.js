/**
 * Query UI boolive.Widget
 * Логика виджета на стороне клиента
 */
(function($, _, undefined) {
    $.widget("boolive.Import", $.boolive.Widget, {
        _form: null,
        _submit: null,
        _fileinput: null,

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            self._form = this.element.find('.Import__form');
            self._fileinput = self._form.find('.Import__input-file');
            self._submit = self._form.find('.Import__input-submit');

            self.element.find('.btn-primary').click(function(e){
                if (!$(this).hasClass('btn-disable')){
                    self.add();
                }
                e.preventDefault();
            });
            self.element.on('click', '.Import__clear', function(e){
                e.preventDefault();
                self.clear();
            });
            self._form.on('change', '[type=file]', function() {
                self.change();
            });
            self.loadTasks();
        },

        change: function(){
            if (this._fileinput.val()!=''){
                this._submit.removeClass('btn-disable');
            }else{
                this._submit.addClass('btn-disable');
            }
        },

        reset: function(){
//            this.element.find('.Field__error').text('');
            this._fileinput.replaceWith(this._fileinput.val('').clone(true));
            this._fileinput = this._form.find('.Import__input-file');
        },

        clear: function()
        {
            var self = this;
            this.callServer('clear_tasks',{
                object: this.options.object
            }, function(result, textStatus, jqXHR){
                if (result.out){
                    self.loadTasks();
                }
            });
        },

        loadTasks: function(){
            var self = this;
            this.callServer('get_tasks',{
                object: this.options.object
            }, function(result, textStatus, jqXHR){
                if (result.out){
                    var list = self.element.find('.Import__list');
                    list.empty();
                    $.each(result.out.list, function(){
                       list.append('<li class="Import__item">' +
                                    '<span class="Import__item-title">'+this.title+'</span> ' +
                                    '<span class="Import__item-status Import__item-status_'+this.status+'">' +
                                    this.status_msg +
                                    '</span></li>'
                       );
                    });
                    if (result.out.have_process){
                        setTimeout(function(){
                            self.loadTasks();
                        }, 500);
                    }
                }
            });
        },

        add: function(){
            var self = this;
            //var url = /^[a-z]+:\/\//i.test(self.options.object) ? self.options.object : window.location.protocol + '//' + window.location.host + self.options.object;
            self._form.ajaxSubmit({
                type: 'POST',
                dataType: 'json',
                //url: url,
                data: {
                    object: this.options.object,
                    direct: this.options.view,
                    call: 'add_task'
                },
                owner: self.eventNamespace,
                beforeSubmit: function(arr, form, options){

                },
                uploadProgress: function(e, position, total, percent){

                },
                success: function(responseText, statusText, xhr, $form){
                    self.reset();
                    self.change();
                    self.loadTasks();
                },
                error: function(jqXHR, textStatus){
//                    var result = $.parseJSON(jqXHR.responseText);
//                    if (typeof result.error !== 'undefined'){
//                        var getErrorMessage = function(error){
//                            var message = '';
//                            _.each(error.children, function(item){
//                                message += getErrorMessage(item);
//                            });
//                            if (!message){
//                                message = error.message;
//                            }
//                            return message;
//                        };
//                        self.element.find('.Field__error').text(getErrorMessage(result.error));
//                    }
                }
            });
        }
    })
})(jQuery, _);