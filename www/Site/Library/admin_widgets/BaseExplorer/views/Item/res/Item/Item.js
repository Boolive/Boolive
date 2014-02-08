/**
 * Виджет элемента списка в обозревателе
 * Query UI boolive.Widget
 */
(function($, _, undefined) {
    $.widget("boolive.Item", $.boolive.Widget, {

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            // Идентификатор объекта к которому будет переход (обычно его URI)
            if (!this.options.link){
                this.options.link = this.element.attr('data-l');
            }
            if (!this.options.newlink){
                this.options.newlink = this.element.attr('data-nl');
            }
            // Вход в объекта
            self.element.find('.Item__title').click(function(e){
                e.stopPropagation();
                e.preventDefault();
                // Сначала выделяем себя
                //self.callParents('setState', [{selected:  self.options.object}]);
                // Теперь входим
                self.callParents('setState', [{object:  self.options.link}]);
            });
            // Вход в объект-ссылку
            self.element.find('.Item__link').click(function(e){
                e.stopPropagation();
                e.preventDefault();
                // Сначала выделяем себя
                //self.callParents('setState', [{selected:  self.options.object}]);
                // Теперь входим
                self.callParents('setState', [{object:  self.options.object}]);
            });
            // Переход к свойствам
            self.element.find('.Item__prop').click(function(e){
                e.stopPropagation();
                e.preventDefault();
                // Теперь входим
                self.callParents('setState', [{object:  self.options.object, select: 'property'}]);
            });
            // Переход к значению
            self.element.find('.Item__value').click(function(e){
                e.stopPropagation();
                e.preventDefault();
                // Теперь входим
                self.callParents('setState', [{object:  self.options.object, select: 'file'}]);
            });
            // Множественное выделение объекта
            self.element.find('.Item__select').click(function(e){
                e.stopPropagation();
                e.preventDefault();
                self.callParents('setState', [{selected: self.options.object, select_type: 'toggle'}]);
            });
            // Выделение объекта
            self.element.on('click', '.Item__select-area', function(e){
                e.stopPropagation();
                //e.preventDefault();
                self._select();
            });

//            self.element.on('focus', 'input', function(e){
//                self._select();
//            }).on('focus', 'textarea', function(e){
//                self._select();
//            });
        },

        _select: function(){
            var s = this.callParents('getState');
            if (_.size(s.selected)>1 || _.indexOf(s.selected, this.options.object)==-1){
                this.callParents('setState', [{selected: this.options.object}]);
            }
        },

        call_object_update: function(caller, info){
            if (!_.isUndefined(info[this.options.object])){
                var view = this.element;
                var changes = info[this.options.object];
                // Скрытие
                if (!_.isUndefined(changes['is_hidden'])){
                    if (changes['is_hidden']){
                        view.addClass('Item_hidden');
                    }else{
                        view.removeClass('Item_hidden');
                    }
                }
                // Черновик
                if (!_.isUndefined(changes['is_draft'])){
                    if (changes['is_draft']){
                        view.addClass('Item_draft');
                    }else{
                        view.removeClass('Item_draft');
                    }
                }
                // Свойство
                if (!_.isUndefined(changes['is_mandatory'])){
                    if (changes['is_mandatory']){
                        view.addClass('Item_mandatory');
                    }else{
                        view.removeClass('Item_mandatory');
                    }
                }
                // Ссылка
                if (!_.isUndefined(changes['is_link'])){
                    if (changes['is_link']){
                        this.options.link = this.options.newlink;
                        view.addClass('Item_link');
                    }else{
                        this.options.link = this.options.object;
                        view.removeClass('Item_link');
                    }
                    this.element.find('.Item__title').attr('href', this.options.link);
                }
            }
        }
    })
})(jQuery, _);