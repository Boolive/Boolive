/**
 * Заготовка jQueryUI виджета с наследованием boolive.Widget
 * Переименуйте WidgetName на своё имя виджета.
 *
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.BaseExplorer", $.boolive.Widget, {
                /**
         * Данные и методы их обновления.
         * При установки атрибутов генерируются события. Обработчиками обновляется форма
         */
        model: null,

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            this.init_sortable();
            this.call_setState({target: this, direct: 'children'}, this.callParents('getState'), {selected: true});

            var self = this;
            this.element.on('click', '.BaseExplorer__proto', function(e){
                e.preventDefault();
                self.callParents('setState', [{object:  $(this).attr('data-o'), select: 'property'}]);
            });
            this.element.on('click', '.BaseExplorer__show-entity', function(e){
                e.preventDefault();
                self.callParents('setState', [{object:  self.options.object, select:$(this).attr('data-select')}]);
            });

            this.element.on('click', '.BaseExplorer__attribs-btn', function(e){
                var attribs = self.element.find('.BaseExplorer__attribs:first');
                $(this).toggleClass('active');
                if ($(this).hasClass('active')){
                    attribs.show();
                }else{
                    attribs.hide();
                }
            });

            this.model = new BooliveModel();

            // Изменение в виде
            this.element.on('input propertychange', '.BaseExplorer__attribs-uri-name', function(e){
                self.model.set_attrib('name', $(this).val());
            });
            // Выбор родителя
            this.element.on('click', '.BaseExplorer__attribs-uri-parent', function(e){

                var selected = self.model.attrib['parent']!='null'?self.model.attrib['parent']:'';
                var object = selected;
                if (selected.length > 0){
                    object = self.getDir(selected);
                }

                e.preventDefault();
                e.stopPropagation();
                self.callParents('openWindow', [null,
                    {
                        url: "/",
                        data: {
                            direct: self.options.view+'/SelectObject', // uri выиджета выбора объекта
                            object: object,
                            selected: [selected]
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
                if (value == null){
                    self.element.find('.BaseExplorer__attribs-uri-parent').text('').hide();
                }else{
                    self.element.find('.BaseExplorer__attribs-uri-parent').text(value+'/').show();
                }
            })
            .on('change-attrib:name', function(value){
                var inp = self.element.find('.BaseExplorer__attribs-uri-name');
                if (inp.val() !== value) inp.val(value);
            })
            .on('change-attrib', function(change){
                if (change){
                    self.callParents('change', [self.model.attrib.uri], null, true);
                }else{
                    self.callParents('nochange', [self.model.attrib.uri], null, true);
                    self.element.find('.BaseExplorer__attribs-error').text('');
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
                parent: uris.length==0 ? null : uris.join('/')
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
                        self.element.find('.BaseExplorer__attribs-error').text('');
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
                                self.element.find('.BaseExplorer__attribs-error-'+key).text(getErrorMessage(value));
                                console.log(getErrorMessage(value), value, key);
                            });
                        }
                    }
                });
                // Запрет сохраненять подчиенным, пока сами не сохранимся. Посел сохранения себя вызов сохранения для остальных
                return false;
            }
        },

        init_sortable: function(){
            var self = this;
            var select = this.callParents('getState').select;
            if (select == 'structure' || select == 'property'){
                this.element.find('.BaseExplorer__list:first').sortable({
                    distance: 15,
                    handle: '.Item__select',
                    placeholder: 'BaseExplorer__item-placeholder',
                    //forceHelperSize: false,
                    update: function(event, ui) {
                        var object_uri = {};
                        var next_uri = {};
                        var next;
                        object_uri['uri'] = ui.item.attr('data-o');
                        if(ui.item.next().length > 0){
                            next = ui.item.next();
                            next_uri['uri'] = next.attr('data-o');
                            next_uri['next'] = 1;
                        }else{
                            next  = ui.item.prev();
                            next_uri['uri'] = next.attr('data-o');
                            next_uri['next'] = 0;
                        }
                        self.callServer('saveOrder', {
                            saveOrder:{object_uri:object_uri, next_uri:next_uri},
                            object: self.options.object
                        });
                    },
                    start: function( event, ui ) {
    //                    ui.placeholder.css(ui.item.css(['width', 'height']));
                        ui.placeholder.outerWidth(ui.item.outerWidth());
                        ui.placeholder.outerHeight(ui.item.outerHeight());
                    }
                });
            }
        },
        /**
         * Выделение объекта
         */
        call_setState: function(caller, state, changes){

            if (caller.direct == 'children'){
                if ($.isPlainObject(changes) && ('selected' in changes)){
                    this.element.find('.Item_selected').removeClass('Item_selected');
                    if (state.selected){
                        var element = this.element;
                        _.each(state.selected, function(s){
                            element.find('.Item[data-o="'+s+'"]').addClass('Item_selected');
                            // @todo Если объект не найден, то загрузить его с сервера. Сервер должен сообщеить после какого объекта тот отображается
                        });
                    }
                }
            }
        },

        call_object_update: function(caller, info){
            var self = this;
            if (!_.isUndefined(info[this.options.object])){
                //console.log(info[this.options.object]);
            }else{
                var uri;
                var reload = false;
                for (uri in info){
                    reload = reload || (this.element.find('[data-o="'+uri+'"]').size()==0);
                }
                if (reload){
                    var s = self.callParents('getState');
                    this.reload({
                            object: this.options.object,
                            select: s.select,
                            page: s.page
                        }, {url:'/', success: function(){
                        self.init_sortable();
                    }});
                }
            }
        },

        call_changeFilter: function(caller){
            var self = this;
            var s = self.callParents('getState');
            this.reload({object: this.options.object, select: s.select}, {
                    success: function(){
                        self.afterReload();
                    },
                    url: '/'
                }
            );
        },

        afterReload: function(){
            this.init_sortable();
            this.init_attribs();
        }
    })
})(jQuery, _);