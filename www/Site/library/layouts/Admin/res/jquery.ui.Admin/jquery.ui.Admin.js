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
(function($, root, _, undefined) {
	$.widget("boolive.Admin", $.boolive.Widget, {
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
        _state: null, // {object: '', selected: [], view_name: '', window: '#id', select:'structure' }
        // Запоминание фида и типа выборки для объектов
        _remember_select: null,
        _remember_view: null,

        _create: function() {
			$.boolive.Widget.prototype._create.call(this);

            // Расширение jquery объектов Admin
            var self = this;
            self.options.basepath = self.element.attr('data-base');
            self.element.children('.sidebar').touchScroll();
            // Контейнер окон
            self._windows = self.element.find('> .center');
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
            self.element.click(function(e){
                self.call_setState({target: self, direct: 'parents'}, {selected: self._state.object}); //before
            });


            //self.element.click();

            // Назад/Вперед
            $(window).on("popstate", function(e){
                // Если история уазывает на другое окно, то
                if (history.state.window != self._state.window){
                    // Запрещаем переход, делая возврат
                    var dx = self._state.history_i - history.state.history_i;
                    history.go(dx);
                }else{
                    if (!_.isEqual(self._state, history.state)){
                        // В истории текущее окно, поэтому обновляем состояние
                        self._state.history_o = history.state.history_o;
                        self._state.history_i = history.state.history_i;
                        self.call_setState({target: self, direct: 'parents'}, history.state, true);  //before
                        $(window).trigger('after_popsate', history.state);
                    }
                }
            });
            // Загрузка меню
//            this.load(this.element.find('.top'), true, this.options.view + '/BreadcrumbsMenu', this._state);
//            this.load(this.element.find('.right'), true, this.options.view + '/ProgramsMenu', this._state);
            // Обработка Ajax ошибок
            this.element.on('ajaxError' + this.eventNamespace, function(e, jqxhr, settings, exception) {
                if(!settings.isErrorCaught) {
                    alert(settings.owner+"\n\n" + exception.message + "\n\n" + jqxhr.responseText);
                    e.stopPropagation();
                }
            });
        },

        /**
         * Инициализация состояния
         * @private
         */
        _init_state: function(){
            var l = history.location || document.location;
            var expr = new RegExp('^'+this.options.basepath+'(/|$)(.*)$');
            var uri_args = this.getStateFromURI('object='+l.pathname.replace(expr, '$2'));
            if (typeof uri_args.object === 'string'){
                if (uri_args.object) uri_args.object = '/'+uri_args.object;
                this._state.object = uri_args.object;
                //this._state.select = uri_args.object;
                this._state.selected = [uri_args.object];
            }
            if (typeof uri_args.select === 'string'){
                this._state.select = uri_args.select;
            }else{
                this._state.select = 'structure';
            }
            if (typeof uri_args.view_name === 'string'){
                this._state.view_name = uri_args.view_name;
            }else{
                this._state.view_name = null;
            }
            this._remember_select = {};
            this._remember_view = {};
            if (/^views\//.test(this._state.view_name)){
                this._remember_select[this._state.object] = this._state.select;
                this._remember_view[this._state.object+'&'+this._state.select] = this._state.view_name;
            }
            this._state.window = this._window_current.attr('id');
            this._state.history_o = 0; // начальный индекс истории текущего окна (необходимо для сброса истории браузера при закрытии окна)
            this._state.history_i = 0; // текущий индекс истории окна
            history.replaceState(this._state, null, null);
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
         * Вызов дейсвтия у подчиненных.
         * Подчиенные неактивные окна игнорируются
         * @param call Название действия (функции)
         * @param args Аргументы
         * @param target Объект, иницировавший вызов действия. По умолчанию this
         * @param all Вызыв всех подчиненных или только внеоконных
         * @extends $.boolive.Widget.callChildren
         */
        callChildren: function(call, args, target, all){
            var stop = undefined;
            if (target/* && target!=this*/){
                // По target опредлить окно и для него вызывать обработчик
                // Если target вне окон, то используется текущее окно
                if ($.isFunction(this['call_'+call])){
                    var a = [{target: target, direct: 'children'}].concat(args);
                    stop = this['call_'+call].apply(this, a);
                }
            }
            var result = [];
            // Отправлять виджетам текущего окна и виджетам вне окна
            if (all!==false){
                var children = this._window_current.data('children');
                for (var child in children){
                    stop = children[child].callChildren(call, args, target || this);
                    if (stop !== undefined) result.push(stop);
                }
            }
            for (var child in this._children){
                stop = this._children[child].callChildren(call, args, target || this);
                if (stop !== undefined) result.push(stop);
            }
            return result.length? result : undefined;
        },

        /**
         * Вызов дейсвтия у родителей
         * Вызовы от неактивных окон игнорируются
         * @param call Название действия (функции)
         * @param args Аргументы
         * @param target Объект, иницировавший вызов действия. По умолчанию this.
         * @param up Признак, когда вызов дойдет до корневого объекты, перенаправить вызов всем подчиенным.
         * @extends $.boolive.Widget.callParents
         */
        callParents: function(call, args, target, up){
            if (!target) target = null;
            var window = null;
            if (!target){
                window = target.element.closest(this._windows.children());
            }
            // Обработка если target из текущего окна или вне окна
            if (!window || !window.length || window == this._window_current){
                return $.boolive.Widget.prototype.callParents.apply(this, [call, args, target, up]);
//                var stop = undefined;
//                if (!up && target && target!=this){
//                    if ($.isFunction(this['call_'+call])){
//                        var a = [{target: target, direct: 'parents'}].concat(args);
//                        stop = this['call_'+call].apply(this, a);
//                    }
//                }
//                if (stop !== undefined){
//                    return stop;
//                }else
//                if (this._parent){
//                    return this._parent.callParents(call, args, target || this, up);
//                }else
//                if (up){
//                    this.callChildren(call, args, target);
//                }
            }
            return undefined;
        },

        /**
         * Изменение состояния
         * @param state Новое состояние
         * @param without_history Признак, не создавать историю (true)
         */
        call_setState: function(caller, state, without_history){
            var change = {};
            // Вход в объект
            if ('object' in state && state.object != this._state.object){
                this._state.prev = this._state.object;
                this._state.object = state.object;

                change['object'] = true;
                // По умолчанию выделенный объект - в который вошли
                if (!('selected' in state)){
                    state.selected = state.object;
                    state.select_type = null; // Переопределение выделения
                }
            }
            // Выделение объекта
            if ('selected' in state/* && state.selected != this._state.selected*/){
                if (state.selected == null) state.selected = this._state.object;

                var current = this._state.selected.slice(0);
                if (!_.isArray(state.selected)) state.selected = [state.selected];

                if (state.select_type == 'toggle'){
                    var sel = this._state.selected;
                    var index;
                    _.each(state.selected, function(s){
                        if ((index = _.indexOf(sel, s))!=-1){
                            sel.splice(index, 1);
                        }else{
                            sel.push(s);
                        }
                    });
                }else
                if (state.select_type == 'remove'){
                    this._state.selected = _.without.apply(_, [this._state.selected].concat(state.selected));
                }else{
                    // Если не добавление к выделению, то удаление текущего выделения
                    if (state.select_type != 'add') this._state.selected = [];
                    // Добавление к выделению
                    this._state.selected = _.union(this._state.selected, state.selected);
                }
                // Если множественное выделение, то из выделения убирается родительский объект
                if (this._state.selected.length > 1 && _.indexOf(this._state.selected, this._state.object)!=-1){
                    if (_.isArray(this._state.object)){
                        this._state.selected = _.without.apply(_, [this._state.selected].concat(this._state.object));
                    }else{
                        this._state.selected = _.without(this._state.selected, this._state.object);
                    }
                }else
                if (this._state.selected.length==0){
                    this._state.selected = _.isArray(this._state.object)? this._state.object : [this._state.object];
                }
                // Выделение изменилось?
                if (_.difference(current, this._state.selected).length > 0 || _.difference(this._state.selected, current).length > 0){
                    change['selected'] = true;
                }
            }
            var object_str1 = this._state.object;
            // Смена выбора (что об объекте показывать)
            if ('select' in state){
//                this._state.select = state.select;
                this._remember_select[object_str1] = state.select;
//                change['select'] = true;
            }else
            if ('object' in change && /*this._state.select != 'structure' && */!('view_name' in state)){

                if (object_str1 in this._remember_select){
                    state.select = this._remember_select[object_str1];
                }else{
                    state.select = 'structure';
                }
            }
            if ('select' in state && state.select !== this._state.select){
                change['select'] = true;
                this._state.select = state.select;
            }

            // Выбор вида, если указан новый или сменился объект или режим выбора объекта
            if ('object' in change || 'view_name' in state || 'select' in change){
                var object_str = (_.isArray(this._state.object)? this._state.object.join(';') : this._state.object)+'&'+this._state.select;
                // Если вход в объект и не указан view_name и его нет в истории для объекта, то по умолчанию
                if (('object' in change || 'select' in change) && !('view_name' in state) && !(object_str in this._remember_view)){
                    if (!/^views\//.test(this._state.view_name) || 'select' in change) state.view_name = null;
                }
                // Если не указан view_name, но он есть в истории
                if (!('view_name' in state) && object_str in this._remember_view){
                    state.view_name = this._remember_view[object_str];
                }
                // Смена view_name
                if ('view_name' in state && state.view_name != this._state.view_name){
                    this._state.view_name = state.view_name;
                    change['view_name'] = true;
                }
                // view_name в историю, если его uri = ...views/*
                if (/^views\//.test(this._state.view_name)){
                    this._remember_view[object_str] = this._state.view_name;
                }
            }

            if (!$.isEmptyObject(change)){
                // Запись истории
                if (without_history!==true){
                    if (without_history!==true && ('object' in change || 'view_name' in change || 'select' in change)){
                        this._state.history_i++;
                        history.pushState(this._state, '', this.getURIFromState(this._state));

                    }else{
                        history.replaceState(this._state, '', this.getURIFromState(this._state));
                    }
                }else{
                    history.replaceState(this._state, '', this.getURIFromState(this._state));
                }
                this.callChildren('setState', [this._state, change]);
                this.callChildren('setStateAfter', [this._state, change]);
            }
        },

        /**
         * Получение текущего состояния
         * @return {*}
         */
        call_getState: function(){
            return this._state;
        },

        call_refreshState: function(){
            this.callChildren('setState', [this._state, {selected: true}]);
            this.callChildren('setStateAfter', [this._state, {selected: true}]);
        },

        /**
         * Открытие окна
         * @param settings
         * @param close_callback Функция обратного вызова при закрытии окна
         * @param id
         * @return Идентификатор окна
         */
        call_openWindow: function(caller, id, settings, close_callback){ //before
            if (!id) id = ++this._windows_cnt;
            var self = this;
            // Есть ли требуемое окно?
            var window = this._windows.find('> #'+id+':last');
            if (window.length){
                // Скрыть текущее окно
                self.callChildren('window_hide');
                self._window_current.hide();
                // Показываем. Тег перенести в начало списка.
                self._windows.append(window);
                self.review_windows();
                self._window_current = self._windows.find('> #'+id+':last');
                self._state = self._window_current.data('state');
                self.refresh_state();
                self.callChildren('window_show');
                window.show();
            }else{
                if (!(typeof settings.data == 'object')) settings.data = {};
                // Создание тега окна с ключём, загрузка содержимого по Ajax
                settings.owner = this.widgetName;
                settings.context = this.element;
                settings.type = 'POST';
                settings.dataType = 'json';
                settings.success = function(result, textStatus, jqXHR){
                    if (!result.links) result.links = [];
                    $.include(result.links, function(){
                        // Скрыть текущее окно
                        self._window_current.hide();
                        self._windows.append('<div class="window" id="' + id + '">' + result.out + '</div>');
                        self.review_windows();
                        self._window_current = self._windows.find('> #'+id+':last');
                        self._window_current
                            .data('state', {
                                object: settings.data.object ? settings.data.object : '',
                                view_name: settings.data.view_name ? settings.data.view_name : null,
                                select: settings.data.select ? settings.data.select : 'structure',
                                window: id,
                                history_o: self._state.history_i,
                                history_i: self._state.history_i+1

                            }).data('children', {})
                            .data('close_callback', close_callback);
                        //self._window_current.data('state').select = request.data.select ? request.data.select : self._window_current.data('state').object;
                        self._window_current.data('state').selected = settings.data.selected ? settings.data.selected : [self._window_current.data('state').object];
                        self._state = self._window_current.data('state');
                        // Открытие окна фиксируем в истории браузера
                        history.pushState(self._state, null, self.getURIFromState(self._state));
                        // Сообщаем всем о новосм состоянии
                        self.refresh_state(false);
                        // Событие для автоматического подключение плагинов в загруженном html
                        $(document).trigger('load-html', [self._window_current]);
                    });
                };
                $.ajax(settings);
            }
            return id;
        },

        /**
         * Закрытие текущего окна
         * @param result
         * @param params
         */
        call_closeWindow: function(caller, result, params){ //before
            // Удаление тега окна
            var window = this._windows.find('> :last');
            var dh = 0;
            var callback;
            if (window.length){
                dh = window.data('state').history_o - window.data('state').history_i;
                if (typeof window.data('close_callback') == 'function'){
                    callback = window.data('close_callback');

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
            if (callback){
                callback(result, params);
            }
        },

        review_windows: function(){
            var left = 0;
            var dl = 10;
//            this._windows.children().each(function(){
//                $(this).css('margin-left', left +'px');
//               left+=dl;
//            });
        },

        /**
         * Сообщимть всем о состоянии
         * Делается вид, что оно полностью изменилось, чтоб всех принудить обновиться
         * @private
         */
        refresh_state: function(all){
            this.callChildren('setState', [this._state, {object: true, selected: true, view_name: true, select: true}], undefined, all);
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
            var obj = _.isArray(state.object)? _.first(state.object) : state.object;
            if (!obj || !/^\//.test(obj)) obj = '/' + obj;
            var uri = this.options.basepath + obj;
            if (state.view_name){
                uri = uri + '&view_name=' + state.view_name;
            }
            if (state.select !== 'structure'){
                uri = uri + '&select=' + state.select;
            }
            return uri;
        },

        call_updateURI: function(e, args){
            $.boolive.Widget.prototype.call_updateURI.apply(this, [e, args]);
            var reg = new RegExp('^'+args.uri+'(\/|$)');
            if (this._state.object === args.uri){
                this._state.object = args.new_uri;
                this._state.selected = [args.new_uri];
                history.replaceState(this._state, '', this.getURIFromState(this._state));
            }
        }
	})
})(jQuery, window, _);
