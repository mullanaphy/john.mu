(function(factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else {
        factory(jQuery);
    }
}(function($) {
    $.ajaxDispatcher = function(event, func) {
        var dispatcher = 'ajax:dispatcher:' + event;
        if (func) {
            $(document).delegate(document, dispatcher, func);
        }
        return dispatcher;
    };
}));