(function(factory) {
  if (typeof define === 'function' && define.amd) {
    define(['jquery'], factory);
  } else {
    factory(jQuery);
  }
}(function($) {
  $.popup = function(options) {
    var modal = $('#modal');
    $('.modal-content', modal).html(options.content);
    if (options.classname) {
      modal.addClass(options.classname);
      modal.on('hidden', function() {
        modal.removeClass(options.classname);
        modal.off('hidden');
      });
    }
    modal.modal();
  };
  $.popup.alert = function(options) {
    if (typeof options !== 'object') {
      options = {
        content: options
      };
    }

    var title = $('<div class="modal-header"></div>')
      .html(
      $('<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>')
        .text('×')
    )
      .append(
      $('<h4 class="modal-title" id="modal-label"></h4>')
        .html(options.title || 'Alert'));
    var content = $('<div class="modal-body"></div>')
      .html(options.message || 'Odd, no content provided...');
    var footer = $('<div class="modal-footer"></div>');

    if (options.cancel) {
      var cancel = $('<a href="' + options.cancel.url + '" class="btn"></a>')
      cancel.text(options.cancel.label);
      if (options.cancel.callback) {
        cancel.on('click', options.cancel.callback);
      }
      footer.append(cancel);
    }
    if (typeof options.ok === 'object') {
      var ok = $('<a href="' + options.ok.url + '" class="btn ' + (typeof options.ok.classname !== 'undefined' ? options.ok.classname : 'btn-primary') + '"></a>');
      ok.text(options.ok.label);
      if (options.ok.callback) {
        ok.on('click', options.ok.callback);
      }
      footer.append(ok);
    } else {
      footer.append(
        $('<a href="#" class="btn ' + (typeof options.okclassname !== 'undefined' ? options.okclassname : 'btn-primary') + '" data-dismiss="modal"></a>')
          .text(options.ok || 'Ok')
      );
    }
    var html = title.add(content).add(footer);
    $.popup({content: html});
  };
  $.popup.confirm = function(options) {
    if (typeof options !== 'object') {
      options = {
        content: options
      };
    }

    var title = $('<div class="modal-header"></div>')
      .html(
      $('<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>')
        .text('×')
    )
      .append(
      $('<h4 class="modal-title" id="modal-label"></h4>')
        .html(options.title || 'Confirm'));
    var content = $('<div class="modal-body"></div>')
      .html(options.message || 'Odd, no content provided...');
    var footer = $('<div class="modal-footer"></div>');
    footer.append(
      $('<a href="#" class="btn ' + (typeof options.okclassname !== 'undefined' ? options.okclassname : 'btn-primary') + '"></a>')
        .text(options.ok || 'Yes')
        .click(options.callback || function() {
          $('modal').modal('hide');
          return false;
        }));
    footer.append(
      $('<button type="button" class="btn btn-default ' + (typeof options.cancelclassname !== 'undefined' ? options.cancelclassname : 'btn-danger') + '" data-dismiss="modal"></a>')
        .text(options.cancel || 'No')
    );
    var html = title.add(content).add(footer);
    $.popup({content: html});
  };
  $.popup.flash = function(response, type, jqXHR) {
    if (jqXHR.status === 204) {
      window.location = window.location.href.split('#')[0];
    } else {
      if (typeof response.url !== 'undefined') {
        window.location = response.url;
      } else {
        var caller = $('#modal');
        if (!$('#modal-flash').length) {
          $('.modal-body', caller).prepend($('<div id="modal-flash"></div>'));
        }
        $('#modal-flash')
          .removeClass()
          .addClass('alert')
          .addClass('alert-' + (response.type || 'success'))
          .html(response.message || response);
      }
    }
  };
  return $.popup;
}));