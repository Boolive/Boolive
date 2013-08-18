/**
 * User: pauline
 * Date: 17.04.13
 * Виджет для сохранения изменений в подчиненных виджетах страницы
 */
(function($) {
    $.widget("boolive.PageEditor", $.boolive.Widget, {
        // uri измененных подчиенных
         _changes: {},
         _changes_cnt: 0,
         _buttons: {},

        _create: function() {
            $.boolive.Widget.prototype._create.call(this);
            var self = this;
            self._buttons['save'] = self.element.find('a.save');
            self._buttons['cancel'] = self.element.find('a.cancel');
            self._buttons['save'].on('click', function(e){
                e.preventDefault();
                if (!$(this).hasClass('btn-disable') && self._changes_cnt){
                  $(this).addClass('btn-disable');
                  self.callChildren('save');
                }
            });
            self._buttons['cancel'].on('click', function(e){
                e.preventDefault();
                self.callChildren('cancel');
                self._buttons['save'].addClass('btn-disable');
            });

        },
        /**
         * Выделение объекта
         */
        call_setState: function(caller, state, changes){

            if (caller.direct == 'children'){
                if ($.isPlainObject(changes) && ('selected' in changes)){
                    this.element.find('.main .selected').removeClass('selected');
                    if (state.selected){
                        var element = this.element;
                        _.each(state.selected, function(s){
                            element.find('.main [data-o="'+s+'"]').addClass('selected');
                        });
                    }
                }
            }
        },
        call_change: function(caller, object){ //before
            this._buttons['save'].removeClass('btn-disable');
            this._changes[object] = true;
            this._changes_cnt++;
        },
        call_nochange: function(caller, object){ //before
            delete this._changes[object];
            this._changes_cnt--;
            if (!this._changes_cnt){
                this._buttons['save'].addClass('btn-disable');
            }
        }
    });
  })(jQuery);
