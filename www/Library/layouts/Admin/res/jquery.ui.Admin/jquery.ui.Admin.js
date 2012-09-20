(function($) {
	$.widget("boolive.Admin", $.boolive.AjaxWidget, {
        options: {
            basepath: '/admin'
        },
        // Начальное состояние
        _state_start: {
            object:'', // отображаемый объект
            select:'', // выделенный объект
            view_name:null // способ отображения (программа)
        },
        // Текущее состояние
        _state: {
            object:'', // отображаемый объект
            select:'', // выделенный объект
            view_name:null // способ отображения (программа)
        },

        _create: function() {
			$.boolive.AjaxWidget.prototype._create.call(this);

            var self = this;
            // Инициализация состояния
            self._init_state();

            // Вход в объект
            self.element.on('before-entry-object', function(e, object){
				self.entry({object:object}, true, function(){
                    self.select(self._state.object, false);
                });
			});
            // Выделение объекта
            self.element.on('before-select-object', function(e, object){
				self.select(object, true);
			});
            // Отмена выделения при клике на свободную центральную область
            self.element.find('.center').click(function(e){
                self.select(self._state.object, true);
            });
            // Выбор способа отображения (программы)
            self.element.on('before-choice-view', function(e, view_name){
				//self.choice_view(view_name);
                self.entry({object:self._state.select, view_name: view_name}, true, function(){
                    self.select(self._state.object, false);
                });
			});
            // Назад/Вперед
            $(window).on("popstate", function(e){
                var state = history.state || self._state_start;
                self.entry(state, false, function(){
                    self.select(state.select, false);
                });
            });
        },

        /**
         * Инициализация состояния
         * @private
         */
        _init_state: function(){
            var l = history.location || document.location;
            var expr = new RegExp('^'+this.options.basepath+'/(.*)$');
            var uri_args = this.parse_str('object='+l.pathname.replace(expr, '$1'));

            if (typeof uri_args.object == 'string'){
                if (uri_args.object) uri_args.object = '/'+uri_args.object;
                this._state_start.object = uri_args.object;
                this._state_start.select = uri_args.object;
            }
            if (typeof uri_args.view == 'string'){
                this._state_start.view_name = uri_args.view;
            }
            this._state = $.extend({}, this._state_start);
            var self = this;
            // Загрузка подчиненных
            this.loadsub(this.element.find('.center'), '/', this._state, 'Programs', true, function(){
                self.select(self._state_start.object, false);
            });
            this.loadsub(this.element.find('.right'), '/', this._state, 'ProgramsMenu', true, function(){
                self.select(self._state_start.object, false);
            });
        },

        /**
         * Вход в объект
         * @param object URI объекта
         */
        entry: function(state, push_history, callback){
            var replace = (state.view_name == this._state.view_name && (state.object == this._state.object || state.object==null));
            if (state.object!=null){
                this._state.object = state.object;
            }
            this._state.view_name = state.view_name ? state.view_name : null;
            this._state.select = state.select ? state.select : state.object;

            var uri = this.options.basepath + this._state.object;
            if (!this._state.object) uri = uri+'/';
            if (this._state.view_name){
                uri = uri + '&view='+state.view_name;
            }
            if (push_history){
                if (replace){
                    history.replaceState(this._state, null, uri);
                }else{
                    history.pushState(this._state, null, uri);
                }
            }
            $(document).trigger('after-entry-object', [this._state, callback]);
        },

        /**
         * Выделение объекта
         * Если объект не указан, то выделенным становится отображаемый объект
         * @param object URI выделенного объекта
         */
        select: function(object, push_history){
            this._state.select = object;
            if (push_history) history.replaceState(this._state);
            $(document).trigger('after-select-object', [this._state]);
        },

		destroy: function() {
			$.boolive.AjaxWidget.prototype.destroy.call(this);
		},

        /**
         * Parses the string into variables
         * @param str
         * @return {Array}
         * @private
         */
        parse_str: function(str){
            var ch1 = '=';
            var ch2 = '&';
            var list = str.split(ch2);
            var result = [];
            for(var i = 0; i < list.length; i++){
                var tmp = list[i].split(ch1);
                result[decodeURI(tmp[0])] = decodeURI(tmp[1]).replace(/[+]/g, ' ');
            }
            return result;
        }
	})
})(jQuery);
