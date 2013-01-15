/**
 * Виджет админки
 * Обрабатывает команды изменения состояния, истории браузера.
 * Реализует многоокнность админки с общими для всех окон боковыми панелями (меню)
 * В один момент времени активно только одно окно и с ним взаимоедйсвтуют боковые панели
 * Для изменения состояния или выполнения других дейсвтий используется паттерн "Цепочка обязанностей" методами bofore*() after*()
 * Методом before команда передаётся родителям, методом after - подчиненным.
 * JQuery UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, root, undefined) {
	$.widget("boolive.Admin", $.boolive.AjaxWidget, {
        options: {
            basepath: '/admin'
        },
        // контейнер для окон. В нём первое окно. (объект jquery)
        _windows: null,
        // счётчик окон для идентификации
        _windows_cnt: 0,
        // текущее окно (объект jquery)
        _window_current: null,
        // в каком окне подчиенный виджет по его uuid
        _where_children: null,
        // Текущее состояние
        _state: null,

        _create: function() {
			$.boolive.AjaxWidget.prototype._create.call(this);

            // Расширение jquery объектов Admin
            var self = this;
            // Контейнер окон
            self._windows = self.element.find('.center');
            // Текущее окно
            self._window_current = self._windows.children(':first');
            // Состояние и подчиенные виджеты текущего окна
            self._window_current.data('state', {}).data('children', {}).attr('id', this._windows_cnt);
            self._state = self._window_current.data('state');
            // В каком окне подчиенный виджет по его uuid
            self._where_children = {};
            // Инициализация состояния
            self._init_state();

            // Отмена выделения при клике на свободную центральную область
            self.element.find('.center').click(function(e){
                self.before_setState({select: self._state.object});
            });

            // Назад/Вперед
            $(window).on("popstate", function(e){
                // Если история уазывает на другое окно, то
                if (history.state.window != self._state.window){
                    // Запрещаем переход, делая возврат
                    var dx = self._state.history_i - history.state.history_i;
                    history.go(dx);
                }else{
                    // В истории текущее окно, поэтому обновляем состояние
                    self._state.history_o = history.state.history_o;
                    self._state.history_i = history.state.history_i;
                    self._state.window = history.state.window;
                    self.before_setState(history.state, true);
                }
            });
            // Загрузка меню
            this.loadsub(this.element.find('.top'), '/', this._state, 'BreadcrumbsMenu', true);
            this.loadsub(this.element.find('.right'), '/', this._state, 'ProgramsMenu', true);
        },

        /**
         * Инициализация состояния
         * @private
         */
        _init_state: function(){
            var l = history.location || document.location;
            var expr = new RegExp('^'+this.options.basepath+'/(.*)$');
            var uri_args = this.getStateFromURI('object='+l.pathname.replace(expr, '$1'));
            if (typeof uri_args.object == 'string'){
                if (uri_args.object) uri_args.object = '/'+uri_args.object;
                this._state.object = uri_args.object;
                this._state.select = uri_args.object;
            }
            if (typeof uri_args.view_name == 'string'){
                this._state.view_name = uri_args.view_name;
            }else{
                this._state.view_name = null;
            }
            this._state.window = this._window_current.attr('id');
            this._state.history_o = 0; // начальный индекс истории текущего окна
            this._state.history_i = 0; // текущий индекс истории окна
            history.replaceState(this._state, null, null);
        },

        /**
         * Изменение состояния
         * @param state Новое состояние
         * @param without_history Признак, не создавать историю (true)
         */
        before_setState: function(state, without_history){
            var change = {};
            // Вход в объект
            if ('object' in state && state.object != this._state.object){
                this._state.object = state.object;
                change['object'] = true;
                // По умолчанию выделенный объект - в который вошли
                if (!('select' in state)){
                    state.select = state.object;
                }
            }
            // Выделение объекта
            if ('select' in state && state.select != this._state.select){
                this._state.select = state.select;
                change['select'] = true;
            }
            // Выбор вида
            if ('view_name' in state && state.view_name != this._state.view_name){
                this._state.view_name = state.view_name;
                change['view_name'] = true;
            }
            if (!$.isEmptyObject(change)){
                // Запись истории
                if (without_history!==true){
                    if (without_history!==true && ('object' in change || 'view_name' in change)){
                        this._state.history_i++;
                        history.pushState(this._state, null, this.getURIFromState(this._state));

                    }else{
                        history.replaceState(this._state, null, this.getURIFromState(this._state));
                    }
                }else{
                    history.replaceState(this._state, null, this.getURIFromState(this._state));
                }
                this.after('setState', [this._state, change]);
            }
        },

        /**
         * Получение текущего состояния
         * @return {*}
         */
        before_getState: function(){
            return this._state;
        },

        /**
         * Открытие окна
         * @param request
         * @param close_callback Функция обратного вызова при закрытии окна
         * @param id
         * @return Идентификатор окна
         */
        before_openWindow: function(id, request, close_callback){
            if (!id) id = ++this._windows_cnt;
            var self = this;
            // Есть ли требуемое окно?
            var window = this._windows.find('> #'+id+':last');
            if (window.length){
                // Скрыть текущее окно
                self._window_current.hide();
                // Показываем. Тег перенести в начало списка.
                self._windows.append(window);
                self.review_windows();
                self._window_current = self._windows.find('> #'+id+':last');
                self._state = self._window_current.data('state');
                self.refresh_state();
                window.show();
            }else{
                if (!(typeof request.data == 'object')) request.data = {};
                // Создание тега окна с ключём, загрузка содержимого по Ajax
                $.ajax({
                    type: 'POST',
                    url: (typeof request.url == 'string') ? request.url : self.options.default_url,
                    data: request.data,
                    dataType: 'json',
                    success: function(result, textStatus, jqXHR){
                        if (!result.links) result.links = [];
                        $.include(result.links, function(){
                            // Скрыть текущее окно
                            //self._window_current.hide();
                            self._windows.append('<div class="window" id="' + id + '">' + result.out + '</div>');
                            self.review_windows();
                            self._window_current = self._windows.find('> #'+id+':last');
                            self._window_current
                                .data('state', {
                                    object: request.data.object ? request.data.object : '',
                                    view_name: request.data.view_name ? request.data.view_name : null,
                                    window: id,
                                    history_o: self._state.history_i,
                                    history_i: self._state.history_i+1
                                }).data('children', {})
                                .data('close_callback', close_callback);
                            self._window_current.data('state').select = request.data.select ? request.data.select : self._window_current.data('state').object;
                            self._state = self._window_current.data('state');
                            // Открытие окна фиксируем в истории браузера
                            history.pushState(self._state, null, self.getURIFromState(self._state));
                            // Сообщаем всем о новосм состоянии
                            self.refresh_state();
                            // Событие для автоматического подключение плагинов в загруженном html
                            $(document).trigger('load-html', [self._window_current]);
                        });
                    }
                });
            }
            return id;
        },

        /**
         * Закрытие текущего окна
         * @param result
         * @param params
         */
        before_closeWindow: function(result, params){
            // Удаление тега окна
            var window = this._windows.find('> :last');
            var dh = 0;
            if (window.length){
                dh = window.data('state').history_o - window.data('state').history_i;
                if (typeof window.data('close_callback') == 'function'){
                    window.data('close_callback')(result, params);
                }
                window.remove();
                this.review_windows();
            }
            // Открытие первого окна с спике
            this._window_current = this._windows.find('> :last');
            this._state = this._window_current.data('state');
            this._window_current.show();
            // Сброс истории барузера до момента смены окна
            if (dh!=0) history.go(dh);
            // Сообщаем всем о смене состояния
            this.refresh_state(false);
        },

        review_windows: function(){
            var left = 0;
            var dl = 10;
            this._windows.children().each(function(){
                $(this).css('margin-left', left +'px');
               left+=dl;
            });
        },

        /**
         * Добавление подчиненного виджета
         * Если виджет подчиенный окна, то он добавляется в список подчиенных окна,
         * иначе в общий список. В общий список, обычно, попадают меню админки.
         * @param widget Объект виджета
         * @return {Boolean}
         * @private
         */
        _addChild: function(widget){
            if (widget != this){
                var window = widget.element.closest(this._windows.children());
                if (window.length){
                    if (typeof window.data('children') != 'object') window.data('children', {});
                    window.data('children')[widget.uuid] = widget;
                    this._where_children[widget.uuid] = window.data('children');
                }else{
                    this._children[widget.uuid] = widget;
                }
                widget._parent = this;
                return true;
            }
            return false;
        },

        /**
         * Удаление подчиненного виджета
         * @param widget Объект виджета
         * @return {Boolean}
         * @private
         */
        _deleteChild: function(widget){
            if (widget != this){
                delete this._children[widget.uuid];
                if (this._where_children[widget.uuid]){
                    // удаление из списка подчиенных соответсвующего окна. Окно определяется по widget.uuid
                    delete this._children[widget.uuid][widget.uuid];
                    delete this._where_children[widget.uuid];
                }
                return true;
            }
            return false;
        },

        /**
         * Вызов дейсвтия у родителя
         * Если инициатором является виджет неактивного окна, то вызов игнорируется
         * @param action Название действия
         * @param args Аргументы
         * @param target Объект, иницировавший вызов действия
         */
        before: function(action, args, target){
            if (!target) target = null;
            var window = null;
            if (!target){
                window = target.element.closest(this._windows.children());
            }
            // Обработка если target из текущего окна или вне окна
            if (!window || !window.length || window == this._window_current){
                var stop = undefined;
                if (target && target!=this){
                    if ($.isFunction(this['before_'+action])){
                        stop = this['before_'+action].apply(this, args);
                    }
                }
                if (stop !== undefined){
                    return stop;
                }else
                if (this._parent){
                    return this._parent.before(action, args, target || this);
                }
            }
            return undefined;
        },

        /**
         * Вызов действия завершения у всех подчиненных
         * Вызываются общие подчиенные и подчиенные активного окна.
         * @param action Название действия
         * @param args Аргументы
         * @param target Объект, иницировавший вызов действия
         */
        after: function(action, args, target, all){
            var stop = false;
            if (target && target!=this){
                // По target опредлить окно и для него вызывать обработчик
                // Если target вне окон, то используется текущее окно
                if ($.isFunction(this['after_'+action])){
                    stop = this['after_'+action].apply(this, args);
                }
            }
            if (!stop){
                // Отправлять виджетам текущего окна и виджетам вне окна
                if (all!==false){
                    var children = this._window_current.data('children');
                    for (var child in children){
                        children[child].after(action, args, target || this);
                    }
                }
                for (var child in this._children){
                    this._children[child].after(action, args, target || this);
                }
            }
        },

        /**
         * Сообщимть всем о состоянии
         * Делается вид, что оно полностью изменилось, чтоб всех принудить обновиться
         * @private
         */
        refresh_state: function(all){
            this.after('setState', [this._state, {object: true, select: true, view_name: true}], undefined, all);
        },

        /**
         * Парсер строки с URI аргументами в массив
         * @param uri
         * @return {Array}
         * @private
         */
        getStateFromURI: function(uri){
            var ch1 = '=';
            var ch2 = '&';
            var list = uri.split(ch2);
            var result = [];
            for(var i = 0; i < list.length; i++){
                var tmp = list[i].split(ch1);
                result[decodeURI(tmp[0])] = decodeURI(tmp[1]).replace(/[+]/g, ' ');
            }
            return result;
        },

        /**
         * Создание URI из состояния
         * @param state
         * @return {String}
         */
        getURIFromState: function(state){
            var uri = this.options.basepath + state.object;
            if (!state.object) uri = uri + '/';
            if (state.view_name){
                uri = uri + '&view_name=' + state.view_name;
            }
            return uri;
        }
	})
})(jQuery, window);
