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
                this.options.view_uri = this.element.attr('data-view_uri');
            }
            // URL для ajax запросов
            if (!this.options.default_url){
                this.options.default_url = location.pathname;
            }
            // Обработка Ajax ошибок
            this.element.ajaxError(function(e, jqxhr, settings, exception) {
                alert('AJAX error: '+ jqxhr.statusText);
            });
            // Добавление нового подчиненного в свой список
            this.element.on('_create', function(e, widget){
                return !self._addChild(widget);
            });
            // Удаление подчиенного из списка
            this.element.on('_destroy', function(e, widget){
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
         * Вызов дейсвтия у родителя
         * @param action Название действия
         * @param args Аргументы
         * @param target Объект, иницировавший вызов действия
         */
        before: function(action, args, target){
            if (!target) target = null;
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
            return undefined;
        },

        /**
         * Вызов действия завершения у всех подчиненных
         * @param action Название действия
         * @param args Аргументы
         * @param target Объект, иницировавший вызов действия
         */
        after: function(action, args, target){
            var stop = false;
            if (target && target!=this){
                if ($.isFunction(this['after_'+action])){
                    stop = this['after_'+action].apply(this, args);
                }
            }
            if (!stop){
                for (var child in this._children){
                    this._children[child].after(action, args, target || this);
                }
            }
        },

        /**
         * Перегрзука html виджета
         */
        reload: function(url, data, callbacks){
			var self = this;
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
						var expr = new RegExp('<[a-z]+.*data-view_uri=[\'"]'+self.options.view_uri+'[\'"].*>');

                        var tag = expr.exec(result.out);
						if (tag && tag.length==1){
							tag = tag[0];
							// оставляем только вложенные теги
							expr = new RegExp(tag+'([\\s\\S]*)<\/div>[\\s\\S]*');
							var html = result.out.replace(expr, '$1');
							//var x = $(container).html(html);
							self.element.empty();
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