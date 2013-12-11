/**
 * Заготовка jQueryUI виджета с наследованием boolive.Widget
 * Переименуйте WidgetName на своё имя виджета.
 *
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.Editor", $.boolive.BaseExplorer, {
        /**
         * Данные и методы их обновления.
         * При установки атрибутов генерируются события. Обработчиками обновляется форма
         */
        model: null,

        _create: function() {
            $.boolive.BaseExplorer.prototype._create.call(this);
            var self = this;
            this.element.on('click', '.Editor__attribs-btn', function(e){
                var attribs = self.element.find('.Editor__attribs:first');
                $(this).toggleClass('active');
                if ($(this).hasClass('active')){
                    attribs.show();
                }else{
                    attribs.hide();
                }
            });

            this.model = new BooliveModel();


            // Изменение в виде
            this.element.on('input propertychange', '.Editor__attribs-uri-name', function(e){
                self.model.set_attrib('name', $(this).val());
            });
            // Выбор родителя
            this.element.on('click', '.Editor__attribs-uri-parent', function(e){
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

            // Изменение в модели
            this.model
            .on('change-attrib:parent', function(value){
                self.element.find('.Editor__attribs-uri-parent').text(value+'/').show();
            })
            .on('change-attrib:name', function(value){
                self.element.find('.Editor__attribs-uri-name').val(value);
            })
            .on('change-attrib', function(change){
                if (change){
                    self.callParents('change', [self.model.attrib.uri], null, true);
                }else{
                    self.callParents('nochange', [self.model.attrib.uri], null, true);
                }
            });

            this.model.
            on('change-error:fatal', function(value){
                alert('ERROR!!!');
            });
            this.init_attribs();
        },

        init_attribs: function(){
            // Загрузить с сервера атрибуты объекта
            var uris = this.options.object.split('/');
            this.model.init({
                uri:this.options.object,
                name: uris.pop(),
                parent: uris.join('/')
            });
        },

        call_save: function(e){
            var self = this;
            if (this.model.is_change()){

                var url = /^[a-z]+:\/\//i.test(self.options.object) ? self.options.object : window.location.protocol + '//' + window.location.host + self.options.object;
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    accepts: {json: 'application/json'},
                    url: url,
                    data: {
                        method: 'PUT',
                        entity: self.model.attrib
                    },
                    success: function(result, textStatus, jqXHR){
                        self.element.find('.Editor__attribs-error').text('');
                        self.callParents('updateURI', {uri: self.model.attrib.uri, new_uri:result.result.uri}, null, true);
                        self.model.init(result.result);
                        // Сохранение всех, кто до "нас" не успел начать сохраняться
                        self.callParents('save', null, null, true);
                    },
                    error: function(jqXHR, textStatus){
                        try{
                            var result = $.parseJSON(jqXHR.responseText);
                        }catch(e){
                            console.log(jqXHR.responseText);
                        }
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
                            _.each(result.error.children._attribs.children, function(value, key){
                                self.element.find('.Editor__attribs-error-'+key).text(getErrorMessage(value));
                                console.log(getErrorMessage(value), value, key);
                            });
                        }
                    }
                });
                // Запрет сохраненять подчиенным, пока сами не сохранимся. Посел сохранения себя вызов сохранения для остальных
                return false;
            }
        },

        afterReload: function(){
            $.boolive.BaseExplorer.prototype.afterReload.call(this);
            this.init_attribs();
        }
    })
})(jQuery, _);