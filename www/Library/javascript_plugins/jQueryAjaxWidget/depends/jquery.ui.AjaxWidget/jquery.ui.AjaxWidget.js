/**
 * jQueryUI widget with reloading function
 */
(function($){

    $.widget("boolive.AjaxWidget", {

        options: {
			default_url: '',
            view_uri: '',
			wait_window: null // Окно ожидания. jQuery объект
        },

		_is_wait: false, // признак, находится ли виджет в режиме загрузки окна

        _create: function() {
			this.element.attr('widget',true);
            if (!this.options.view_uri){
                this.options.view_uri = this.element.attr('data-view_uri');
            }
            if (!this.options.default_url){
                this.options.default_url = location.pathname;
            }
            var self = this;
            this.element.ajaxError(function(e, jqxhr, settings, exception) {
                console.log(settings);
            });
        },

        ajaxError: function(event, request, settings){
            alert('AJAX '+request.statusText);
        },

        /**
         * Перегрзука html виджета
         */
        reload: function(url, data, callbacks){
			var self = this;
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

        destroy: function() {
			$.Widget.prototype.destroy.call( this );
		},

		escape: function (str){
			if( str)
				return str.replace(/([ #;&,.+*~\':"!^$[\]()=>|\/@])/g,'\\$1');
			else
				return str;
		}
    })
})(jQuery);