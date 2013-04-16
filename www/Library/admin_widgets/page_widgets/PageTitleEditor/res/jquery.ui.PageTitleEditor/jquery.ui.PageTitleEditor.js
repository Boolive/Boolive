/**
 * User: pauline
 * Виджет для редактирования заголовка страницы
 */
(function($) {
    $.widget("boolive.PageTitleEditor", $.boolive.Widget, {
        _form: null,

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            if(self._form==null){
                self._form = $('<form method="POST" action=""></form>');
                var input = $('<input type="text" class="title" name="Page[title]" value="">');
                var obj = $('<input type="hidden" name="object" value="">');
                var button = $('<a class="btn btn-success btn-disable submit" href="#">Сохранено</a>');
                var error = $('<div class="error"></div>');
            }
            this.element.find('.titlelink').click(function(e){
                if(!self._form.hasClass('hide')){
                    e.preventDefault();
                    $(input).val($(this).text());
                    $(obj).val(self.options.object);
                    $(self._form).append(obj);
                    $(self._form).append(input);
                    $(self._form).append(button);
                    $(self._form).append(error);
                    $(this).parent().append(self._form);
                    $(this).toggleClass('hide');
                }else{
                    e.preventDefault();
                    $(this).toggleClass('hide');
                    self._form.removeClass('hide');
                }

            });
            self._form.on('change', '.title[type=text]', function(e) {
                if(self._form.find('.submit').hasClass('btn-disable') && self._form.find('.title[type=text]').val()!=''){
                    self._form.find('.submit').text('Сохранить').removeClass('btn-disable');
                }
            })
            .on('click', '.submit', function(e){
                e.preventDefault();
                if (!$(this).hasClass('btn-disable')){
                    self._form.find('.submit').text('Сохраняется...').removeClass('btn-disable');
                    // Ошибка при обработки запроса
                    self._form.ajaxError(function(e, jqxhr, settings, exception) {
                        if (settings.owner == self.eventNamespace){
                            self._form.find('div.error').text('Не удалось сохранить');
                            self._form.find('.submit').text('Сохранить еще раз')
                        }
                    });
                    self._form.ajaxSubmit({
                        url: "&direct="+self.options.view+'&call=save',
                        owner: self.eventNamespace,
                        type: 'post',
                        dataType: 'json',
                        success: function(responseText, statusText, xhr, $form){
                            console.log(responseText);
                            if (responseText.out.error){
                                for(var e in responseText.out.error){
                                    self._form.find('div.error').text(responseText.out.error[e]);
                                    self._form.find('.submit').text('Сохранить еще раз');
                                }
                            }else{
                                self._form.find('div.error').text('');
                                self._form.find('.submit').text('Сохранено').addClass('btn-disable');
                                self.element.find('a.titlelink').text(responseText.out.title);
                                self.element.find('a.titlelink').toggleClass('hide');
                                self._form.addClass('hide');
                            }
                        }
                    });
                }
            })
        }
    });
})(jQuery);