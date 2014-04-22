/**
 * Скролинг пальцем и колесом мыши
 */
(function($) {
    $.fn.touchScroll = function(preventDefault){
        return this.each(function (){
            var self = $(this);
            var scrollStartPos = 0;
            self.on('touchstart', function(event) {
                // jQuery clones events, but only with a limited number of properties for perf reasons. Need the original event to get 'touches'
                var e = event.originalEvent;
                scrollStartPos = $(this).scrollTop() + e.touches[0].pageY;
                if (preventDefault===false) e.preventDefault();
            });
            self.on('touchmove', function(event) {
                var e = event.originalEvent;
                $(this).scrollTop(scrollStartPos - e.touches[0].pageY);
                e.preventDefault();
            });
//            if (self.css('overflow-y')=='hidden'){

                self.on('mousewheel', function(event, delta){
                   $(this).scrollTop($(this).scrollTop()-delta*50);
                });

//            }

        });
    }
})(jQuery);