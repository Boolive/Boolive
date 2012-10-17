/**
 * Виджет добавления объекта
 * Выбор из часто используемых или среди всех сущесвтующих
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($) {
	$.widget("boolive.Add", $.boolive.AjaxWidget, {
        // Объект, в который добавлять (родитель)
        object: null,
        // Выделенный объект (прототип для новового)
        object_select: null,

        _create: function() {
			$.boolive.AjaxWidget.prototype._create.call(this);
            var self = this;
            // uri объекта
            self.object = this.element.attr('data-object');
            // Отмена выделения при клике на свободную центральную область
            self.element.click(function(e){
                e.stopPropagation();
                self._select(null);
            });
            // Отмена
            self.element.find('.cancel').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                history.back();
            });
            // Добавление
            self.element.find('.submit').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                self._add();
            });
            // Выбор и добавление другого объекта
            self.element.find('.other').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                self.before('openWindow', [null,
                    {
                        url: "/",
                        data: {
                            direct: self.options.view_uri+'/SelectObject', // uri выиджета выбора объекта
                            object: '' //какой объект показать
                        }
                    },
                    function(result, params){
                        if (result == 'submit' && 'select' in params){
                            self.object_select = params.select;
                            self._add();
                        }
                    }
                ]);
            });
        },

        /**
         * При выделении в списке часто используемых
         * @param state
         * @return {Boolean}
         */
        before_setState: function(state){
            if (state.object || state.select){
                this._select(state.object || state.select);
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
                    this.element.find('[data-object="'+self.escape(object)+'"]').parent().addClass('selected');
                    this.element.find('.submit').removeClass('btn-disable');
                }else{
                    this.element.find('.submit').addClass('btn-disable');
                }
                this.object_select = object;
            }
        }
	})
})(jQuery);