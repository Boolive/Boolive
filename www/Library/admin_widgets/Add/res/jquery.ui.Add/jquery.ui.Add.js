/**
 * Виджет добавления объекта
 * Выбор из часто используемых или среди всех сущесвтующих
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _) {
	$.widget("boolive.Add", $.boolive.AjaxWidget, {
        // Объект, в который добавлять (родитель)
        object: null,
        // Выделенный объект (прототип для новового)
        object_select: null,

        _create: function() {
			$.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;
            // uri объекта
            self.object = this.element.attr('data-o');
            // Отмена выделения при клике на свободную центральную область
            self.element.click(function(e){
                e.stopPropagation();
                self._select(null);
            });
            // Отмена
            self.element.find('.cancel').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                if (!$(this).hasClass('btn-disable')) history.back();
            });
            // Добавление
            self.element.find('.submit').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                if (!$(this).hasClass('btn-disable')) self._add();
            });
            // Выбор и добавление другого объекта
            self.element.find('.other').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                if (!$(this).hasClass('btn-disable')){
                    self.callParents('openWindow', [null,
                        {
                            url: "/",
                            data: {
                                direct: self.options.view_uri+'/SelectObject', // uri выиджета выбора объекта
                                object: '' //какой объект показать
                            }
                        },
                        function(result, params){
                            if (result == 'submit' && 'selected' in params){
                                self.object_select = params.selected;
                                self._add();
                            }
                        }
                    ]);
                }
            });
        },

        /**
         * При выделении в списке часто используемых
         * @param caller Информация, кто вызывал {target, direct}
         * @param state Объект текущего состояния.
         * @param changes Что изменилось в объекте состояния
         * @return {Boolean}
         */
        call_setState: function(caller, state, changes){  //before
            if (state.object || state.selected){
                this._select(state.object || state.selected);
            }
            return true;
        },

        /**
         * Добавление выбранного и возврат на предыдущую страницу
         * @private
         */
        _add: function(){
            var self = this;
            self._call('add', {
                object: self.object,
                proto: self.object_select
            }, function(result, textStatus, jqXHR){
                history.back();
            });
        },
        /**
         * Выделение объекта в списке часто используемых
         * @param object
         * @private
         */
        _select: function(object){
            if (object != this.object_select){
                this.element.find('.selected').removeClass('selected');
                if (object !=null){
                    this.element.find('[data-o="'+self.escape(object)+'"]').addClass('selected');
                    this.element.find('.submit').removeClass('btn-disable');
                }else{
                    this.element.find('.submit').addClass('btn-disable');
                }
                this.object_select = object;
            }
        }
	})
})(jQuery, _);