(function(factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else {
        factory(jQuery);
    }
}(function($) {
    $.fn.center = function(position) {
        position = position || 'absolute';
        return this.each(function() {
            var browser = $(window);
            var element = $(this);
            var top = (((browser.height() - element.height()) / 2));
            var left = (((browser.width() - element.width()) / 2));
            if (position !== 'fixed') {
                top += browser.scrollTop();
                left += browser.scrollLeft();
            }
            top = top < 0 ? 0 : top;
            left = left < 0 ? 0 : left;
            element.css({position: position, left: left + 'px', top: top + 'px'});
        });
    };
}));