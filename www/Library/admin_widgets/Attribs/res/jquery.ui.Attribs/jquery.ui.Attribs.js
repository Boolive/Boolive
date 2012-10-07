(function($) {
	$.widget("boolive.Attribs", $.boolive.AjaxWidget, {
        /**
         * Данные и методы их обновления.
         * При установки атрибутов генерируются события. Обработчиками обновляется форма
         */
        model: {
            object: '', // uri объекта
            attrib: {}, // текущие значения атрибутов
            attrib_start: {}, // начальные значения
            error: {}, // Оишибки
            process: { // Состояние процесса
                start: false, // процесс сохранения
                percent: 0 // процент сохранения
            },
            events: {}, // назначенные обработчики событий
            /**
             * Установка всех атрибутов
             * @param attribs
             */
            init: function(attribs){
                this.attrib = $.extend({}, attribs);
                this.attrib_start = attribs;
                for (var name in this.attrib){
                    this.trigger('change-attrib:'+name, this.attrib[name]);
                }
                this.trigger('change-attrib', false);
            },
            /**
             * Установка атрибута по имени
             * @param name
             * @param value
             */
            set_attrib: function(name, value){
                if (this.attrib[name] != value){
                    this.attrib[name] = value;
                    this.trigger('change-attrib:' + name, value);
                    this.trigger(
                        'change-attrib',
                        this.attrib[name] != this.attrib_start[name] ? true : this.is_change()
                    );
                }
            },

            set_error: function(name, value){
                if (this.error[name] != value){
                    this.error[name] = value;
                    this.trigger('change-error:' + name, value);
                    this.trigger('change-error', {name: name, value: value});
                }
            },

            set_process: function(name, value){
                if (this.process[name] != value){
                    this.process[name] = value;
                    this.trigger('change-process:' + name, value);
                }
            },

            clear_errors: function(){
                this.error = {};
                this.trigger('clear-error', null);
            },
            /**
             * Вызов события
             * @param event
             * @param arg
             */
            trigger: function(event, arg){
                if (this.events[event]){
                    this.events[event].fire(arg);
                }
            },
            /**
             * Регистрация на событие
             * @param event
             * @param callback
             * @return {*}
             */
            on: function(event, callback){
                if (!this.events[event]){
                    this.events[event] = $.Callbacks();
                }
                this.events[event].add(callback);
                return this;
            },

            is_change: function(){
                for (name in this.attrib){
                    if (this.attrib[name]!=this.attrib_start[name]) return true;
                }
                return false;
            }
        },

        submit_btn: null,
        submit_msg: null,

        /**
         * Конструктор виджета
         * @private
         */
        _create: function() {
			$.boolive.AjaxWidget.prototype._create.call(this);

            var self = this;
            // Элемент формы
            var form = self.element.find('form');

            // URI редактируемого объекта
            this.model.object = form.find('[name=object]').val();
            // Кнопка сохранения
            this.submit_btn = this.element.find('.submit');
            //
            // Обновление формы (при изменении атрибутов)
            //
            this.model
            .on('change-attrib:uri', function(value){
                form.find('[data-name="uri"]:first').text(value);
                value = value ? self.getLastParam(value) : '<корень>';
                form.find('[data-name="name"]:first').text(value);
            })
            .on('change-attrib:proto', function(value){
                form.find('input[name="attrib[proto]"]:first').val(value);
                if (!value) value = '';
                form.find('[data-name="proto-uri"]:first').text(value).attr('href', value);

                value = value ? self.getLastParam(value) : 'Entity';
                form.find('[data-name="proto-show"]:first').text(value);
            })
            .on('change-attrib:value', function(value){
                form.find('textarea[name="attrib[value]"]:first').val(value);
            })
            .on('change-attrib:is_null', function(value){
                if (value){
                    form.find('[data-name="is_null"]:first').addClass('active');
                    form.find('input[name="attrib[is_null]"]:first').val(1);
                }else{
                    form.find('[data-name="is_null"]:first').removeClass('active');
                    form.find('input[name="attrib[is_null]"]:first').val(0);
                }
            })
            .on('change-attrib:is_file', function(value){
                if (value){
                    form.find('[data-name="is_file"]:first').addClass('active');
                    form.find('input[name="attrib[is_file]"]:first').val(1);
                }else{
                    form.find('[data-name="is_file"]:first').removeClass('active');
                    form.find('input[name="attrib[is_file]"]:first').val(0);
                }
            })
            .on('change-attrib:order', function(value){
                form.find('input[name="attrib[order]"]:first').val(value);
            })
            .on('change-attrib:lang', function(value){
                form.find('input[name="attrib[lang]"]:first').val(value);
                value = value ? self.getLastParam(value) : 'Все';
                form.find('[data-name="lang-show"]:first').text(value);
            })
            .on('change-attrib:owner', function(value){
                form.find('input[name="attrib[owner]"]:first').val(value);
                value = value ? self.getLastParam(value) : 'Общий';
                form.find('[data-name="owner-show"]:first').text(value);
            })
            .on('change-attrib:date', function(value){
                form.find('[data-name="date"]:first').text(value);
            })
            .on('change-attrib:is_link', function(value){
                form.find('input[name="attrib[is_link]"]:first').prop("checked", value);
            })
            .on('change-attrib:is_logic', function(value){
                form.find('input[name="attrib[is_logic]"]:first').prop("checked", value);
            })
            .on('change-attrib:is_hidden', function(value){
                form.find('input[name="attrib[is_hidden]"]:first').prop("checked", value);
            })
            .on('change-attrib:override', function(value){
                form.find('input[name="attrib[override]"]:first').prop("checked", value);
            })
            .on('change-attrib', function(change){
                if (change){
                    form.find('.submit').text('Сохранить').removeClass('btn-disable');
                    form.find('.reset').removeClass('hide');
                }else{
                    form.find('.submit').text('Сохранено').addClass('btn-disable');
                    form.find('.reset').addClass('hide');
                }
            });

            this.model.
            on('change-error:fatal', function(value){
                form.find('.submit-message').text(value);
            }).
            on('change-error', function(error){
                form.find('.submit').text('Сохранить ещё раз').removeClass('btn-disable');
                form.find('.reset').removeClass('hide');
                form.find('.item-'+error.name).addClass('error').find('.error-message').text(error.value);
            }).
            on('clear-error', function(value){
                form.find('.submit-message').text('');
                form.find('.error').removeClass('error');
            }).
            on('change-process:start', function(value){
                //self.element.find('.submit-message').text(value);
                if (value){
                    form.find('.submit').text('Сохраняется...').removeClass('btn-disable');
                    form.find('.reset').addClass('hide');
                }else{
                    form.find('.submit').text('Сохранено').addClass('btn-disable');
                    form.find('.submit-message').text('');
                }
            }).
            on('change-process:percent', function(value){
                form.find('.submit-message').text(value + '%');
            });

            //
            // Обновление атриубтов (при изменении формы)
            //
			form
            .on('change', '.attrib[type=checkbox]', function(e) {
                self.model.set_attrib($(this).attr('data-name'), $(this).prop("checked"));
			})
            .on('keyup', '.attrib[type=text]', function(e) {
                self.model.set_attrib($(this).attr('data-name'), $(this).val());
			})
            .on('change', '[type=file]', function(e) {
                self.model.set_attrib('value', self.getLastParam($(this).val()));
                self.model.set_attrib('is_file', true);
                self.model.set_attrib('is_null', false);
			})
            .on('keyup', 'textarea.attrib', function(e) {
				self.model.set_attrib('value', $(this).val());
                self.model.set_attrib('is_null', false);
                self.model.set_attrib('is_file', false);
			})
            .on('input', 'textarea.attrib', function(e) {
				self.model.set_attrib('value', $(this).val());
                self.model.set_attrib('is_null', false);
                self.model.set_attrib('is_file', false);
			})
            .on('click', '.default', function(e){
                e.preventDefault();
                if (!$(this).hasClass('active')){
                    self.model.set_attrib('value', self.model.attrib_start.value_null);
                    self.model.set_attrib('is_file', self.model.attrib_start.is_file_null);
                    self.model.set_attrib('is_null', true);
                    // Обнуление поля выбора файла
                    var file_input = form.find('input[name="attrib[file]"]');
                    file_input.replaceWith(file_input.clone(true));
                }
            })
            .on('click', '.reset', function(e){
                e.preventDefault();
                self.model.init(self.model.attrib_start);
                self.model.clear_errors();
            })
            .on('click', '.submit', function(e){
                e.preventDefault();
                if (!$(this).hasClass('btn-disable')){
                    // Ошибка при обработки запроса
                    form.ajaxError(function(e, jqxhr, settings, exception) {
                        if (settings.owner == "boolive.Attribs"){
                            self.model.set_process('start', false);
                            self.model.set_error('fatal', 'Не удалось сохранить');
                        }
                    });
                    form.ajaxSubmit({
                        url: "&direct="+self.options.view_uri+'&call=save',
                        owner: "boolive.Attribs",
                        type: 'post',
                        dataType: 'json',
                        beforeSubmit: function(arr, form, options){
                            self.model.set_process('start', true);
                            self.model.clear_errors();
                        },
                        uploadProgress: function(e, position, total, percent){
                            self.model.set_process('percent', percent);
                            //self.submit_msg.text(percent+'%');
                        },
                        success: function(responseText, statusText, xhr, $form){
                            if (responseText.out.error){
                                self.model.set_process('start', false);
                                for(var e in responseText.out.error){
                                    if (e == 'file'){
                                        self.model.set_error('value', responseText.out.error[e]);
                                    }else{
                                        self.model.set_error(e, responseText.out.error[e]);
                                    }
                                }
                            }else{
                                self.model.set_process('start', false);
                                self.model.init(responseText.out.attrib);
                            }
                        }
                    });
                }
            }).
            on('click', '[data-name="proto-uri"]:first', function(e){
                e.preventDefault();
                self.element.trigger('before-entry-object', $(this).attr('href'));
            }).
            on('click', '[data-name="proto-show"]:first', function(e){
                e.preventDefault();
                alert('Выбор прототипа не реализован');
            }).
            on('click', '[data-name="lang-show"]:first', function(e){
                e.preventDefault();
                alert('Выбор языка ещё не реализован');
            }).
            on('click', '[data-name="owner-show"]:first', function(e){
                e.preventDefault();
                alert('Выбор владельца не реализован');
            });

            //
            // Загрзка начальны данных
            //
            this._call('load', {object: this.model.object}, function(result, textStatus, jqXHR){
                self.model.init(result.out.attrib);
            });

            // Кнопка сохранения
            this.submit_btn = this.element.find('.submit');
            this.submit_msg = this.element.find('.submit-message');
        },

		destroy: function() {
			$.boolive.AjaxWidget.prototype.destroy.call(this);
		},

        getLastParam: function(uri){
            var m = uri.match(/([^\\\/]*)$/)
            if (m){
                return m[1];
            }else{
                return '';
            }
        }
	})
})(jQuery);