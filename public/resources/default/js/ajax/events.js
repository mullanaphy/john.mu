(function(factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else {
        factory(jQuery);
    }
}(function($) {
    var _callback = function() {
    };
    $.ajaxDispatcher('default', function(event) {
        event.caller.success = event.callback || _callback;
        $.ajax(event.caller);
    });
    $.ajaxDispatcher('confirm', function(event) {
        var data = $.extend({
                title: 'Warning',
                content: 'Are you sure you want to perform this action?',
                confirm: 'default'
            },
            event.caller.dataset
        );
        data.callback = function() {
            $('#modal').modal('hide');
            setTimeout(function() {
                event.type = $.ajaxDispatcher(event.caller.dataset.confirm || 'default');
                $('body').trigger(event);
            }, 500);
            return false;
        };
        $.popup.confirm(data);
    });
    $.ajaxDispatcher('popup', function(event) {
        event.type = $.ajaxDispatcher('default');
        event.callback = $.popup;
        $('body').trigger(event);
    });
    $.ajaxDispatcher('alert', function(event) {
        event.type = $.ajaxDispatcher('default');
        event.callback = $.popup.alert;
        $('body').trigger(event);
    });
    $.ajaxDispatcher('popupFlash', function(event) {
        event.type = $.ajaxDispatcher('default');
        event.callback = $.popup.flash;
        $('body').trigger(event);
    });
    $.ajaxDispatcher('remove', function(event) {
        event.type = $.ajaxDispatcher('default');
        event.callback = function() {
            $(event.caller.dataset.element).fadeOut(function() {
                $(this).remove();
            });
            return false;
        };
        $('body').trigger(event);
    });
    $.ajaxDispatcher('reset', function(event) {
        event.type = $.ajaxDispatcher('default');
        event.callback = function() {
            window.location = window.location;
            return false;
        };
        $('body').trigger(event);
    });
}));