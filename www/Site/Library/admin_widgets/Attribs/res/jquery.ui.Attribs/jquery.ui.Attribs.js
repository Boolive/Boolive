/**
 * Виджет - редактор атрибутов
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _) {
    $.widget("boolive.Attribs", $.boolive.Widget, {
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
                this.attrib = _.clone(attribs);
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
            },

            is_change_attrib: function(name){
                return (this.attrib[name]!=this.attrib_start[name]);
            }
        },

        submit_btn: null,
        submit_msg: null,
        form: null,

        /**
         * Конструктор виджета
         * @private
         */
        _create: function() {
            $.boolive.Widget.prototype._create.call(this);

            var self = this;
            // Элемент формы
            var form = this.form = self.element.find('form');

            // URI редактируемого объекта
            this.model.object = this.options.object = form.find('[name=object]').val();
            // Кнопка сохранения
            this.submit_btn = this.element.find('.submit');
            //
            // Обновление формы (при изменении атрибутов)
            //
            this.model
            .on('change-attrib:proto', function(value){
                var item = form.find('.item-proto');
                item.find('input[name="attrib[proto]"]:first').val(value);
                if (value!='null'){
                    item.find('[data-name="proto-uri"]').text(value?value:'/').attr('href', value).show();
                    item.find('[data-name="proto-delete"]:first').show();
                }else{
                    item.find('[data-name="proto-uri"]').text('Выбрать...');
                    item.find('[data-name="proto-delete"]').hide();
                }
            })
            .on('change-attrib:name', function(value){
                form.find('input[name="attrib[name]"]:first').val(value);
            })
//            .on('change-attrib:uri', function(value){
//                if (value){
//                    form.find('.item-name [data-name="uri"]:first').text(self.getDirParam(value)).show();
//                }else{
//                    form.find('.item-name [data-name="uri"]:first').hide();
//                }
//            })
            .on('change-attrib:parent', function(value){
                form.find('input[name="attrib[parent]"]:first').val(value);
                if (value!='null'){
                    form.find('[data-name="parent-uri"]').text(value+'/').attr('href', value).show();
                }else{
                    form.find('[data-name="parent-uri"]').text('&nbsp').hide();
                }
            })

            .on('change-attrib:value', function(value){
                var input = form.find('textarea[name="attrib[value]"]:first');
                if (!input.is(":focus")) input.val(value);
            })
            .on('change-attrib:is_null', function(value){
                if (value){
                    form.find('[data-name="is_null"]:first').addClass('selected');
                    form.find('input[name="attrib[is_null]"]:first').val(1);
                }else{
                    form.find('[data-name="is_null"]:first').removeClass('selected');
                    form.find('input[name="attrib[is_null]"]:first').val(0);
                }
            })
            .on('change-attrib:is_file', function(value){
                if (value){
                    form.find('[data-name="is_file"]:first').addClass('selected');
                    form.find('input[name="attrib[is_file]"]:first').val(1);
                }else{
                    form.find('[data-name="is_file"]:first').removeClass('selected');
                    form.find('input[name="attrib[is_file]"]:first').val(0);
                    self.clearFileInputField();
                }
            })
            .on('change-attrib:order', function(value){
                form.find('input[name="attrib[order]"]:first').val(value);
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
            .on('change-attrib:class', function(value){
                form.find('.class_name').text(value);
            })
            .on('change-attrib:class_self', function(value){
                form.find('.class_name_self').text(value);
            })
            .on('change-attrib:is_hidden', function(value){
                form.find('input[name="attrib[is_hidden]"]:first').prop("checked", value);
            })
            .on('change-attrib:override', function(value){
                form.find('input[name="attrib[override]"]:first').prop("checked", value);
            })
            .on('change-attrib', function(change){
                if (change){
                    self.callParents('change', [self.model.object]);
//                    form.find('.submit').text('Сохранить').removeClass('btn-disable');
//                    form.find('.reset').removeClass('hide');
                }else{
                    self.callParents('nochange', [self.model.object]);
//                    form.find('.submit').text('Сохранено').addClass('btn-disable');
//                    form.find('.reset').addClass('hide');
                }
            });

            this.model.
            on('change-error:fatal', function(value){
                form.find('.submit-message').text(value);
            }).
            on('change-error', function(error){
//                self.callParents('change', [self.model.object]);
//                form.find('.submit').text('Сохранить ещё раз').removeClass('btn-disable');
//                form.find('.reset').removeClass('hide');
                form.find('.item-'+error.name).addClass('error').find('.error-message').text(error.value);
            }).
            on('change-error:_other_', function(value){
                form.find('.submit-message').text(value);
            }).
            on('clear-error', function(value){
                form.find('.submit-message').text('');
                form.find('.error').removeClass('error');
            }).
            on('change-process:start', function(value){
                //self.element.find('.submit-message').text(value);
//                if (value){
//                    form.find('.submit').text('Сохраняется...').removeClass('btn-disable');
//                    form.find('.reset').addClass('hide');
//                }else{
//                    form.find('.submit').text('Сохранено').addClass('btn-disable');
//                    form.find('.submit-message').text('');
//                }
            }).
            on('change-process:percent', function(value){
//                form.find('.submit-message').text(value + '%');
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
                form.find('.fileupload:first').addClass('selected');
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
                if (!self.model.attrib['is_null']){
                    self.model.set_attrib('value', self.model.attrib_start.value_null);
                    self.model.set_attrib('is_file', self.model.attrib_start.is_file_null);
                    self.model.set_attrib('is_null', true);
                }else{
                    self.model.set_attrib('is_null', false);
                }
            })
            .on('click', '.is_file', function(e){
                e.preventDefault();
                self.model.set_attrib('is_null', false);
                self.model.set_attrib('is_file', !self.model.attrib['is_file']);
            }).
            // Выбор прототипа
            on('click', '.item-proto [data-name="proto-uri"]', function(e){
                e.preventDefault();
                e.stopPropagation();
                self.callParents('openWindow', [null,
                    {
                        url: "/",
                        data: {
                            direct: self.options.view+'/SelectObject', // uri выиджета выбора объекта
                            object: self.model.attrib['proto']!='null'?self.model.attrib['proto']:'/Library' //какой объект показать
                        }
                    },
                    function(result, params){
                        if (result == 'submit' && 'selected' in params){
                            self.model.set_attrib('proto', _.first(params.selected));
                        }
                    }
                ]);
            }).
            // Удаление прототипа
            on('click', '.item-proto [data-name="proto-delete"]', function(e){
                e.preventDefault();
                self.model.set_attrib('proto', 'null');
            }).
            // Выбор родителя
            on('click', '.item-parent [data-name="parent-uri"]', function(e){
                e.preventDefault();
                e.stopPropagation();
                self.callParents('openWindow', [null,
                    {
                        url: "/",
                        data: {
                            direct: self.options.view+'/SelectObject', // uri выиджета выбора объекта
                            object: self.model.attrib['parent']!='null'?self.model.attrib['parent']:'' //какой объект показать
                        }
                    },
                    function(result, params){
                        if (result == 'submit' && 'selected' in params){
                            self.model.set_attrib('parent', _.first(params.selected));
                        }
                    }
                ]);
            });
//            on('click', '[data-name="lang-show"]:first', function(e){
//                e.preventDefault();
//                e.stopPropagation();
//                self.before('openWindow', [null,
//                    {
//                        url: "/",
//                        data: {
//                            direct: self.options.view+'/SelectObject', // uri выиджета выбора объекта
//                            object: '/Languages' //какой объект показать
//                        }
//                    },
//                    function(result, params){
//                        if (result == 'submit' && 'selected' in params){
//                            alert('Выбор языка ещё нереализован');
//                            //self.object_select = params.selected;
//                            //self._add();
//                        }
//                    }
//                ]);
//
//            }).
//            on('click', '[data-name="owner-show"]:first', function(e){
//                e.preventDefault();
//                e.stopPropagation();
//                self.before('openWindow', [null,
//                    {
//                        url: "/",
//                        data: {
//                            direct: self.options.view+'/SelectObject', // uri выиджета выбора объекта
//                            object: '/Members' //какой объект показать
//                        }
//                    },
//                    function(result, params){
//                        if (result == 'submit' && 'selected' in params){
//                            alert('Выбор владельца нереализован');
//                            //self.object_select = params.selected;
//                            //self._add();
//                        }
//                    }
//                ]);
//
//            });

            //
            // Загрзка начальны данных
            //
            this.callServer('load', {object: self.model.object}, function(result, textStatus, jqXHR){
                self.model.init(result.out.attrib);
            });

            // Кнопка сохранения
            this.submit_btn = this.element.find('.submit');
            this.submit_msg = this.element.find('.submit-message');
        },

        call_save: function(e){
            var self = this;
            // Элемент формы
            var form = this.form = self.element.find('form');
            // Ошибка при обработки запроса
            form.ajaxError(function(e, jqxhr, settings, exception) {
                if (settings.owner == self.eventNamespace){
                    self.model.set_process('start', false);
                    self.model.set_error('fatal', 'Не удалось сохранить');
                }
            });
            form.ajaxSubmit({
                url: "&direct="+self.options.view+'&call=save',
                owner: self.eventNamespace,
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

                        if (_.isObject(responseText.out.error.children._attribs) && _.isObject(responseText.out.error.children._attribs.children)){
                            var children = responseText.out.error.children._attribs.children;
                            for(var e in children){
                                if (e == 'file'){
                                    self.model.set_error('value', self.errorMessage(children[e], true));
                                }else{
                                    self.model.set_error(e, self.errorMessage(children[e], true));
                                }
                            }
                        }
                        self.submit_msg.text(self.errorMessage(responseText.out.error, false));
                        self.callParents('change', [self.model.object]);
//                                for(var e in responseText.out.error){
//                                    if (e == 'file'){
//                                        self.model.set_error('value', responseText.out.error[e]);
//                                    }else{
//                                        self.model.set_error(e, responseText.out.error[e]);
//                                    }
//                                }
                    }else{
                        if (self.model.is_change_attrib('name') || self.model.is_change_attrib('parent')){
                            self.callParents('setState', [{object: responseText.out.attrib.uri}, true]);
                        }
                        self.model.set_process('start', false);
                        self.model.init(responseText.out.attrib);

                    }
                }
            });
        },

        clearFileInputField: function(){
            var file_input = this.form.find('input[type="file"]:first');
            file_input.replaceWith(file_input.val('').clone(true));
            this.form.find('.fileupload:first').removeClass('selected');
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
        },

        /**
         * Последний параметр в pathname URI
         * @param uri
         * @return {*}
         */
        getDirParam: function(uri){
            var m = uri.match(/^(.*\/)[^\\\/]*$/)
            if (m){
                return m[1];
            }else{
                return '';
            }
        },

        errorMessage: function(error, sub, glue){
            if (!error.children || _.isEmpty(error.children) || _.isUndefined(sub)){
                return error.message;
            }else{
                var result = '';
                if (!glue) glue = '';
                for (var i in error.children){
                    if (!result) result+=glue;
                    result+= this.errorMessage(error.children[i], sub, glue);
                }
                return result;
            }
        }
    })
})(jQuery, _);