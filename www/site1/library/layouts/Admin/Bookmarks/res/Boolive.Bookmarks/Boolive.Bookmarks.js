/**
 * Заготовка jQueryUI виджета с наследованием boolive.Widget
 * Переименуйте WidgetName на своё имя виджета.
 *
 * Query UI widget
 * Copyright 2012 (C) Boolive
 */
(function($, _, undefined) {
    $.widget("boolive.Bookmarks", $.boolive.Widget, {

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            this.call_setState({target: this, direct: 'children'}, this.callParents('getState'), {object: true})
            // Enter
			self.element.on('click', 'a', function(e){
                e.preventDefault();
                e.stopPropagation();
                // Вход в объект
                self.callParents('setState', [{
                    object: $(this).closest('.Bookmarks__item').attr('data-l')
                }]);
			});

            // Add
            self.element.on('click', '.Bookmarks__add', function(e){
                e.preventDefault();
                var proto = self.callParents('getState').object;
                if (self.element.find('.Bookmarks__item[data-l="'+self.escape_regex(proto)+'"]').size() == 0){
                    // Поиск закладок, которые нужно переместить внутрь новой
                    var sub_items = [];
                     var sel;
                    if (proto === ""){
                        sel = '.Bookmarks__item';
                    }else{
                        sel = '.Bookmarks__item[data-l^="'+self.escape_regex(proto)+'"]';
                    }
                    self.element.find(sel+':first').parent().children(sel).each(function(){
                        sub_items.push($(this).attr('data-o'));
                    });

                    // Поиск родительской закладки
                    var items = self.element.find('.Bookmarks__item');
                    var parent = [null,0];
                    items.each(function(){
                        var reg = new RegExp('^'+self.escape_regex($(this).attr('data-l')));
                        var cnt;
                        if (reg.test(proto)){
                            cnt = $(this).attr('data-l').split('/').length;
                            if (parent[1] < cnt){
                                parent[0] = $(this).attr('data-o');
                                parent[1] = cnt;
                            }
                        }
                    });
                    self._add((parent[0] === null ? self.options.object : parent[0]), proto, function(result, textStatus, jqXHR){
                        if (sub_items.length){
                            var pull = [];
                            $.each(sub_items, function(i, item){
                                pull.push(self._move(item, result.result.id));
                            });
                            $.when.apply($, pull).then(function(){
                                self.reload({object: proto});
                            });
                        }else{
                            self.reload({object: proto});
                        }
                    });
                }
            });

            // Remove
            self.element.on('click', '.Bookmarks__item-remove', function(e){
                e.preventDefault();
                e.stopPropagation();
                var pull = [];
                var item = $(this).closest('.Bookmarks__item');
                // Пермещение вложенных вкдаок
                var sub_items = item.children('.Bookmarks__items-list:first').children('.Bookmarks__item');

                if (sub_items.size() > 0){
                    var parent = item.parent().closest('.Bookmarks__item');
                    if (parent.size() == 1){
                        parent = parent.attr('data-o');
                    }else{
                        parent = self.options.object;
                    }
                    sub_items.each(function(i, item){
                        pull.push(self._move($(item).attr('data-o'), parent));
                    });
                }
                $.when.apply($, pull).then(function(){
                    self._delete(item.attr('data-o'), function(){
                        self.reload({object: self.callParents('getState').object});
                    });
                });
            });
        },

        _add: function(parent, proto, success, error){
            var url = /^[a-z]+:\/\//i.test(this.options.object) ? this.options.object : window.location.protocol + '//' + window.location.host + (typeof parent === 'undefined' ? this.options.object : parent);
            return $.ajax({
                type: 'POST',
                dataType: 'json',
                accepts: {json: 'application/json'},
                url: url,
                data: {
                    method: 'POST',
                    entity: {
                        proto: proto,
                        is_link: true,
                        is_draft: false
                    }
                },
                success: success,
                error: error
            });
        },

        _delete: function(object, success, error){
            var url = /^[a-z]+:\/\//i.test(this.options.object) ? this.options.object : window.location.protocol + '//' + window.location.host + object;
            return $.ajax({
                type: 'POST',
                dataType: 'json',
                accepts: {json: 'application/json'},
                url: url,
                data: {
                    method: 'DELETE'
                },
                success: success,
                error: function(jqXHR, textStatus){
                    if (jqXHR.status == 204){
                        if (_.isFunction(success)) success('', textStatus, jqXHR)
                    }else{
                        if (_.isFunction(error)) error(jqXHR, textStatus)
                    }
                }
            });
        },

        _move: function(object, new_parent, success, error){
            var url = /^[a-z]+:\/\//i.test(this.options.object) ? this.options.object : window.location.protocol + '//' + window.location.host + object;
            return $.ajax({
                type: 'POST',
                dataType: 'json',
                accepts: {json: 'application/json'},
                url: url,
                data: {
                    method: 'PUT',
                    entity: {
                        parent: (typeof new_parent === 'undefined' ? this.options.object : new_parent)
                    }
                },
                success: success,
                error: error
            });
        },

        /**
         * При любом изменении состояния (вход, выделение объекта, выбор программы)
         * загрузка пунктов меню программ и выделение текущего пункта
         * @param caller
         * @param state
         * @param change Какие изменения в state
         */
        call_setState: function(caller, state, change){ //after
            if (caller.direct == 'children'){
                var self = this;
                if ('object' in change){
                    self.element.find('.active').removeClass('active');
                    self.element.find('.subactive').removeClass('subactive');
                    self.element.find('[data-l="'+state.object+'"]').addClass('active');
                    var items = self.element.find('[data-l]');
                    var cnt = items.size();
                    var item;
                    while (--cnt >= 0){
                        item = $(items[cnt]);
                        if (state.object.indexOf(item.attr('data-l')) == 0){
                            if (!item.hasClass('active')) item.addClass('subactive');
                            cnt = 0;
                        }
                    }
                }
            }
        }
    })
})(jQuery, _);