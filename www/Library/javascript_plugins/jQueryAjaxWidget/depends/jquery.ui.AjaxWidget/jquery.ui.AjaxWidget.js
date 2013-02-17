/**
 * Базовый виджет для админки Boolive.
 * С функциями обновления по ajax и взаимодействия с подчиненными и родительсктими виджетами
 * Query UI widget
 * @copyright Copyright 2012 (C) Boolive
 * @used jQuery (http://jquery.com)
 * @used jQueryUI (http://jqueryui.com/)
 * @used Underscore.js (http://underscorejs.org)
 */
(function($, _, undefined){
    /**
     * Виджет на основе JQuery UI Widget
     */
    $.widget("boolive.AjaxWidget", {

        options: {
            view: undefined, // Идентификатор вида (виджета).
            object: undefined // Идентификатор отображаемого объекта
        },
        /**
         * @var AjaxWidget | undefined Родительский виджет
         */
        _parent: undefined, // Родитель
        /**
         * @var Array | undefined Массив подчиенных виджетов
         */
        _children: undefined, // Подчиненные виджеты

        /**
         * Конструктор объекта
         * @private
         */
        _create: function(){
            // ссылка на себя
            var self = this;

            this._children = {};
            // Идентификатор вида.
            if (!this.options.view){
                this.options.view = this.element.attr('data-v');
            }
            // Идентификатор отображаемого объекта (обычно его URI)
            if (!this.options.object){
                this.options.object = this.element.attr('data-o');
            }
            // Добавление нового подчиненного в свой список
            this.element.on('_create'+this.eventNamespace, function(e, widget){
                return !self._addChild(widget);
            });
            // Удаление подчиенного из списка
            this.element.on('_destroy'+this.eventNamespace, function(e, widget){
                return !self._deleteChild(widget);
            });
            // Сообщаем родителю о своём создании
            this.element.trigger('_create', [this]);
        },

        /**
         * Деструктор объекта
         * @private
         */
        _destroy: function(){
            // Сообщаем родителю о своём удалении
            this.element.trigger('_destroy', [this]);
            this._parent = undefined;
        },

        /**
         * Добавление подчиненного виджета
         * @param widget Объект виджета
         * @return {Boolean}
         * @private
         */
        _addChild: function(widget){
            if (widget != this){
                this._children[widget.uuid] = widget;
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
                return true;
            }
            return false;
        },

        /**
         * Вызов дейсвтия у подчиненных
         * @param call Название действия (функции)
         * @param args Массив аргументов
         * @param target Объект, иницировавший вызов действия. По умолчанию this
         */
        callChildren: function(call, args, target){
            var stop = undefined;
            if (target && target!=this){
                if ($.isFunction(this['call_'+call])){
                    if (!$.isArray(args)) args = [args];
                    var a = [{target: target, direct: 'children'}].concat(args);
                    stop = this['call_'+call].apply(this, a);
                }
            }
            if (stop !== undefined){
                return stop;
            }
            var result = [];
            for (var child in this._children){
                stop = this._children[child].callChildren(call, args, target || this);
                if (stop !== undefined){
                    result.push(stop);
                }
            }
            return result.length? result : undefined;
        },

        /**
         * Вызов дейсвтия у родителей
         * @param call Название действия (функции)
         * @param args Массив аргументов
         * @param target Объект, иницировавший вызов действия. По умолчанию this.
         */
        callParents: function(call, args, target){
            if (!target) target = null;
            var stop = undefined;
            if (target && target!=this){
                if ($.isFunction(this['call_'+call])){
                    if (!$.isArray(args)) args = [args];
                    var a = [{target: target, direct: 'parents'}].concat(args);
                    stop = this['call_'+call].apply(this, a);
                }
            }
            if (stop !== undefined){
                return stop;
            }else
            if (this._parent){
                return this._parent.callParents(call, args, target || this);
            }
            return undefined;
        },

        /**
         * Вызов метода виджета на сервере по AJAX
         * @param call Название действия (функции) для вызова на сервере.
         * @param data POST данные
         * @param settings Дополнительные парметры ajax запроса и функции обратного вызова как в $.ajax()
         */
        callServer: function(call, data, settings){
            if (_.isFunction(settings)){
                settings = {success:settings};
            }else
            if (!_.isObject(settings)){
                settings = {};
            }
            var success = _.isFunction(settings.success)? settings.success : null;
            settings.owner = this.widgetName;
            settings.context = this.element;
            settings.type = 'POST';
            settings.dataType = 'json';
            settings.data = settings.data? _.extend(settings.data, data) : data;
            settings.data.direct = this.options.view;
            settings.data.call = call;
            $.ajax(settings);
        },

        /**
         * Обновление html виджета с сервера
         * @param data POST данные запроса
         * @param settings Дополнительные парметры ajax запроса и функции обратного вызова как в $.ajax()
         */
        reload: function(data, settings){
            var self = this;
            if (_.isFunction(settings)){
                settings = {success:settings};
            }else
            if (!_.isObject(settings)){
                settings = {};
            }
            var success = _.isFunction(settings.success)? settings.success : null;
            settings.owner = self.widgetName;
            settings.context = self.element;
            settings.type = 'POST';
            settings.data = settings.data? _.extend(settings.data, data) : data;
            settings.data.direct = self.options.view;
            settings.dataType = 'json';
            settings.success = function(result, textStatus, jqXHR){
                // Подключение файлов css и js. После их подключения обновляется HTML виджета
                if (!result.links) result.links = [];
                $.include(result.links, function(){
                    // Название корневого тега
                    var expr = new RegExp('<[a-z]+.*data-v=[\'"]'+self.options.view+'[\'"].*>');
                    var tag = expr.exec(result.out);
                    if (tag && tag.length==1){
                        tag = tag[0];
                        // Оставляем только вложенные теги
                        expr = new RegExp(tag+'([\\s\\S]*)<\/div>[\\s\\S]*');
                        var html = result.out.replace(expr, '$1');
                        // Обратный вызов перед очисткой элемента
                        if ('empty' in settings && _.isFunction(settings.empty)){
                            settings.empty();
                        }
                        self.element.html(html);
                        // Вызов события изменения HTML. Будут подключаться jquery плагины к загруженному html
                        $(document).trigger('load-html', [self.element]);
                        // Обратный вызов при удачном обновлении виджета
                        if (success) success(result, textStatus, jqXHR);
                    }
                });
            };
            $.ajax(settings);
        },

        /**
         * Загрузка виджета по его URI
         * @param container jQuery объект, куда вставить html полученного объекта
         * @param append Признак, добавлять в конец (true) или в начало (false) контейнера
         * @param uri URI загружаемого с сервера виджета. Может быть сокращенным.
         * @param data POST данные запроса
         * @param settings Дополнительные парметры ajax запроса и функции обратного вызова
         */
        load: function(container, append, uri, data, settings){
            var self = this;
            if (_.isFunction(settings)){
                settings = {success:settings};
            }else
            if (!_.isObject(settings)){
                settings = {};
            }
            var success = _.isFunction(settings.success)? settings.success : null;
            settings.owner = this.widgetName;
            settings.context = this.element;
            settings.type = 'POST';
            settings.data = settings.data? _.extend(settings.data, data) : data;
            settings.data.direct = uri;
            settings.dataType = 'json';
            settings.success = function(result, textStatus, jqXHR){
                if (!result.links) result.links = [];
                $.include(result.links, function(){
                    if (typeof append == 'boolean' && append){
                        container.append(result.out);
                    }else{
                        container.prepend(result.out);
                    }
                    $(document).trigger('load-html', [container]);
                    // Обратный вызов при удачном обновлении виджета
                    if (success) success(result, textStatus, jqXHR);
                });
            };
            $.ajax(settings);
        },

        /**
         * Экранирование спецсимволов строки обратным слэшем для использования в jQuery селекторах
         * @param str
         * @return {*}
         */
        escape_selector: function (str){
            if( str)
                return str.replace(/([ #;&,.+*~\':"!^$[\]()=>|\/@])/g,'\\$1');
            else
                return str;
        }
    });
})(jQuery, _);