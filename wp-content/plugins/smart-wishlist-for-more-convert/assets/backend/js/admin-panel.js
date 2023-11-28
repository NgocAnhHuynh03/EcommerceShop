"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }

jQuery(function ($) {
  /**
   * Coupon actions
   */
  var mct_coupon_actions = {
    init: function init() {
      $('select#discount-type').on('change', function () {
        // Get value.
        var select_val = $(this).val();
        var elem = $('#coupon-amount');

        if ('percent' === select_val) {
          elem.removeClass('wc_input_price').addClass('wc_input_decimal');
          elem.siblings('.description').text(wlfmc_wishlist_admin.i18n_percent_description);
        } else {
          elem.removeClass('wc_input_decimal').addClass('wc_input_price');
          elem.siblings('.description').text(wlfmc_wishlist_admin.i18n_amount_description);
        }
      }).trigger('change');
    }
  };
  mct_coupon_actions.init();

  var ImportByCsv = function ImportByCsv(action, offset, element) {
    $.ajax({
      url: wlfmc_wishlist_admin.ajax_url,
      data: {
        action: action,
        key: wlfmc_wishlist_admin.ajax_nonce,
        attachment_id: element.parent().find('input').val(),
        offset: offset
      },
      method: 'post',
      beforeSend: function beforeSend() {
        element.addClass('loading');
      },
      success: function success(response) {
        if (response.success) {
          element.text(response.data.percentage + '%');

          if (100 <= parseInt(response.data.percentage)) {
            setTimeout(function () {
              element.text(element.data('label'));
              element.removeClass('loading');
              element.prop('disabled', false);
              showSnack(response.data.message);
            }, 2000);
          } else {
            ImportByCsv(action, parseInt(response.data.offset), element);
          }
        } else {
          element.text(element.data('label'));
          element.removeClass('loading');
          element.prop('disabled', false);
          showSnack(response.data.message);
        }
      }
    }).fail(function (response) {
      element.removeClass('loading');
      window.console.log(response);
    });
  };

  var ImportByAction = function ImportByAction(action, offset, element) {
    $.ajax({
      url: wlfmc_wishlist_admin.ajax_url,
      data: {
        action: action,
        key: wlfmc_wishlist_admin.ajax_nonce,
        offset: offset
      },
      method: 'post',
      beforeSend: function beforeSend() {
        element.addClass('loading');
      },
      success: function success(response) {
        if (response.success) {
          element.text(response.data.percentage + '%');

          if (100 <= parseInt(response.data.percentage)) {
            setTimeout(function () {
              element.text(element.data('label'));
              element.removeClass('loading');
              element.prop('disabled', false);
              showSnack(response.data.message);
            }, 2000);
          } else {
            ImportByAction(action, parseInt(response.data.offset), element);
          }
        } else {
          element.text(element.data('label'));
          element.removeClass('loading');
          element.prop('disabled', false);
          showSnack(response.data.message);
        }
      }
    }).fail(function (response) {
      element.removeClass('loading');
      window.console.log(response);
    });
  };

  $(document.body).on('click', '.wlfmc-import-by-csv .mct_action_file_button', function (e) {
    var element = $(this);
    e.preventDefault();
    ImportByCsv(element.closest('.wlfmc-import-by-csv').data('action'), 0, element);
    return false;
  });
  $(document.body).on('click', '.wlfmc-import-by-action', function (e) {
    var element = $(this);
    e.preventDefault();
    ImportByAction(element.data('action'), 0, element);
    return false;
  });
  $(document.body).on('change', '.wc_input_price[type=text], .wc_input_decimal[type=text]', function () {
    var regex,
        decimalRegex,
        decimailPoint = wlfmc_wishlist_admin.decimal_point;

    if ($(this).is('.wc_input_price')) {
      decimailPoint = wlfmc_wishlist_admin.mon_decimal_point;
    }

    regex = new RegExp('[^\-0-9\%\\' + decimailPoint + ']+', 'gi');
    decimalRegex = new RegExp('\\' + decimailPoint + '+', 'gi');
    var value = $(this).val();
    var newvalue = value.replace(regex, '').replace(decimalRegex, decimailPoint);

    if (value !== newvalue) {
      $(this).val(newvalue);
    }
  }).on('keyup', // eslint-disable-next-line max-len.
  '.wc_input_price[type=text], .wc_input_decimal[type=text]', function () {
    var regex, error, decimalRegex;
    var checkDecimalNumbers = false;

    if ($(this).is('.wc_input_price')) {
      checkDecimalNumbers = true;
      regex = new RegExp('[^\-0-9\%\\' + wlfmc_wishlist_admin.mon_decimal_point + ']+', 'gi');
      decimalRegex = new RegExp('[^\\' + wlfmc_wishlist_admin.mon_decimal_point + ']', 'gi');
      error = wlfmc_wishlist_admin.i18n_mon_decimal_error;
    } else {
      checkDecimalNumbers = true;
      regex = new RegExp('[^\-0-9\%\\' + wlfmc_wishlist_admin.decimal_point + ']+', 'gi');
      decimalRegex = new RegExp('[^\\' + wlfmc_wishlist_admin.decimal_point + ']', 'gi');
      error = wlfmc_wishlist_admin.i18n_decimal_error;
    }

    var value = $(this).val();
    var newvalue = value.replace(regex, ''); // Check if newvalue have more than one decimal point.

    if (checkDecimalNumbers && 1 < newvalue.replace(decimalRegex, '').length) {
      newvalue = newvalue.replace(decimalRegex, '');
    }

    if (value !== newvalue) {
      showSnack(error);
    }
  });
  $(document.body).on('change', '#sfl_enable', function (e) {
    if ($(this).is(':checked')) {
      $(".nav-tab-wrapper.mct-tabs a[href='#button']").show();
      $(".nav-tab-wrapper.mct-tabs a[href='#page-settings']").show();
    } else {
      $(".nav-tab-wrapper.mct-tabs a[href='#button']").hide();
      $(".nav-tab-wrapper.mct-tabs a[href='#page-settings']").hide();
    }
  }).trigger('change');
  $(document.body).on('change', '#multi_list_enable', function (e) {
    if ($(this).is(':checked')) {
      $(".nav-tab-wrapper.mct-tabs a[href='#button']").show();
      $(".nav-tab-wrapper.mct-tabs a[href='#lists']").show();
      $(".nav-tab-wrapper.mct-tabs a[href='#single-list']").show();
      $(".nav-tab-wrapper.mct-tabs a[href='#counter']").show();
    } else {
      $(".nav-tab-wrapper.mct-tabs a[href='#button']").hide();
      $(".nav-tab-wrapper.mct-tabs a[href='#lists']").hide();
      $(".nav-tab-wrapper.mct-tabs a[href='#single-list']").hide();
      $(".nav-tab-wrapper.mct-tabs a[href='#counter']").hide();
    }
  }).trigger('change');
  $(document.body).on('change', '#wishlist_enable', function (e) {
    if ($(this).is(':checked')) {
      $(".nav-tab-wrapper.mct-tabs a[href='#button']").show();
      $(".nav-tab-wrapper.mct-tabs a[href='#page-settings']").show();
      $(".nav-tab-wrapper.mct-tabs a[href='#counter']").show();
    } else {
      $(".nav-tab-wrapper.mct-tabs a[href='#button']").hide();
      $(".nav-tab-wrapper.mct-tabs a[href='#page-settings']").hide();
      $(".nav-tab-wrapper.mct-tabs a[href='#counter']").hide();
    }
  }).trigger('change');
  $(document.body).on('change', '#waitlist_enable', function (e) {
    if ($(this).is(':checked')) {
      $(".nav-tab-wrapper.mct-tabs a[href='#button']").show();
      $(".nav-tab-wrapper.mct-tabs a[href='#page-settings']").show();
      $(".nav-tab-wrapper.mct-tabs a[href='#counter']").show();
    } else {
      $(".nav-tab-wrapper.mct-tabs a[href='#button']").hide();
      $(".nav-tab-wrapper.mct-tabs a[href='#page-settings']").hide();
      $(".nav-tab-wrapper.mct-tabs a[href='#counter']").hide();
    }
  }).trigger('change');
  $(document.body).on('change', 'select#sfl_button_position', function (e) {
    if ('with_remove_icon' === $(this).val()) {
      $("#sfl_button_type_text").attr('disabled', true);
      $("#sfl_button_type_both").attr('disabled', true);
      $("#sfl_button_type_icon").prop('checked', true);
      $(".row-sfl_button_type").hide();
    } else {
      $("#sfl_button_type_text").removeAttr('disabled');
      $("#sfl_button_type_both").removeAttr('disabled');
      $(".row-sfl_button_type").show();
    }
  });
  $(document.body).on('click', '.wlfmc-built-wishlist-page:not(.disabled)', function (e) {
    e.preventDefault();
    var elem = $(this),
        oldtext = elem.text();
    elem.addClass('disabled');
    elem.text(wlfmc_wishlist_admin.i18n_making_page);
    $.ajax({
      url: wlfmc_wishlist_admin.ajax_url,
      data: {
        action: 'wlfmc_create_wishlist_page',
        key: wlfmc_wishlist_admin.ajax_nonce
      },
      dataType: 'json',
      method: 'POST',
      success: function success(data) {
        if (data && data.success) {
          location.reload(true);
        }
      },
      error: function error() {
        console.log('We cant create page. Something wrong with AJAX response. Probably some PHP conflict.');
      },
      complete: function complete() {
        elem.removeClass('disabled');
        elem.text(oldtext);
      }
    });
    return false;
  });
  $(document.body).on('click', '.wlfmc-built-waitlist-page:not(.disabled)', function (e) {
    e.preventDefault();
    var elem = $(this),
        oldtext = elem.text();
    elem.addClass('disabled');
    elem.text(wlfmc_wishlist_admin.i18n_making_page);
    $.ajax({
      url: wlfmc_wishlist_admin.ajax_url,
      data: {
        action: 'wlfmc_create_waitlist_page',
        key: wlfmc_wishlist_admin.ajax_nonce
      },
      dataType: 'json',
      method: 'POST',
      success: function success(data) {
        if (data && data.success) {
          location.reload(true);
        }
      },
      error: function error() {
        console.log('We cant create page. Something wrong with AJAX response. Probably some PHP conflict.');
      },
      complete: function complete() {
        elem.removeClass('disabled');
        elem.text(oldtext);
      }
    });
    return false;
  });
  $(document.body).on('click', '.wlfmc-built-lists-page:not(.disabled)', function (e) {
    e.preventDefault();
    var elem = $(this),
        oldtext = elem.text();
    elem.addClass('disabled');
    elem.text(wlfmc_wishlist_admin.i18n_making_page);
    $.ajax({
      url: wlfmc_wishlist_admin.ajax_url,
      data: {
        action: 'wlfmc_create_lists_page',
        key: wlfmc_wishlist_admin.ajax_nonce
      },
      dataType: 'json',
      method: 'POST',
      success: function success(data) {
        if (data && data.success) {
          location.reload(true);
        }
      },
      error: function error() {
        console.log('We cant create page. Something wrong with AJAX response. Probably some PHP conflict.');
      },
      complete: function complete() {
        elem.removeClass('disabled');
        elem.text(oldtext);
      }
    });
    return false;
  });
  $(document.body).on('click', '.wlfmc-built-tabbed-page:not(.disabled)', function (e) {
    e.preventDefault();
    var elem = $(this),
        oldtext = elem.text();
    elem.addClass('disabled');
    elem.text(wlfmc_wishlist_admin.i18n_making_page);
    $.ajax({
      url: wlfmc_wishlist_admin.ajax_url,
      data: {
        action: 'wlfmc_create_tabbed_page',
        key: wlfmc_wishlist_admin.ajax_nonce
      },
      dataType: 'json',
      method: 'POST',
      success: function success(data) {
        if (data && data.success) {
          location.reload(true);
        }
      },
      error: function error() {
        console.log('We cant create page. Something wrong with AJAX response. Probably some PHP conflict.');
      },
      complete: function complete() {
        elem.removeClass('disabled');
        elem.text(oldtext);
      }
    });
    return false;
  });
  $(document).on('wlfmc_automation_init', function () {
    var t = $(this);
    t.on('click', '.save-automation', function (e) {
      e.preventDefault();
      save_automation($(this), 'wlfmc_save_automation');
      return false;
    });
    t.on('click', '.new-automation', function (e) {
      e.preventDefault();
      save_automation($(this), 'wlfmc_new_automation');
      return false;
    });
    t.on('change', "form#wlfmc_automation_options_form :input", function () {
      $('.ajax-message-holder').html('');
    });
    t.on('click', '.wlfmc-send-offer-email-test', function (e) {
      e.preventDefault();
      var elem = $(this),
          oldtext = elem.text();
      elem.addClass('disabled');
      elem.text(wlfmc_wishlist_admin.i18n_sending);
      $.ajax({
        url: wlfmc_wishlist_admin.ajax_url,
        data: {
          id: elem.data('id'),
          automation_id: $('#automation_id').val(),
          action: 'wlfmc_send_offer_email_test',
          key: wlfmc_wishlist_admin.ajax_nonce
        },
        dataType: 'json',
        method: 'POST',
        success: function success(data) {
          if (data && data.message) {
            showSnack(data.message);
          }
        },
        error: function error() {
          console.log('We cant Send email. Something wrong with AJAX response. Probably some PHP conflict.');
        },
        complete: function complete() {
          elem.removeClass('disabled');
          elem.text(oldtext);
        }
      });
      return false;
    });
    t.on('click', '.wlfmc-reset-sending-cycles', function (e) {
      e.preventDefault();
      var elem = $(this),
          oldtext = elem.text();

      if (confirm(wlfmc_wishlist_admin.i18n_resetting_confirm)) {
        elem.addClass('disabled');
        elem.text(wlfmc_wishlist_admin.i18n_resetting);
        $.ajax({
          url: wlfmc_wishlist_admin.ajax_url,
          data: {
            action: 'wlfmc_reset_sending_cycles_automation',
            key: wlfmc_wishlist_admin.ajax_nonce
          },
          dataType: 'json',
          method: 'POST',
          success: function success(data) {
            if (data && data.message) {
              showSnack(data.message);
            }
          },
          error: function error() {
            console.log('We cant Send email. Something wrong with AJAX response. Probably some PHP conflict.');
          },
          complete: function complete() {
            elem.removeClass('disabled');
            elem.text(oldtext);
          }
        });
      }

      return false;
    });
  }).trigger('wlfmc_automation_init');

  function save_automation(elem, action) {
    if (typeof tinyMCE !== 'undefined') {
      tinyMCE.triggerSave(true, true);
    }

    var data = {
      action: action,
      key: wlfmc_wishlist_admin.ajax_nonce,
      options: $('form#wlfmc_automation_options_form').serialize()
    };
    $.ajax({
      url: wlfmc_wishlist_admin.ajax_url,
      data: data,
      type: 'POST',
      beforeSend: function beforeSend() {
        elem.addClass('loading');
      },
      complete: function complete() {
        elem.removeClass('loading');
      },
      success: function success(response) {
        if (true === response.status) {
          show_message(response.errors, 'mct-updated');
        } else if (true === response.show_pro_popup) {
          var modal = elem.data('modal');
          $('#' + modal).removeAttr('style').toggleClass('is-visible');
        } else {
          show_message(response.errors, 'mct-error');
        }

        if (response.url && '' !== response.url) {
          window.location.href = response.url;
        }
      }
    });
  } // Preview email templates.


  var is_loading_preview = null;
  $(document).on('change', '#wlfmc_automation_options_form', function (e) {
    e.preventDefault();
    var preview_wrapper = $('.preview_iframe_wrapper:visible');
    var heading = $('.mail_heading:visible').val();
    var template = $('#mail-type').val();
    var index = $('#offer_emails .manage-row.is-current .wlfmc-send-offer-email-test').data('id');

    if (typeof index === 'undefined') {
      return false;
    }

    var content = 'plain' === template ? $('.text_content:visible').val() : typeof tinyMCE !== 'undefined' && tinyMCE.get('offer_emails_' + index + '__html_content_') ? tinyMCE.get('offer_emails_' + index + '__html_content_').getContent() : $('#offer_emails_' + index + '__html_content_').val();
    var footer = 'plain' === template ? $('.text_footer:visible').val() : typeof tinyMCE !== 'undefined' && tinyMCE.get('offer_emails_' + index + '__html_footer_') ? tinyMCE.get('offer_emails_' + index + '__html_footer_').getContent() : $('#offer_emails_' + index + '__html_footer_').val();

    if (!preview_wrapper.length) {
      return false;
    }

    update_preview(preview_wrapper, template, heading, content, footer, $(this));
    return false;
  });

  if ($('#wlfmc_automation_options_form').length) {
    setTimeout(function () {
      var length = tinymce.editors.length;

      for (var i = 0; i < length; i++) {
        tinymce.editors[i].on('input-dirty keyup change', function () {
          $('#wlfmc_automation_options_form').trigger('change');
        });
      }
    }, 2000);
    $(document).on('changed-manage-item', function (e) {
      $('#wlfmc_automation_options_form').trigger('change');
    });
  }

  function update_preview(preview, template, heading, content, footer, form) {
    if (is_loading_preview) {
      is_loading_preview.abort();
    }

    var formData = new FormData();
    var data = {
      action: 'wlfmc_preview_automation_template',
      key: wlfmc_wishlist_admin.ajax_nonce,
      template: template,
      heading: heading,
      content: content,
      footer: footer
    };

    if (form || form.length > 0) {
      formData = new FormData(form.get(0));
    }

    $.each(data, function (key, valueObj) {
      formData.append(key, _typeof(valueObj) === 'object' ? JSON.stringify(valueObj) : valueObj);
    });
    is_loading_preview = $.ajax({
      url: wlfmc_wishlist_admin.ajax_url,
      contentType: false,
      processData: false,
      cache: false,
      data: formData,
      method: 'POST',
      beforeSend: function beforeSend() {
        preview.closest('.mct-column').addClass('block-loading');
      },
      complete: function complete() {
        preview.closest('.mct-column').removeClass('block-loading');
      },
      success: function success(response) {
        var result = response.data;
        var iframeDoc = preview[0].contentDocument;
        iframeDoc.open();
        iframeDoc.write(result.html);
        iframeDoc.close();

        if ('html' === template) {
          $(iframeDoc).contents().find("#wrapper > table").removeAttr('height');
        }
      }
    });
  }

  function show_message(messages, type) {
    var wrapper = $('.ajax-message-holder .mct-message');
    var output = '';

    if (Array.isArray(messages)) {
      $.each(messages, function (index, message) {
        output += '' !== message ? '<li>' + message + '</li>' : '';
      });
    } else {
      output += '' !== messages ? '<li>' + messages + '</li>' : '';
    }

    if ('' !== output) {
      if (!wrapper.length) {
        $('.ajax-message-holder').append('<div  class="mct-message ' + type + '"><ul></ul></div>');
      }

      $('.ajax-message-holder .mct-message').find('ul').html(output);
      $("html, body").animate({
        scrollTop: 0
      }, "slow");
    }
  }

  function showSnack(error) {
    var x = document.getElementById("snackbar");
    x.innerHTML = error;
    x.className = "show";
    setTimeout(function () {
      x.className = x.className.replace("show", "");
    }, 3000);
  }
});