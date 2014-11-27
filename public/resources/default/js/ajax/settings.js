(function(factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else {
        factory(jQuery);
    }
}(function($) {
    $.ajaxSetup({
        url: '/rest',
        dataType: 'json',
        dataFilter: function(data, dataType) {
            switch (dataType) {
                case 'json':
                    data = data.replace('while(1);', '');
                    break;
            }
            return data;
        },
        error: function(e, t, s, c) {
            if (e.status === 403 || e.status === 503) {
                window.location = window.location;
            } else {
                var m = $.parseJSON(e.responseText);
                $.popup.alert({
                    title: 'Sorry',
                    message: m.message || m.response || m || 'There was an ajax problem.'
                });
            }
        },
        complete: function(jqXHR) {
            var response;
            try {
                response = jqXHR.responseText ? $.parseJSON(jqXHR.responseText) : {};
            } catch (e) {
                response = {};
            }
            if (response.ajaxCallback) {
                var func = Function(response.ajaxCallback);
                func(response);
            }
            if (response.model) {
                var model = response.model;
                var modelElement = '.' + model.name + '-' + model.data.id + '-';
                for (var i in model.data) {
                    if (model.data.hasOwnProperty(i)) {
                        $(modelElement + i).text(model.data[i]);
                    }
                }
            }
        }
    });
    $(document).ajaxStart(function() {
        if (!$('#ajax-loading').length) {
            $('body').append($('<div id="ajax-loading" style="position:absolute"></div>')
                .html(
                    $('<div class="alert alert-info"></div>').html($('<h4>Loading...</h4>'))
                        .append($('<div class="progress progress-striped active"></div>')
                            .html($('<div class="bar"></div>'))
                        )
                )
            );
        }
        $('#ajax-loading').center('fixed').fadeIn('fast');
    });
    $(document).ajaxStop(function() {
        var loading = $('#ajax-loading');
        if (loading.length) {
            if (loading.is(':visible')) {
                loading.fadeOut('fast');
            }
        }
    });
}));