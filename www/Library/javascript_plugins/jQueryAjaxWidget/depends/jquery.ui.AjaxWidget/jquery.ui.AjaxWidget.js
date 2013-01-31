/**
 * Базовый виджет для админки Boolive.
 * С функциями обновления по Ajax и связывния со своими подчиненными и родителем для передачи им команд
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, undefined){
    /**
     * Виджет на основе JQuery UI Widget
     */
    $.widget("boolive.AjaxWidget", {

        options: {
			default_url: '',
            view_uri: '',
			wait_window: null // Окно ожидания. jQuery объект
        },
        _parent: null, // Родитель
        _children: null, // Подчиненные виджеты
        _children_length: 0, // Кол-во подчиенных

		_is_wait: false, // признак, находится ли виджет в режиме загрузки окна

        /**
         * Конструктор объекта
         * @private
         */
        _create: function() {
            // ссылка на себя
            var self = this;

            this._children = {};
            // URI виджета
            if (!this.options.view_uri){
                this.options.view_uri = this.element.attr('data-v');
            }
            // URL для ajax запросов
            if (!this.options.default_url){
                this.options.default_url = location.pathname;
            }
            // Обработка Ajax ошибок
            this.element.ajaxError(function(e, jqxhr, settings, exception) {
                alert('AJAX error: '+ jqxhr.responseText);
            });
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
        _destroy: function() {
            // Сообщаем родителю о своём удалении
            this.element.trigger('_destroy', [this]);
            this._parent = null;
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
                this._children_length++;
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
                this._children_length--;
                return true;
            }
            return false;
        },

        /**
         * Вызов дейсвтия у подчиненных
         * @param action Название действия (функции)
         * @param args Аргументы
         * @param target Объект, иницировавший вызов действия. По умолчанию this
         */
        callChildren: function(action, args, target){
            var stop = undefined;
            if (target && target!=this){
                if ($.isFunction(this['call_'+action])){
                    if (!$.isArray(args)) args = [args];
                    var a = [{target: target, direct: 'children'}].concat(args);
                    stop = this['call_'+action].apply(this, a);
                }
            }
            if (stop !== undefined){
                return stop;
            }
            var result = [];
            for (var child in this._children){
                stop = this._children[child].callChildren(action, args, target || this);
                if (stop !== undefined){
                    result.push(stop);
                }
            }
            return result.length? result : undefined;
        },

        /**
         * Вызов дейсвтия у родителей
         * @param action Название действия (функции)
         * @param args Аргументы
         * @param target Объект, иницировавший вызов действия. По умолчанию this.
         */
        callParents: function(action, args, target){
            if (!target) target = null;
            var stop = undefined;
            if (target && target!=this){
                if ($.isFunction(this['call_'+action])){
                    if (!$.isArray(args)) args = [args];
                    var a = [{target: target, direct: 'parents'}].concat(args);
                    stop = this['call_'+action].apply(this, a);
                }
            }
            if (stop !== undefined){
                return stop;
            }else
            if (this._parent){
                return this._parent.callParents(action, args, target || this);
            }
            return undefined;
        },

        /**
         * Перегрзука html виджета
         */
        reload: function(url, data, callbacks){

            var self = this;
            //self.element.empty();
            data = $.extend({}, data);
            data.direct = self.options.view_uri;
            $.ajax({
                owner: "boolive.AjaxWidget",
                type: 'POST',
                url: (typeof url == 'string')?url:self.options.default_url,
                data: data,
                dataType: 'json',
                success: function(result, textStatus, jqXHR){
                    // Css, js
					if (!result.links) result.links = [];
                    $.include(result.links, function(){
                        // Html
						// Название корневого тега
						var expr = new RegExp('<[a-z]+.*data-v=[\'"]'+self.options.view_uri+'[\'"].*>');

                        var tag = expr.exec(result.out);
						if (tag && tag.length==1){
							tag = tag[0];
							// оставляем только вложенные теги
							expr = new RegExp(tag+'([\\s\\S]*)<\/div>[\\s\\S]*');
							var html = result.out.replace(expr, '$1');
							//var x = $(container).html(html);

							if (callbacks && typeof callbacks == 'object' && typeof callbacks.empty == 'function'){
								callbacks.empty();
							}

                            self.element.html(html);//x.children().children());

							// Атрибуты корневого тега виджета
//							if (container.children.length > 0){
//								var attribs = container.children[0].attributes;
//								for(var i=0; i<attribs.length; i++){
//									self.element.attr(attribs[i].name, attribs[i].value);
//								}
//							}
                            $(document).trigger('load-html', [self.element]);
							if (typeof callbacks == 'function'){
								callbacks(result, textStatus, jqXHR);
							}else
							if (callbacks && typeof callbacks == 'object' && typeof callbacks.success == 'function'){
								callbacks.success(result, textStatus, jqXHR);
							}
						}
					});
                }
            });
        },

        /**
         *
         * @param method
         * @param data
         * @param callbacks
         * @private
         */
        _call: function(method, data, callbacks){
            var self = this;
            data = $.extend({}, data);
            data.direct = self.options.view_uri;
            data.call = method;
            $.ajax({
                owner: "boolive.AjaxWidget",
                type: 'POST',
                url: self.options.default_url,
                data: data,
                dataType: 'json',
                context: self.element,
                success: function(result, textStatus, jqXHR){
                    if (typeof callbacks == 'function'){
						callbacks(result, textStatus, jqXHR);
					}else
					if (callbacks && typeof callbacks == 'object' && typeof callbacks.success == 'function'){
						callbacks.success(result, textStatus, jqXHR);
					}
                }
                //beforeSend(jqXHR, settings)
                //complete(jqXHR, textStatus)
                //error(jqXHR, textStatus, errorThrown)
            });
        },

		/**
		 * Режим ожидания загрузки
		 * @param state
		 */
		_wait: function(state){
			if (this.options.wait_window){
				if (state){
					this._is_wait = true;
					this.options.wait_window.show();
				}else{
					this._is_wait = false;
					this.options.wait_window.hide();
				}
			}
		},

		isWait: function(){
			return this._is_wait;
		},

		/**
		 * Загрузка подчиенного виджета
		 * @param container jQuery объект, куда вставить html полученного объекта
		 * @param url Адрес запроса
		 * @param args Данные запроса
		 * @param sub_name Имя подчиненного виджета. Может быть путем на подчиненных любого уровня
		 * @param callbacks
		 */
		loadsub: function(container, url, data, sub_name, append, callbacks){
			var self = this;
            data = $.extend({}, data);
            data.direct = self.options.view_uri+'/'+sub_name;
			$.ajax({
				type: 'POST',
                url: (typeof url == 'string')?url:self.options.default_url,
                data: data,
                dataType: 'json',
				success: function(result, textStatus, jqXHR){

                    if (!result.links) result.links = [];
					$.include(result.links, function(){
						if (typeof append == 'boolean' && append){
                            container.append(result.out);
                        }else{
                            container.empty();
						    container.html(result.out);
                        }
                        $(document).trigger('load-html', [container]);

						if (typeof callbacks == 'function'){
							callbacks(result, textStatus, jqXHR);
						}else
						if (callbacks && typeof callbacks == 'object' && typeof callbacks.success == 'function'){
							callbacks.success(result, textStatus, jqXHR);
						}
					});
				}
			});
        },



		escape: function (str){
			if( str)
				return str.replace(/([ #;&,.+*~\':"!^$[\]()=>|\/@])/g,'\\$1');
			else
				return str;
		}
    });
})(jQuery);