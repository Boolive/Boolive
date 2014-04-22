var BooliveModel =(function($, window, document, undefined) {

    var Model = function(){
        this.attrib = {}; // текущие значения атрибутов
        this.attrib_start = {}; // начальные значения
        this.error = {}; // Оишибки
        this.events = {}; // назначенные обработчики событий
    };
    /**
     * Установка всех атрибутов
     * @param attribs
     */
    Model.prototype.init = function(attribs){
        this.attrib = _.clone(attribs);
        this.attrib_start = attribs;
        for (var name in this.attrib){
            this.trigger('change-attrib:'+name, this.attrib[name]);
        }
        this.trigger('change-attrib', false);
    };
    /**
     * Установка атрибута по имени
     * @param name
     * @param value
     */
    Model.prototype.set_attrib = function(name, value){
        if (this.attrib[name] != value){
            this.attrib[name] = value;
            this.trigger('change-attrib:' + name, value);
            this.trigger(
                'change-attrib',
                this.attrib[name] != this.attrib_start[name] ? true : this.is_change()
            );
        }
    };

    Model.prototype.set_error = function(name, value){
        if (this.error[name] != value){
            this.error[name] = value;
            this.trigger('change-error:' + name, value);
            this.trigger('change-error', {name: name, value: value});
        }
    };

    Model.prototype.clear_errors = function(){
        this.error = {};
        this.trigger('clear-error', null);
    };
    /**
     * Вызов события
     * @param event
     * @param arg
     */
    Model.prototype.trigger = function(event, arg){
        if (this.events[event]){
            this.events[event].fire(arg);
        }
    };
    /**
     * Регистрация на событие
     * @param event
     * @param callback
     * @return {*}
     */
    Model.prototype.on = function(event, callback){
        if (!this.events[event]){
            this.events[event] = $.Callbacks();
        }
        this.events[event].add(callback);
        return this;
    };

    Model.prototype.is_change = function(){
        var name;
        for (name in this.attrib_start){
            if (this.attrib[name] != this.attrib_start[name]) return true;
        }
        return false;
    };

    Model.prototype.is_change_attrib = function(name){
        return (this.attrib[name]!=this.attrib_start[name]);
    };

    return Model;
}(jQuery, window, document));