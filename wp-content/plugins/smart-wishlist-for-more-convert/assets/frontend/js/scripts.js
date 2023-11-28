"use strict";

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _typeof2(obj) { "@babel/helpers - typeof"; return _typeof2 = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof2(obj); }

/**
 * Main Smart WooCommerce Wishlist JS
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 *
 * @version 1.5.2
 */

/*jshint esversion: 6 */
(function ($) {
  $.noConflict();
  $(document).ready(function () {
    /* === MAIN INIT === */
    var product_in_list = [],
        product_in_waitlist = [],
        lang = wlfmc_l10n.lang,
        remove_item_url = null,
        wishlist_items = wlfmc_l10n.wishlist_items,
        waitlist_items = wlfmc_l10n.waitlist_items,
        product_adding = false,
        fragmentxhr,
        fragmenttimeout;
    toastr.options = {
      tapToDismiss: true,
      toastClass: 'toast',
      containerId: 'toast-container',
      debug: false,
      closeButton: false,
      showMethod: 'fadeIn',
      showDuration: 300,
      showEasing: 'swing',
      onShown: undefined,
      hideMethod: 'fadeOut',
      hideDuration: 1000,
      hideEasing: 'swing',
      onHidden: undefined,
      closeMethod: false,
      closeDuration: false,
      closeEasing: false,
      closeOnHover: true,
      extendedTimeOut: 20000,
      iconClasses: {
        error: 'toast-error',
        info: 'toast-info',
        success: 'toast-success',
        warning: 'toast-warning'
      },
      iconClass: 'toast-info',
      positionClass: wlfmc_l10n.toast_position === 'default' ? wlfmc_l10n.is_rtl ? 'toast-top-right' : 'toast-top-left' : wlfmc_l10n.toast_position,
      timeOut: 5000,
      titleClass: 'toast-title',
      messageClass: 'toast-message',
      escapeHtml: false,
      target: 'body',
      newestOnTop: true,
      preventDuplicates: false,
      progressBar: true,
      progressClass: 'toast-progress',
      rtl: wlfmc_l10n.is_rtl ? true : false
    };

    if (wlfmc_l10n.is_save_for_later_enabled && wlfmc_l10n.is_save_for_later_popup_remove_enabled) {
      $('.woocommerce-cart-form .product-remove a.remove').off('click').unbind('click').data('events', null);
      $('body').on('click', 'form.woocommerce-cart-form a.remove', function (ev) {
        ev.stopImmediatePropagation();
        ev.preventDefault();
        var t = $(this);
        remove_item_url = t.attr('href');
        var elem = $('#add_to_save_for_later_popup');
        var defaultOptions = {
          absolute: false,
          color: '#333',
          transition: 'all 0.3s',
          horizontal: elem.data('horizontal'),
          vertical: elem.data('vertical')
        };
        elem.popup(defaultOptions);
        elem.popup('show');
        return false;
      });
    }

    $(document).on('wlfmc_init', function () {
      init_fix_on_image_single_position();
      var t = $(this),
          cart_redirect_after_add = typeof wc_add_to_cart_params !== 'undefined' && wc_add_to_cart_params !== null ? wc_add_to_cart_params.cart_redirect_after_add : '';
      t.on('click', '.wlfmc-list button[name="apply_bulk_actions"]', function (ev) {
        var elem = $(this).closest('.action-wrapper').find('select[name="bulk_actions"]');
        var quantity_fields = $(this).closest('form').find('input.qty');

        if (elem.length > 0 && 'delete' === elem.val() && quantity_fields.length > 0) {
          quantity_fields.attr("disabled", true);
        }
      });
      t.on('click', '.wlfmc-list .remove-all-from-save-for-later-btn', function (ev) {
        var quantity_fields = $(this).closest('form').find('input.qty');

        if (quantity_fields.length > 0) {
          quantity_fields.attr("disabled", true);
        }
      });
      t.on('click', 'body.elementor-editor-active .wlfmc-lists-header a,body.elementor-editor-active a.wlfmc-open-list-link', function (ev) {
        var href = $(this).attr('href');

        if (href && href !== '#' && href !== '#!') {
          block($('.wlfmc-tab-content'));
          ev.preventDefault();
          $.ajax({
            url: href,
            type: 'GET',
            dataType: 'html',
            success: function success(data) {
              var $response = $(data),
                  $header = $response.find('.wlfmc-lists-header'),
                  $content = $response.find('.wlfmc-tab-content');

              if ($content) {
                $('.wlfmc-tab-content').replaceWith($content);
              }

              if ($header) {
                $('.wlfmc-lists-header').replaceWith($header);
              }

              if (href !== window.location.href) {
                window.history.pushState({
                  path: href
                }, '', href);
              }
            }
          });
          return false;
        }
      });
      /* === SAVE FOR LATER === */

      t.on('click', '.wlfmc_add_to_save_for_later', function (ev) {
        ev.preventDefault();
        var t = $(this),
            cart_item_key = t.attr('data-cart_item_key');
        add_to_save_for_later(cart_item_key, t);
        return false;
      });
      t.on('click', '.wlfmc_remove_from_save_for_later', function (ev) {
        ev.preventDefault();
        var t = $(this),
            row = t.parents('[data-item-id]'),
            data_item_id = row.data('item-id'),
            data = {
          action: wlfmc_l10n.actions.remove_from_save_for_later_action,
          nonce: wlfmc_l10n.ajax_nonce.remove_from_save_for_later_nonce,
          context: 'frontend',
          remove_from_save_for_later: data_item_id //fragments: retrieve_fragments()

        };
        $.ajax({
          url: wlfmc_l10n.save_for_later_ajax_url,
          data: data,
          method: 'post',
          beforeSend: function beforeSend(xhr) {
            if (wlfmc_l10n.ajax_mode === 'rest_api') {
              xhr.setRequestHeader('X-WP-Nonce', wlfmc_l10n.nonce);
            } //block( row );


            loading(t);
          },
          complete: function complete() {
            //unblock( row );
            unloading(t);
          },
          success: function success(data) {
            if (!data) {
              return;
            }

            load_fragments();
            /*if (typeof data.fragments !== 'undefined') {
            	replace_fragments( data.fragments );
            }*/

            if (data.count !== 'undefined') {
              $('.wlfmc-tabs-wrapper').attr('data-count', data.count);
            }

            if ('true' === data.result && '' !== $.trim(wlfmc_l10n.labels.sfl_product_removed_text)) {
              toastr.error(wlfmc_l10n.labels.sfl_product_removed_text);
            }

            if (data.count !== 'undefined' && 0 === data.count) {
              $('.wlfmc-save-for-later-table-wrapper').empty();
            }

            init_handling_after_ajax();
            $('body').trigger('wlfmc_removed_from_save_for_later', [t, row, data]);
          }
        });
        return false;
      });
      t.on('click', '.wlfmc_save_for_later_ajax_add_to_cart', function (ev) {
        var t = $(this),
            item_id = t.attr('data-item_id'),
            data = {
          action: wlfmc_l10n.actions.save_for_later_add_to_cart_action,
          nonce: wlfmc_l10n.ajax_nonce.add_to_cart_from_sfl_nonce,
          context: 'frontend',
          item_id: item_id //fragments: retrieve_fragments()

        };
        ev.preventDefault();
        t.removeClass('added');
        t.addClass('loading'); // Allow 3rd parties to validate and quit early.

        if (false === $(document.body).triggerHandler('should_send_ajax_request.adding_to_cart', [t])) {
          $(document.body).trigger('ajax_request_not_sent.adding_to_cart', [false, false, t]);
          return true;
        }

        $(document.body).trigger('adding_to_cart', [t, {}]);
        $.ajax({
          url: wlfmc_l10n.save_for_later_ajax_url,
          data: data,
          method: 'post',
          //dataType: 'json',
          beforeSend: function beforeSend(xhr) {
            if (wlfmc_l10n.ajax_mode === 'rest_api') {
              xhr.setRequestHeader('X-WP-Nonce', wlfmc_l10n.nonce);
            }
          },
          success: function success(response) {
            if (!response) {
              return;
            }

            if (typeof response.fragments !== 'undefined') {
              replace_fragments(response.fragments);
            }

            if (response.error) {
              toastr.error(response.error);
              return;
            }

            if (!$('form.woocommerce-cart-form').length) {
              location.reload();
            } else {
              if (typeof response.fragments !== 'undefined' && response.fragments.wlfmc_save_for_later_count !== 'undefined') {
                $('.wlfmc-tabs-wrapper').attr('data-count', response.fragments.wlfmc_save_for_later_count);
              }

              $(document.body).trigger('wc_fragment_refresh'); // Trigger event so themes can refresh other areas.

              $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, t]);
            }
          }
        });
        return false;
      });
      t.on('click', '#add_to_save_for_later_popup #add_item', function (ev) {
        ev.preventDefault();
        $('#add_to_save_for_later_popup').popup('hide');
        var cart_item_key = getUrlParameter(remove_item_url, 'remove_item');
        var row = $('a[href="' + remove_item_url + '"]').closest('tr');
        add_to_save_for_later(cart_item_key, row);
        return false;
      });
      t.on('click', '#add_to_save_for_later_popup #remove_item', function (ev) {
        ev.preventDefault();
        $('#add_to_save_for_later_popup').popup('hide');
        window.location.href = remove_item_url;
        return false;
      });
      t.on('click', '.wlfmc-sfl-btn-login-need', function (ev) {
        ev.preventDefault();
        toastr.error(wlfmc_l10n.labels.sfl_login_need);
        return false;
      });
      /* === Waitlist === */

      t.on('click', '.wlfmc-waitlist-btn-login-need', function (ev) {
        ev.preventDefault();
        toastr.error(wlfmc_l10n.labels.waitlist_login_need);
        return false;
      });
      t.on('wlfmc_show_variation', function (ev, data) {
        var product_id = data.product_id,
            variation_id = data.variation_id;
        check_waitlist_modules(data, product_id, variation_id);
      });
      t.on('reset_data', function (ev) {
        var t = $(ev.target),
            product_id = t.data('product_id');
        check_waitlist_modules(null, product_id, product_id);

        if (isTrue(wlfmc_l10n.waitlist_required_product_variation)) {
          var elem = $('.wlfmc-add-to-waitlist-' + product_id);
          elem.find('.wlfmc-popup-trigger').addClass('wlfmc-disabled');
        }
      });
      t.on('hide_variation', '.variations_form', function (a) {
        var elem = $('.wlfmc-add-to-waitlist-' + $(this).data('product_id'));

        if (elem.length > 0 && isTrue(wlfmc_l10n.waitlist_required_product_variation)) {
          a.preventDefault();
          elem.find('.wlfmc-popup-trigger').addClass('wlfmc-disabled');
        }
      });
      t.on('show_variation', '.variations_form', function (a, b, d) {
        var elem = $('.wlfmc-add-to-waitlist-' + $(this).data('product_id'));

        if (elem.length > 0) {
          a.preventDefault();
          elem.find('.wlfmc-popup-trigger').removeClass('wlfmc-disabled');
        }
      });
      t.on('click', '.wlfmc-add-to-waitlist .wlfmc-popup-trigger.wlfmc-disabled', function (ev) {
        ev.preventDefault();
        toastr.error(wlfmc_l10n.labels.waitlist_make_a_selection_text);
        return false;
      });
      t.on('click', '.wlfmc_add_to_waitlist', function (ev) {
        var t = $(this),
            product_id = t.attr('data-product-id'),
            parent_product_id = t.attr('data-parent-product-id'),
            filtered_data = null,
            popup = t.closest('.wlfmc-popup'),
            on_sale = popup.find('.wlfmc-waitlist-select-type input[name=list_on-sale]:checked').length > 0 ? 1 : 0,
            back_in_stock = popup.find('.wlfmc-waitlist-select-type input[name=list_back-in-stock]:checked').length > 0 ? 1 : 0,
            price_change = popup.find('.wlfmc-waitlist-select-type input[name=list_price-change]:checked').length > 0 ? 1 : 0,
            low_stock = popup.find('.wlfmc-waitlist-select-type input[name=list_low-stock]:checked').length > 0 ? 1 : 0,
            email = popup.find('input[name=wlfmc_email]').length > 0 ? popup.find('input[name=wlfmc_email]').val().trim() : false,
            data = {
          action: wlfmc_l10n.actions.add_product_to_waitlist_action,
          nonce: wlfmc_l10n.ajax_nonce.add_product_to_waitlist_nonce,
          context: 'frontend',
          add_to_waitlist: product_id,
          product_type: t.attr('data-product-type'),
          on_sale: on_sale,
          back_in_stock: back_in_stock,
          price_change: price_change,
          low_stock: low_stock,
          wlfmc_email: email
        };

        if (false !== email && '' === email) {
          toastr.error(wlfmc_l10n.labels.waitlist_email_required);
          return;
        }

        if (false !== email && !validateEmail(email)) {
          toastr.error(wlfmc_l10n.labels.waitlist_email_format);
          return;
        } // allow third party code to filter data.


        if (filtered_data === $(document).triggerHandler('wlfmc_add_to_waitlist_data', [t, data])) {
          data = filtered_data;
        }

        var current_product_form;

        if ($('form.cart[method=post][data-product_id="' + parent_product_id + '"], form.vtajaxform[method=post][data-product_id="' + parent_product_id + '"]').length) {
          current_product_form = $('form.cart[method=post][data-product_id="' + parent_product_id + '"], form.vtajaxform[method=post][data-product_id="' + parent_product_id + '"]').eq(0);
        } else if ($(this).closest('form.cart[method=post], form.vtajaxform[method=post]').length) {
          current_product_form = $(this).closest('form.cart[method=post], form.vtajaxform[method=post]').eq(0);
        } else if ($('#product-' + parent_product_id + ' form.cart[method=post],#product-' + parent_product_id + ' form.vtajaxform[method=post]').length) {
          current_product_form = $('#product-' + parent_product_id + ' form.cart[method=post],#product-' + parent_product_id + ' form.vtajaxform[method=post]').eq(0);
        } else if ($('form.cart[method=post] button[name="add-to-cart"][value="' + parent_product_id + '"],form.vtajaxform[method=post] button[name="add-to-cart"][value="' + parent_product_id + '"]').length) {
          var button = $('form.cart[method=post] button[name="add-to-cart"][value="' + parent_product_id + '"],form.vtajaxform[method=post] button[name="add-to-cart"][value="' + parent_product_id + '"]');
          current_product_form = button.closest('form').eq(0);
        }

        var formData = new FormData();

        if (typeof current_product_form !== 'undefined' && current_product_form.length > 0) {
          formData = new FormData(current_product_form.get(0));
          formData["delete"]('add-to-cart');
        }

        $.each(data, function (key, valueObj) {
          formData.append(key, _typeof2(valueObj) === 'object' ? JSON.stringify(valueObj) : valueObj);
        });
        ev.preventDefault();
        jQuery(document.body).trigger('wlfmc_adding_to_waitlist');

        if (!is_cookie_enabled()) {
          window.alert(wlfmc_l10n.labels.cookie_disabled);
          return;
        }

        $.ajax({
          url: wlfmc_l10n.waitlist_ajax_url,
          data: formData,
          type: 'POST',
          //dataType: 'json',
          contentType: false,
          processData: false,
          cache: false,
          beforeSend: function beforeSend(xhr) {
            if (wlfmc_l10n.ajax_mode === 'rest_api') {
              xhr.setRequestHeader('X-WP-Nonce', wlfmc_l10n.nonce);
            } //block( t );


            loading(t);
          },
          complete: function complete() {
            //unblock( t );
            unloading(t);
          },
          success: function success(response) {
            var response_result = response.result,
                response_message = response.message,
                show_toast = true;

            if (response_result === 'true') {
              load_fragments();
              popup.popup('hide');

              if (show_toast && '' !== $.trim(response_message)) {
                toastr.success(response_message);
              }

              var list_type = 'waitlist';
              if (on_sale === 1) list_type += ',on-sale';
              if (price_change === 1) list_type += ',price-change';
              if (back_in_stock === 1) list_type += ',back-in-stock';
              if (low_stock === 1) list_type += ',low-stock';
              load_automations(product_id, response.wishlist_id, response.customer_id, list_type);
            }

            if (show_toast && '' !== $.trim(response.message) && response_result !== 'true') {
              toastr.error(response_message);
            }
          }
        });
        return false;
      });
      t.on('click', '.wlfmc_add_to_outofstock', function (ev) {
        var t = $(this),
            product_id = t.attr('data-product-id'),
            parent_product_id = t.attr('data-parent-product-id'),
            wlfmc_email = t.closest('.wlfmc-add-to-outofstock').find('input[name=wlfmc_email]'),
            filtered_data = null,
            email = wlfmc_email.length > 0 ? wlfmc_email.val().trim() : false,
            data = {
          action: wlfmc_l10n.actions.add_product_to_waitlist_action,
          nonce: wlfmc_l10n.ajax_nonce.add_product_to_waitlist_nonce,
          context: 'frontend',
          add_to_waitlist: product_id,
          product_type: t.attr('data-product-type'),
          on_sale: 0,
          back_in_stock: 1,
          price_change: 0,
          low_stock: 0,
          wlfmc_email: email
        };

        if (false !== email && '' === email) {
          toastr.error(wlfmc_l10n.labels.waitlist_email_required);
          return;
        }

        if (false !== email && !validateEmail(email)) {
          toastr.error(wlfmc_l10n.labels.waitlist_email_format);
          return;
        } // allow third party code to filter data.


        if (filtered_data === $(document).triggerHandler('wlfmc_add_to_waitlist_data', [t, data])) {
          data = filtered_data;
        }

        var current_product_form;

        if ($('form.cart[method=post][data-product_id="' + parent_product_id + '"], form.vtajaxform[method=post][data-product_id="' + parent_product_id + '"]').length) {
          current_product_form = $('form.cart[method=post][data-product_id="' + parent_product_id + '"], form.vtajaxform[method=post][data-product_id="' + parent_product_id + '"]').eq(0);
        } else if ($(this).closest('form.cart[method=post], form.vtajaxform[method=post]').length) {
          current_product_form = $(this).closest('form.cart[method=post], form.vtajaxform[method=post]').eq(0);
        } else if ($('#product-' + parent_product_id + ' form.cart[method=post],#product-' + parent_product_id + ' form.vtajaxform[method=post]').length) {
          current_product_form = $('#product-' + parent_product_id + ' form.cart[method=post],#product-' + parent_product_id + ' form.vtajaxform[method=post]').eq(0);
        } else if ($('form.cart[method=post] button[name="add-to-cart"][value="' + parent_product_id + '"],form.vtajaxform[method=post] button[name="add-to-cart"][value="' + parent_product_id + '"]').length) {
          var button = $('form.cart[method=post] button[name="add-to-cart"][value="' + parent_product_id + '"],form.vtajaxform[method=post] button[name="add-to-cart"][value="' + parent_product_id + '"]');
          current_product_form = button.closest('form').eq(0);
        }

        var formData = new FormData();

        if (typeof current_product_form !== 'undefined' && current_product_form.length > 0) {
          formData = new FormData(current_product_form.get(0));
          formData["delete"]('add-to-cart');
        }

        $.each(data, function (key, valueObj) {
          formData.append(key, _typeof2(valueObj) === 'object' ? JSON.stringify(valueObj) : valueObj);
        });
        ev.preventDefault();
        jQuery(document.body).trigger('wlfmc_adding_to_waitlist');

        if (!is_cookie_enabled()) {
          window.alert(wlfmc_l10n.labels.cookie_disabled);
          return;
        }

        $.ajax({
          url: wlfmc_l10n.waitlist_ajax_url,
          data: formData,
          type: 'POST',
          //dataType: 'json',
          contentType: false,
          processData: false,
          cache: false,
          beforeSend: function beforeSend(xhr) {
            if (wlfmc_l10n.ajax_mode === 'rest_api') {
              xhr.setRequestHeader('X-WP-Nonce', wlfmc_l10n.nonce);
            } //block( t );


            loading(t);
          },
          complete: function complete() {
            //unblock( t );
            unloading(t);
          },
          success: function success(response) {
            var response_result = response.result,
                response_message = response.message,
                show_toast = true;

            if (response_result === 'true') {
              load_fragments();

              if (show_toast && '' !== $.trim(response_message)) {
                toastr.success(response_message);
              }

              $('.wlfmc-add-to-outofstock-' + product_id).addClass('exists');
              load_automations(product_id, response.wishlist_id, response.customer_id, 'back-in-stock');
            }

            if (show_toast && '' !== $.trim(response.message) && response_result !== 'true') {
              toastr.error(response_message);
            }
          }
        });
        return false;
      });
      t.on('click', '.wlfmc_cancel_outofstock', function (ev) {
        var t = $(this),
            product_id = t.attr('data-product-id'),
            parent_product_id = t.attr('data-parent-product-id'),
            data = {
          action: wlfmc_l10n.actions.add_product_to_waitlist_action,
          nonce: wlfmc_l10n.ajax_nonce.add_product_to_waitlist_nonce,
          context: 'frontend',
          add_to_waitlist: product_id,
          product_type: t.attr('data-product-type'),
          on_sale: 0,
          back_in_stock: 0,
          price_change: 0,
          low_stock: 0
        };
        ev.preventDefault();

        if (!is_cookie_enabled()) {
          window.alert(wlfmc_l10n.labels.cookie_disabled);
          return;
        }

        $.ajax({
          url: wlfmc_l10n.waitlist_ajax_url,
          data: data,
          type: 'POST',
          beforeSend: function beforeSend(xhr) {
            if (wlfmc_l10n.ajax_mode === 'rest_api') {
              xhr.setRequestHeader('X-WP-Nonce', wlfmc_l10n.nonce);
            } //block( t );


            loading(t);
          },
          complete: function complete() {
            //unblock( t );
            unloading(t);
          },
          success: function success(response) {
            var response_result = response.result,
                response_message = response.message,
                show_toast = true;

            if (response_result === 'true') {
              if (show_toast && '' !== $.trim(response_message)) {
                toastr.success(response_message);
              }

              $('.wlfmc-add-to-outofstock-' + product_id).removeClass('exists');
              load_fragments();
            }

            if (show_toast && '' !== $.trim(response.message) && response_result !== 'true') {
              toastr.error(response_message);
            }
          }
        });
        return false;
      });
      /* === MULTI LIST === */

      t.on('input', '.wlfmc-popup-lists-wrapper .search-input', function () {
        var searchTerm = $(this).val().toLowerCase();
        var list = $(this).closest('.wlfmc-popup-lists-wrapper').find('.list');
        var noResultsRow = $(this).closest('.wlfmc-popup-lists-wrapper').find('.no-results-row');
        var numVisibleItems = 0;

        if (list.find('.list-item').length) {
          list.find('.list-item').each(function () {
            var text = $(this).text().toLowerCase();

            if (text.indexOf(searchTerm) > -1) {
              $(this).show();
              numVisibleItems++;
            } else {
              $(this).hide();
            }
          });

          if (numVisibleItems === 0) {
            noResultsRow.show();
          } else {
            noResultsRow.hide();
          }
        } else {
          $(this).val('');
        }
      });
      /*t.on(
      	'change',
      	'.wlfmc-add-to-list-container .list-item-checkbox',
      	function() {
      		var selectedItem = $( this ).closest( '.list-item' );
      				if ($( this ).is( ':checked' )) {
      			selectedItem.addClass( 'selected' );
      		} else {
      			selectedItem.removeClass( 'selected' );
      		}
      	}
      );
      		t.on(
      	'change',
      	'.wlfmc-move-to-list-wrapper .list-item-checkbox',
      	function() {
      		var selectedItem = $( this ).closest( '.list-item' );
      		var checkboxes = $(this).closest('.wlfmc-move-to-list-wrapper').find('input[name="destination_wishlist_id"]');
      		$(this).closest('.wlfmc-move-to-list-wrapper').find('.list-item').removeClass( 'selected' );
      		if ($( this ).is( ':checked' )) {
      			selectedItem.addClass( 'selected' );
      			checkboxes.not($(this)).prop('checked', false);
      		}
      	}
      );
      		t.on(
      	'change',
      	'.wlfmc-copy-to-list-wrapper .list-item-checkbox',
      	function() {
      		var selectedItem = $( this ).closest( '.list-item' );
      		var checkboxes = $(this).closest('.wlfmc-copy-to-list-wrapper').find('input[name="destination_wishlist_id"]');
      		$(this).closest('.wlfmc-copy-to-list-wrapper').find('.list-item').removeClass( 'selected' );
      		if ($( this ).is( ':checked' )) {
      			selectedItem.addClass( 'selected' );
      			checkboxes.not($(this)).prop('checked', false);
      		}
      	}
      );*/

      t.on('click', '.wlfmc-create-new-list', function () {
        var parent_popup = $('#' + $(this).attr('data-parent-popup-id'));
        var child_popup = $('#' + $(this).attr('data-child-popup-id'));
        parent_popup.popup('hide');
        var defaultOptions = {
          absolute: false,
          color: '#333',
          transition: 'all 0.1s',
          horizontal: child_popup.data('horizontal'),
          vertical: child_popup.data('vertical')
        };
        child_popup.popup(_objectSpread(_objectSpread({}, defaultOptions), {}, {
          onclose: function onclose() {
            parent_popup.popup('show');
            child_popup.popup(_objectSpread(_objectSpread({}, defaultOptions), {}, {
              onclose: null
            }));
          }
        }));
        child_popup.popup('show');
      });
      t.on('click', '.wlfmc-toggle-privacy', function (ev) {
        ev.preventDefault();
        var elem = $(this),
            row = elem.parents('[data-wishlist-id]'),
            old_privacy = parseInt(elem.attr('data-privacy')),
            new_privacy = 0 === old_privacy ? 1 : 0,
            wishlist_id = row.attr('data-wishlist-id'),
            icon = elem.find('i'),
            edit_popup = $('#edit_popup_' + wishlist_id);
        $.ajax({
          url: wlfmc_l10n.multi_list_ajax_url,
          data: {
            action: wlfmc_l10n.actions.change_privacy_action,
            nonce: wlfmc_l10n.ajax_nonce.change_privacy_nonce,
            context: 'frontend',
            'wishlist_id': wishlist_id,
            'list_privacy': new_privacy
          },
          method: 'post',
          beforeSend: function beforeSend(xhr) {
            if (wlfmc_l10n.ajax_mode === 'rest_api') {
              xhr.setRequestHeader('X-WP-Nonce', wlfmc_l10n.nonce);
            } //block( elem );


            loading(elem);
          },
          complete: function complete() {
            //unblock( elem );
            unloading(elem);
          },
          success: function success(data) {
            if (!data) {
              return;
            }

            icon.removeClass('wlfmc-icon-unlock wlfmc-icon-lock');
            icon.addClass(old_privacy === 0 ? 'wlfmc-icon-lock' : 'wlfmc-icon-unlock');
            edit_popup.find('input[name="list_privacy"][value="' + new_privacy + '"]').prop('checked', 'checked');
            elem.attr('data-privacy', new_privacy);

            if (new_privacy === 0) {
              $('.wlfmc-popup-trigger[data-popup-id="share_popup_' + wishlist_id + '"]').removeAttr('style');
            } else {
              $('.wlfmc-popup-trigger[data-popup-id="share_popup_' + wishlist_id + '"]').css('display', 'none');
            }
          }
        });
        return false;
      });
      t.on('click', '.wlfmc-delete-list', function (ev) {
        ev.preventDefault();
        var elem = $(this),
            wishlist_id = elem.data('wishlist-id'),
            popup = $('#' + elem.data('popup-id')),
            row = $('ul.wlfmc-manage-lists li[data-wishlist-id="' + wishlist_id + '"],a.list-row[data-list-id="' + wishlist_id + '"]');
        $.ajax({
          url: wlfmc_l10n.multi_list_ajax_url,
          data: {
            action: wlfmc_l10n.actions.delete_list_action,
            nonce: wlfmc_l10n.ajax_nonce.delete_list_nonce,
            context: 'frontend',
            wishlist_id: wishlist_id //fragments: retrieve_fragments()

          },
          method: 'POST',
          cache: false,
          beforeSend: function beforeSend(xhr) {
            if (wlfmc_l10n.ajax_mode === 'rest_api') {
              xhr.setRequestHeader('X-WP-Nonce', wlfmc_l10n.nonce);
            } //block( elem );


            loading(elem);
          },
          complete: function complete() {
            //unblock( elem );
            unloading(elem);
          },
          success: function success(data) {
            if (!data) {
              return;
            }

            if (!data.result) {
              toastr.error(data.message);
            } else {
              popup.popup('hide');
              row.remove();
            }

            load_fragments();
            /*if (typeof data.fragments !== 'undefined') {
            	replace_fragments( data.fragments );
            }*/
          }
        });
        return false;
      });
      t.on('click', '.wlfmc-save-list', function (ev) {
        ev.preventDefault();
        var elem = $(this),
            wishlist_id = elem.data('wishlist-id'),
            row = $('ul.wlfmc-manage-lists li[data-wishlist-id="' + wishlist_id + '"],a.list-row[data-list-id="' + wishlist_id + '"], .wlfmc-wishlist-table-header'),
            popup = $('#' + elem.data('popup-id')),
            privacy = parseInt(popup.find('input[name="list_privacy"]:checked').val()),
            name = popup.find('input[name="list_name"]').val(),
            desc = popup.find('textarea[name="list_descriptions"]').val(),
            iconPrivacy = row.find('.wlfmc-toggle-privacy i');
        $.ajax({
          url: wlfmc_l10n.multi_list_ajax_url,
          data: {
            action: wlfmc_l10n.actions.update_list_action,
            nonce: wlfmc_l10n.ajax_nonce.update_list_nonce,
            context: 'frontend',
            wishlist_id: wishlist_id,
            list_privacy: privacy,
            list_name: name,
            list_descriptions: desc
          },
          method: 'POST',
          cache: false,
          beforeSend: function beforeSend(xhr) {
            if (wlfmc_l10n.ajax_mode === 'rest_api') {
              xhr.setRequestHeader('X-WP-Nonce', wlfmc_l10n.nonce);
            } //block( elem );


            loading(elem);
          },
          complete: function complete() {
            //unblock( elem );
            unloading(elem);
          },
          success: function success(data) {
            if (!data) {
              return;
            }

            if (!data.result) {
              toastr.error(data.message);
            } else {
              popup.popup('hide');
              row.find('.list-name').text(name);
              row.find('.list-desc').text(desc);
              row.find('.wlfmc-toggle-privacy').attr('data-privacy', privacy);
              iconPrivacy.removeClass('wlfmc-icon-unlock wlfmc-icon-lock');
              iconPrivacy.addClass(privacy === 0 ? 'wlfmc-icon-unlock' : 'wlfmc-icon-lock');

              if (privacy === 0) {
                $('.wlfmc-popup-trigger[data-popup-id="share_popup_' + wishlist_id + '"]').removeAttr('style');
              } else {
                $('.wlfmc-popup-trigger[data-popup-id="share_popup_' + wishlist_id + '"]').css('display', 'none');
              }
            }
          }
        });
        return false;
      });
      t.on('click', '.wlfmc-add-list', function (ev) {
        ev.preventDefault();
        var elem = $(this),
            popup = $('#new_list_popup'),
            privacy = parseInt(popup.find('input[name="list_privacy"]:checked').val()),
            name = popup.find('input[name="list_name"]').val(),
            desc = popup.find('textarea[name="list_descriptions"]').val();

        if ('' === $.trim(name)) {
          toastr.error(wlfmc_l10n.labels.multi_list_list_name_required);
          return false;
        }

        $.ajax({
          url: wlfmc_l10n.multi_list_ajax_url,
          data: {
            action: wlfmc_l10n.actions.create_new_list_action,
            nonce: wlfmc_l10n.ajax_nonce.create_new_list_nonce,
            context: 'frontend',
            list_privacy: privacy,
            list_name: name,
            list_descriptions: desc,
            fragments: retrieve_fragments('list_counter')
          },
          method: 'POST',
          cache: false,
          beforeSend: function beforeSend(xhr) {
            if (wlfmc_l10n.ajax_mode === 'rest_api') {
              xhr.setRequestHeader('X-WP-Nonce', wlfmc_l10n.nonce);
            } //block( elem );


            loading(elem);
          },
          complete: function complete() {
            //unblock( elem );
            unloading(elem);
          },
          success: function success(data) {
            if (!data) {
              return;
            }

            if (!data.result) {
              toastr.error(data.message);
            } else {
              popup.find('form')[0].reset();
              popup.popup('hide');
              var template = wp.template('wlfmc-list-item');
              var html = template(data);

              if ($('#move_popup').length > 0) {
                $('#move_popup ul.list').append(html);
                $('#move_popup ul.list').find('input.list-item-checkbox[value="' + data.wishlist_id + '"]').prop('checked', 'checked').trigger('change');
                $('#move_popup .list-empty').hide();
                $('#move_popup .no-results-row').hide();
                $('#move_popup .wlfmc-search-lists').attr('style', '');
                $('#move_popup .wlfmc_move_to_list').attr('style', '');
              } else if ($('#copy_popup').length > 0) {
                $('#copy_popup ul.list').append(html);
                $('#copy_popup ul.list').find('input.list-item-checkbox[value="' + data.wishlist_id + '"]').prop('checked', 'checked').trigger('change');
                $('#copy_popup .list-empty').hide();
                $('#copy_popup .no-results-row').hide();
                $('#copy_popup .wlfmc-search-lists').attr('style', '');
                $('#copy_popup .wlfmc_copy_to_list').attr('style', '');
              } else {
                $('#add_to_list_popup ul.list').append(html);
                $('#add_to_list_popup ul.list').find('input.list-item-checkbox[value="' + data.wishlist_id + '"]').prop('checked', 'checked').trigger('change');
                $('#add_to_list_popup .list-empty').hide();
                $('#add_to_list_popup .no-results-row').hide();
                $('#add_to_list_popup .wlfmc-search-lists').attr('style', '');
                $('#add_to_list_popup .wlfmc_add_to_multi_list').attr('style', '');
                $('#add_to_list_popup .wlfmc-manage-btn').attr('style', '');
              }

              init_popup_checkbox_handling();

              if ($('.wlfmc-lists').length > 0) {
                block($('.wlfmc-lists'));
                load_fragments();
              } else if (typeof data.fragments !== 'undefined') {
                replace_fragments(data.fragments);
              }
            }
          }
        });
        return false;
      });
      t.on('wlfmc_show_variation', function (ev, data) {
        var t = $(ev.target),
            product_id = data.product_id,
            variation_id = data.variation_id,
            targets = $('.wlfmc-add-to-multi-list [data-parent-product-id="' + product_id + '"]'),
            enable_outofstock = targets.closest('.wlfmc-add-to-multi-list').data('enable-outofstock');

        if (!product_id || !variation_id || !targets.length) {
          return;
        }

        if (!enable_outofstock && !data.is_in_stock) {
          targets.closest('.wlfmc-add-to-multi-list').addClass('hide');
        } else {
          targets.closest('.wlfmc-add-to-multi-list').removeClass('hide');
        }

        targets.each(function () {
          var t = $(this);
          t.attr('data-parent-product-id', product_id);
          t.attr('data-product-id', variation_id);
        }); //$( '.wlfmc-popup-trigger[data-popup-id="add_to_list_popup"]' ).attr( 'data-parent-product-id', product_id ).attr( 'data-product-id', variation_id );

        $('.wlfmc-popup-trigger[data-popup-id="add_to_list_popup"][data-parent-product-id="' + product_id + '"]').attr('data-product-id', variation_id);
      });
      t.on('reset_data', function (ev) {
        var t = $(ev.target),
            product_id = t.data('product_id'),
            targets = $('.wlfmc-add-to-multi-list [data-parent-product-id="' + product_id + '"]');

        if (!product_id || !targets.length) {
          return;
        }

        targets.closest('.wlfmc-add-to-multi-list').removeClass('hide');
        targets.each(function () {
          var t = $(this);
          t.attr('data-parent-product-id', product_id);
          t.attr('data-product-id', product_id);
        }); //$( '.wlfmc-popup-trigger[data-popup-id="add_to_list_popup"]' ).attr( 'data-parent-product-id', product_id ).attr( 'data-product-id', product_id );

        $('.wlfmc-popup-trigger[data-popup-id="add_to_list_popup"][data-parent-product-id="' + product_id + '"]').attr('data-product-id', product_id);
      });
      t.on('click', '.wlfmc-popup-trigger[data-popup-id="move_popup"]', function (ev) {
        var t = $(this),
            popup = $('#move_popup'),
            item_ids = t.data('item-id');
        popup.find('.wlfmc_move_to_list').attr('data-item-ids', item_ids);
        return false;
      });
      t.on('click', '.wlfmc-popup-trigger[data-popup-id="copy_popup"]', function (ev) {
        var t = $(this),
            popup = $('#copy_popup'),
            item_ids = t.data('item-id');
        popup.find('.wlfmc_copy_to_list').attr('data-item-ids', item_ids);
        return false;
      });
      t.on('click', '.wlfmc-popup-trigger[data-popup-id="add_to_list_popup"]', function (ev) {
        var t = $(this),
            product_id = t.attr('data-product-id'),
            parent_product_id = t.attr('data-parent-product-id'),
            product_type = t.attr('data-product-type'),
            popup = $('#add_to_list_popup'),
            data = {
          action: wlfmc_l10n.actions.load_lists_action,
          nonce: wlfmc_l10n.ajax_nonce.load_lists_nonce,
          context: 'frontend',
          product_id: product_id
        };
        popup.find('.wlfmc_add_to_multi_list').attr('data-product-type', product_type).attr('data-product-id', product_id).attr('data-parent-product-id', parent_product_id);

        if (!is_cookie_enabled()) {
          ev.preventDefault();
          window.alert(wlfmc_l10n.labels.cookie_disabled);
          return;
        }

        $.ajax({
          url: wlfmc_l10n.multi_list_ajax_url,
          data: data,
          type: 'POST',
          beforeSend: function beforeSend(xhr) {
            if (wlfmc_l10n.ajax_mode === 'rest_api') {
              xhr.setRequestHeader('X-WP-Nonce', wlfmc_l10n.nonce);
            }

            block(popup.find('.wlfmc-add-to-list-container'));
          },
          complete: function complete() {
            unblock(popup.find('.wlfmc-add-to-list-container'));
          },
          success: function success(response) {
            var response_result = response.result,
                response_message = response.message,
                data = response.data,
                product_lists = response.product_lists,
                show_toast = true;

            if (response_result === 'true' && 0 < data.length) {
              var template = wp.template('wlfmc-list-item');
              var html = '';

              for (var i = 0; i < data.length; i++) {
                html += template(data[i]);
              }

              popup.find('.wlfmc-search-lists').show();
              popup.find('.list').html(html);
              popup.find('.wlfmc-number').text(product_lists.length);
              popup.find('.list-empty').hide();
              popup.find('.wlfmc_add_to_multi_list').attr('data-list-ids', JSON.stringify(product_lists)).attr('style', '');
              popup.find('.wlfmc-manage-btn').attr('style', '');
            } else {
              popup.find('.wlfmc-manage-btn').css('display', 'none !important');
              popup.find('.wlfmc-search-lists').hide();
              popup.find('.list').html('');
              popup.find('.list-empty').show();
              popup.find('.wlfmc-number').text(0);
              popup.find('.wlfmc_add_to_multi_list').css('display', 'none !important');
            }

            if (show_toast && '' !== $.trim(response.message) && response_result !== 'true') {
              toastr.error(response_message);
              popup.popup('hide');
            }
          }
        });
        return false;
      });
      t.on('click', '.wlfmc_add_to_multi_list', function (ev) {
        var t = $(this),
            product_id = t.attr('data-product-id'),
            parent_product_id = t.attr('data-parent-product-id'),
            filtered_data = null,
            popup = $('#add_to_list_popup'),
            current_lists = JSON.parse(t.attr('data-list-ids')),
            wishlist_ids = popup.find('input.list-item-checkbox:checked').map(function () {
          return this.value;
        }).get(),
            data = {
          action: wlfmc_l10n.actions.add_product_to_list_action,
          nonce: wlfmc_l10n.ajax_nonce.add_product_to_list_nonce,
          context: 'frontend',
          add_to_list: product_id,
          product_type: t.attr('data-product-type'),
          current_lists: current_lists,
          wishlist_ids: wishlist_ids //fragments: retrieve_fragments()

        }; // allow third party code to filter data.

        if (filtered_data === $(document).triggerHandler('wlfmc_add_to_multi_list_data', [t, data])) {
          data = filtered_data;
        }

        var current_product_form;

        if ($('form.cart[method=post][data-product_id="' + parent_product_id + '"], form.vtajaxform[method=post][data-product_id="' + parent_product_id + '"]').length) {
          current_product_form = $('form.cart[method=post][data-product_id="' + parent_product_id + '"], form.vtajaxform[method=post][data-product_id="' + parent_product_id + '"]').eq(0);
        } else if ($(this).closest('form.cart[method=post], form.vtajaxform[method=post]').length) {
          current_product_form = $(this).closest('form.cart[method=post], form.vtajaxform[method=post]').eq(0);
        } else if ($('#product-' + parent_product_id + ' form.cart[method=post],#product-' + parent_product_id + ' form.vtajaxform[method=post]').length) {
          current_product_form = $('#product-' + parent_product_id + ' form.cart[method=post],#product-' + parent_product_id + ' form.vtajaxform[method=post]').eq(0);
        } else if ($('form.cart[method=post] button[name="add-to-cart"][value="' + parent_product_id + '"],form.vtajaxform[method=post] button[name="add-to-cart"][value="' + parent_product_id + '"]').length) {
          var button = $('form.cart[method=post] button[name="add-to-cart"][value="' + parent_product_id + '"],form.vtajaxform[method=post] button[name="add-to-cart"][value="' + parent_product_id + '"]');
          current_product_form = button.closest('form').eq(0);
        }

        var formData = new FormData();

        if (typeof current_product_form !== 'undefined' && current_product_form.length > 0) {
          /*current_product_form.find( "input[name='add-to-cart']" ).attr( "disabled",true );
          current_product_form.find( "input[name='add-to-cart']" ).removeAttr( "disabled" );*/
          formData = new FormData(current_product_form.get(0));
          formData["delete"]('add-to-cart');
        } else {
          var add_to_cart_link = t.closest('.product.post-' + parent_product_id).find('.add_to_cart_button');

          if (add_to_cart_link.length) {
            data.quantity = add_to_cart_link.attr('data-quantity');
          }
        }

        $.each(data, function (key, valueObj) {
          formData.append(key, _typeof2(valueObj) === 'object' ? JSON.stringify(valueObj) : valueObj);
        });
        ev.preventDefault();
        jQuery(document.body).trigger('wlfmc_adding_to_multi_list');

        if (!is_cookie_enabled()) {
          window.alert(wlfmc_l10n.labels.cookie_disabled);
          return;
        }

        $.ajax({
          url: wlfmc_l10n.multi_list_ajax_url,
          data: formData,
          type: 'POST',
          //dataType: 'json',
          contentType: false,
          processData: false,
          cache: false,
          beforeSend: function beforeSend(xhr) {
            if (wlfmc_l10n.ajax_mode === 'rest_api') {
              xhr.setRequestHeader('X-WP-Nonce', wlfmc_l10n.nonce);
            } //block( t );


            loading(t);
          },
          complete: function complete() {
            //unblock( t );
            unloading(t);
          },
          success: function success(response) {
            var response_result = response.result,
                response_message = response.message,
                show_toast = true;

            if (response_result === 'true') {
              load_fragments();
              popup.popup('hide');

              if (show_toast && '' !== $.trim(wlfmc_l10n.labels.multi_list_saved_text)) {
                toastr.success(wlfmc_l10n.labels.multi_list_saved_text);
              }

              if (response.last_list_id && response.last_list_id !== 'false') {
                load_automations(product_id, response.last_list_id, response.customer_id, 'lists');
              }
              /*if (typeof response.fragments !== 'undefined') {
              	replace_fragments( response.fragments );
              }*/

            }

            if (show_toast && '' !== $.trim(response.message) && response_result !== 'true') {
              toastr.error(response_message);
            }
          }
        });
        return false;
      });
      t.on('click', '.wlfmc_move_to_list', function (ev) {
        var t = $(this),
            item_ids = t.attr('data-item-ids'),
            filtered_data = null,
            popup = $('#move_popup'),
            current_list = t.attr('data-wishlist-id'),
            wishlist_id = popup.find('input.list-item-checkbox:checked').val(),
            data = {
          action: wlfmc_l10n.actions.move_to_another_list,
          nonce: wlfmc_l10n.ajax_nonce.move_to_another_list_nonce,
          context: 'frontend',
          item_ids: item_ids,
          current_list: current_list,
          destination_wishlist_id: wishlist_id
        };

        if (data.current_list === '' || data.destination_wishlist_id === '' || data.item_ids === '') {
          return;
        } // allow third party code to filter data.


        if (filtered_data === $(document).triggerHandler('wlfmc_move_to_list_data', [t, data])) {
          data = filtered_data;
        }

        ev.preventDefault();
        $.ajax({
          url: wlfmc_l10n.admin_url,
          data: data,
          dataType: 'json',
          method: 'post',
          beforeSend: function beforeSend(xhr) {
            //block( t );
            loading(t);
          },
          success: function success(response) {
            var response_result = response.result,
                show_toast = true;
            var response_message = wlfmc_l10n.labels.multi_list_successfully_move_message.replace('{wishlist_name}', response.wishlist_name);

            if (isTrue(response_result)) {
              load_fragments('', function (show_toast, response_message) {
                $('#move_popup_background').remove();
                $('#move_popup_wrapper').remove();

                if (show_toast && '' !== $.trim(response_message)) {
                  toastr.success(response_message);
                }
              }, [show_toast, response_message]);
            }

            if (!isTrue(response_result)) {
              popup.popup('hide'); //unblock( t );

              unloading(t);
            }
          }
        });
        return false;
      });
      t.on('click', '.wlfmc_copy_to_list', function (ev) {
        var t = $(this),
            item_ids = t.attr('data-item-ids'),
            filtered_data = null,
            popup = $('#copy_popup'),
            current_list = t.attr('data-wishlist-id'),
            wishlist_id = popup.find('input.list-item-checkbox:checked').val(),
            data = {
          action: wlfmc_l10n.actions.copy_to_another_list,
          nonce: wlfmc_l10n.ajax_nonce.copy_to_another_list_nonce,
          context: 'frontend',
          item_ids: item_ids,
          current_list: current_list,
          wishlist: wishlist_id
        };

        if (data.current_list === '' || data.destination_wishlist_id === '' || data.item_ids === '') {
          return;
        } // allow third party code to filter data.


        if (filtered_data === $(document).triggerHandler('wlfmc_copy_to_list_data', [t, data])) {
          data = filtered_data;
        }

        ev.preventDefault();
        $.ajax({
          url: wlfmc_l10n.admin_url,
          data: data,
          dataType: 'json',
          method: 'post',
          beforeSend: function beforeSend(xhr) {
            //block( t );
            loading(t);
          },
          success: function success(response) {
            var response_result = response.result,
                show_toast = true;
            var response_message = wlfmc_l10n.labels.successfully_copy_message.replace('{wishlist_name}', response.wishlist_name);

            if (isTrue(response_result)) {
              load_fragments('', function (show_toast, response_message) {
                $('#copy_popup_background').remove();
                $('#copy_popup_wrapper').remove();

                if (show_toast && '' !== $.trim(response_message)) {
                  toastr.success(response_message);
                }
              }, [show_toast, response_message]);
            }

            if (!isTrue(response_result)) {
              popup.popup('hide'); //unblock( t );

              unloading(t);
            }
          }
        });
        return false;
      });
      t.on('click', '.wlfmc_copy_to_default_list', function (ev) {
        var t = $(this),
            item_ids = t.attr('data-item-id'),
            filtered_data = null,
            current_list = t.attr('data-wishlist-id'),
            data = {
          action: wlfmc_l10n.actions.copy_to_another_list,
          nonce: wlfmc_l10n.ajax_nonce.copy_to_another_list_nonce,
          context: 'frontend',
          item_ids: item_ids,
          wishlist: 'default',
          current_list: current_list
        };

        if (data.current_list === '' || data.item_ids === '') {
          return;
        } // allow third party code to filter data.


        if (filtered_data === $(document).triggerHandler('wlfmc_copy_to_default_list_data', [t, data])) {
          data = filtered_data;
        }

        ev.preventDefault();
        $.ajax({
          url: wlfmc_l10n.admin_url,
          data: data,
          dataType: 'json',
          method: 'post',
          beforeSend: function beforeSend(xhr) {
            //block( t );
            loading(t);
          },
          success: function success(response) {
            var response_result = response.result,
                show_toast = true;
            var response_message = wlfmc_l10n.labels.successfully_copy_message.replace('{wishlist_name}', response.wishlist_name);

            if (isTrue(response_result)) {
              load_fragments('list_counter');

              if (show_toast && '' !== $.trim(response_message)) {
                toastr.success(response_message);
              }
            }

            if (!isTrue(response_result)) {
              unloading(t);
            }
          }
        });
        return false;
      });
      t.on('click', '.wlfmc-multi-list-btn-login-need', function (ev) {
        ev.preventDefault();
        toastr.error(wlfmc_l10n.labels.multi_list_login_need);
        return false;
      });
      /* === WISHLIST === */

      t.on('click', '.wlfmc_add_to_wishlist', function (ev) {
        ev.preventDefault();

        if (product_adding && Array.isArray(product_in_list) && !product_in_list.length) {
          toastr.error(wlfmc_l10n.labels.product_adding);
          return;
        }

        var t = $(this),
            product_id = t.attr('data-product-id'),
            parent_product_id = t.attr('data-parent-product-id'),
            el_wrap = t.closest('.wlfmc-add-to-wishlist-' + product_id),
            filtered_data = null,
            data = {
          action: wlfmc_l10n.actions.add_to_wishlist_action,
          nonce: wlfmc_l10n.ajax_nonce.add_to_wishlist_nonce,
          context: 'frontend',
          add_to_wishlist: product_id,
          product_type: t.attr('data-product-type') // wishlist_id: t.attr( 'data-wishlist-id' ),
          // fragments: retrieve_fragments( product_id )

        }; // allow third party code to filter data.

        if (filtered_data === $(document).triggerHandler('wlfmc_add_to_wishlist_data', [t, data])) {
          data = filtered_data;
        }

        var current_product_form;

        if ($('form.cart[method=post][data-product_id="' + parent_product_id + '"], form.vtajaxform[method=post][data-product_id="' + parent_product_id + '"]').length) {
          current_product_form = $('form.cart[method=post][data-product_id="' + parent_product_id + '"], form.vtajaxform[method=post][data-product_id="' + parent_product_id + '"]').eq(0);
        } else if ($(this).closest('form.cart[method=post], form.vtajaxform[method=post]').length) {
          current_product_form = $(this).closest('form.cart[method=post], form.vtajaxform[method=post]').eq(0);
        } else if ($('#product-' + parent_product_id + ' form.cart[method=post],#product-' + parent_product_id + ' form.vtajaxform[method=post]').length) {
          current_product_form = $('#product-' + parent_product_id + ' form.cart[method=post],#product-' + parent_product_id + ' form.vtajaxform[method=post]').eq(0);
        } else if ($('form.cart[method=post] button[name="add-to-cart"][value="' + parent_product_id + '"],form.vtajaxform[method=post] button[name="add-to-cart"][value="' + parent_product_id + '"]').length) {
          var button = $('form.cart[method=post] button[name="add-to-cart"][value="' + parent_product_id + '"],form.vtajaxform[method=post] button[name="add-to-cart"][value="' + parent_product_id + '"]');
          current_product_form = button.closest('form').eq(0);
        }

        var formData = new FormData();

        if (typeof current_product_form !== 'undefined' && current_product_form.length > 0) {
          /*current_product_form.find( "input[name='add-to-cart']" ).attr( "disabled",true );
          current_product_form.find( "input[name='add-to-cart']" ).removeAttr( "disabled" );*/
          formData = new FormData(current_product_form.get(0));
          /*$.each(
          	current_product_form,
          	function( index, element ) {
          		$( element ).find( 'div.composite_component' ).not( ':visible' ).each(
          			function() {
          				var id = $( this ).attr( 'data-item_id' );
          				formData.append( 'wccp_component_selection_nil[' + id + ']' , '1' );
          			}
          		);
          	}
          );*/

          formData["delete"]('add-to-cart');
        } else {
          var add_to_cart_link = t.closest('.product.post-' + parent_product_id).find('.add_to_cart_button');

          if (add_to_cart_link.length) {
            data.quantity = add_to_cart_link.attr('data-quantity');
          }
        }

        $.each(data, function (key, valueObj) {
          formData.append(key, _typeof2(valueObj) === 'object' ? JSON.stringify(valueObj) : valueObj);
        });
        jQuery(document.body).trigger('wlfmc_adding_to_wishlist');

        if (!is_cookie_enabled()) {
          product_adding = false;
          window.alert(wlfmc_l10n.labels.cookie_disabled);
          return;
        }

        $.ajax({
          url: wlfmc_l10n.ajax_url,
          data: formData,
          type: 'POST',
          //dataType: 'json',
          contentType: false,
          processData: false,
          cache: false,
          beforeSend: function beforeSend(xhr) {
            if (wlfmc_l10n.ajax_mode === 'rest_api') {
              xhr.setRequestHeader('X-WP-Nonce', wlfmc_l10n.nonce);
            }

            product_adding = true; //block( t );

            loading(t);
          },
          complete: function complete() {
            product_adding = false; //unblock( t );

            unloading(t);
          },
          success: function success(response) {
            var response_result = response.result,
                response_message = response.message,
                show_toast = true;

            if (response_result === 'true' || response_result === 'exists') {
              load_fragments();

              if (response.item_id) {
                if (typeof product_in_list !== 'undefined' && product_in_list !== null) {
                  product_in_list.push({
                    wishlist_id: response.wishlist_id,
                    item_id: response.item_id,
                    product_id: parseInt(product_id)
                  });
                  set_products_hash(JSON.stringify(product_in_list));
                }
              }

              var popup_id = el_wrap.attr('data-popup-id');

              if (popup_id) {
                show_toast = false;
                var elem = $('#' + popup_id);
                var defaultOptions = {
                  absolute: false,
                  color: '#333',
                  transition: 'all 0.3s',
                  horizontal: elem.data('horizontal'),
                  vertical: elem.data('vertical')
                };
                elem.popup(defaultOptions);
                elem.popup('show');
              }

              if (show_toast && '' !== $.trim(wlfmc_l10n.labels.product_added_text) && response_result === 'true') {
                toastr.success(wlfmc_l10n.labels.product_added_text);
              }

              if (response_result === 'true') {
                load_automations(product_id, response.wishlist_id, response.customer_id, 'wishlist');
              }
            }

            if (response_result === 'true' && wlfmc_l10n.click_behavior === 'add-redirect') {
              window.location.href = response.wishlist_url;
            }

            if (show_toast && '' !== $.trim(response.message) && response_result !== 'true') {
              toastr.error(response_message);
            }

            init_handling_after_ajax();
            $('body').trigger('wlfmc_added_to_wishlist', [t, el_wrap]);
          }
        });
        return false;
      });
      t.on('click', '.wlfmc_ajax_add_to_cart:not(.disabled)', function (ev) {
        var t = $(this),
            item_id = t.attr('data-item_id'),
            wishlist_id = t.attr('data-wishlist_id'),
            data = {
          action: wlfmc_l10n.actions.add_to_cart_action,
          nonce: wlfmc_l10n.ajax_nonce.add_to_cart_nonce,
          context: 'frontend',
          lid: item_id,
          wid: wishlist_id
        };
        ev.preventDefault();
        t.removeClass('added');
        t.addClass('loading'); // Allow 3rd parties to validate and quit early.

        if (false === $(document.body).triggerHandler('should_send_ajax_request.adding_to_cart', [t])) {
          $(document.body).trigger('ajax_request_not_sent.adding_to_cart', [false, false, t]);
          return true;
        }

        $(document.body).trigger('adding_to_cart', [t, data]);
        $.ajax({
          url: wlfmc_l10n.admin_url,
          data: data,
          type: 'POST',
          dataType: 'json',
          success: function success(response) {
            if (!response) {
              return;
            }

            if (response.error) {
              if (response.product_url) {
                window.location = response.product_url;
                return;
              }

              if ('' !== wlfmc_l10n.labels.failed_add_to_cart_message) {
                toastr.error(wlfmc_l10n.labels.failed_add_to_cart_message);
              }
            } else {
              // Redirect to cart option.
              if (isTrue(wc_add_to_cart_params.cart_redirect_after_add)) {
                window.location = wc_add_to_cart_params.cart_url;
                return;
              }

              $(document.body).trigger('wc_fragment_refresh'); // Trigger event so themes can refresh other areas.

              $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, t]);

              if ('' !== wlfmc_l10n.labels.added_to_cart_message) {
                toastr.success(wlfmc_l10n.labels.added_to_cart_message);
              }
            }

            if (response.message && '' !== response.message) {
              $(document.body).trigger('add_to_cart_message', [response.message, t]);
            }
          }
        });
        return false;
      });
      t.on('click', '.wlfmc-btn-login-need', function (ev) {
        ev.preventDefault();
        toastr.error(wlfmc_l10n.labels.login_need);
        return false;
      });
      t.on('click', '.wlfmc_already_in_wishlist', function (ev) {
        ev.preventDefault();
        toastr.error(wlfmc_l10n.labels.already_in_wishlist_text);
        return false;
      });
      t.on('click', '.wlfmc-wishlist-table .remove_from_wishlist', function (ev) {
        var t = $(this);
        ev.preventDefault();
        var table = t.parents('.wlfmc-wishlist-items-wrapper'),
            row = t.parents('[data-row-id]'),
            data_row_id = row.data('row-id'),
            wishlist_id = table.data('id'),
            wishlist_token = table.data('token'),
            data = {
          action: wlfmc_l10n.actions.remove_from_wishlist_action,
          nonce: wlfmc_l10n.ajax_nonce.remove_from_wishlist_nonce,
          context: 'frontend',
          remove_from_wishlist: data_row_id,
          wishlist_id: wishlist_id,
          wishlist_token: wishlist_token //fragments: retrieve_fragments( data_row_id )

        };
        $.ajax({
          url: wlfmc_l10n.ajax_url,
          data: data,
          method: 'post',
          beforeSend: function beforeSend(xhr) {
            if (wlfmc_l10n.ajax_mode === 'rest_api') {
              xhr.setRequestHeader('X-WP-Nonce', wlfmc_l10n.nonce);
            }

            block(row);
          },
          complete: function complete() {
            unblock(row);
          },
          success: function success(data) {
            var i;
            load_fragments();
            /*if (typeof data.fragments !== 'undefined') {
            	replace_fragments( data.fragments );
            }*/

            if (isTrue(data.result)) {
              if (typeof product_in_list !== 'undefined' && product_in_list !== null) {
                var product_count = product_in_list.length;

                for (i = 0; i <= product_count - 1; i++) {
                  if (typeof product_in_list[i] !== 'undefined' && product_in_list[i].wishlist_id == wishlist_id && product_in_list[i].product_id == data_row_id) {
                    product_in_list.splice(i, 1);
                    $('body').trigger('wlfmc_removed_from_wishlist', [t, row, data]);
                    break;
                  }
                }

                set_products_hash(JSON.stringify(product_in_list));
              }

              if (typeof product_in_waitlist !== 'undefined' && product_in_waitlist !== null) {
                var _product_count = product_in_waitlist.length;

                for (i = 0; i <= _product_count - 1; i++) {
                  if (typeof product_in_waitlist[i] !== 'undefined' && product_in_waitlist[i].wishlist_id == wishlist_id && product_in_waitlist[i].product_id == data_row_id) {
                    product_in_waitlist.splice(i, 1);
                    $('body').trigger('wlfmc_removed_from_waitlist', [t, row, data]);
                    break;
                  }
                }

                set_waitlist_hash(JSON.stringify(product_in_waitlist));
              }
            }

            init_handling_after_ajax();
          }
        });
        return false;
      });
      t.on('click touchend', '.wlfmc-products-counter-wishlist .remove_from_wishlist,.wlfmc-products-counter-waitlist .remove_from_wishlist', function (ev) {
        var t = $(this);
        ev.preventDefault();
        var table = t.parents('.wlfmc-wishlist-items-wrapper'),
            row = t.parents('[data-row-id]'),
            data_row_id = row.data('row-id'),
            data_item_id = row.data('item-id'),
            wishlist_id = table.data('id'),
            wishlist_token = table.data('token'),
            data = {
          action: wlfmc_l10n.actions.remove_from_wishlist_action,
          nonce: wlfmc_l10n.ajax_nonce.remove_from_wishlist_nonce,
          context: 'frontend',
          remove_from_wishlist: data_row_id,
          wishlist_id: wishlist_id,
          wishlist_token: wishlist_token //fragments: retrieve_fragments( data_row_id )

        };
        $.ajax({
          url: wlfmc_l10n.ajax_url,
          data: data,
          method: 'post',
          beforeSend: function beforeSend(xhr) {
            if (wlfmc_l10n.ajax_mode === 'rest_api') {
              xhr.setRequestHeader('X-WP-Nonce', wlfmc_l10n.nonce);
            } //block( table );


            loading(t);
          },
          complete: function complete() {
            //unblock( table );
            unloading(t);
          },
          success: function success(data) {
            if (isTrue(data.result)) {
              if (typeof product_in_list !== 'undefined' && product_in_list !== null) {
                var product_count = product_in_list.length;

                for (var i = 0; i <= product_count - 1; i++) {
                  if (typeof product_in_list[i] !== 'undefined' && product_in_list[i].wishlist_id === wishlist_id && product_in_list[i].product_id === data_row_id) {
                    product_in_list.splice(i, 1);
                    $('body').trigger('wlfmc_removed_from_wishlist', [t, row, data]);
                    break;
                  }
                }

                set_products_hash(JSON.stringify(product_in_list));
              }

              if (typeof product_in_waitlist !== 'undefined' && product_in_waitlist !== null) {
                var product_count = product_in_waitlist.length;

                for (var i = 0; i <= product_count - 1; i++) {
                  if (typeof product_in_waitlist[i] !== 'undefined' && product_in_waitlist[i].wishlist_id === wishlist_id && product_in_waitlist[i].product_id === data_row_id) {
                    product_in_waitlist.splice(i, 1);
                    $('body').trigger('wlfmc_removed_from_waitlist', [t, row, data]);
                    break;
                  }
                }

                set_waitlist_hash(JSON.stringify(product_in_waitlist));
              }

              if (t.closest('.wlfmc-products-counter-wrapper').length > 0) {
                $('.wlfmc-products-counter-wrapper').find('[data-item-id="' + data_item_id + '"]').remove();
                $('.wlfmc-wishlist-form').find('[data-item-id="' + data_item_id + '"]').remove();
                $('.wlfmc-products-counter-wrapper  .products-counter-number').text(data.count);
                $('.wlfmc-products-counter-wishlist .total-products .wlfmc-total-count').text(data.count);
                $('.wlfmc-add-to-wishlist.wlfmc-add-to-wishlist-' + data_row_id).removeClass('exists');
              }

              if (t.closest('.wlfmc-waitlist-counter-wrapper').length > 0) {
                $('.wlfmc-waitlist-counter-wrapper').find('[data-item-id="' + data_item_id + '"]').remove();
                $('.wlfmc-wishlist-form').find('[data-item-id="' + data_item_id + '"]').remove();
                $('.wlfmc-waitlist-counter-wrapper  .products-counter-number').text(data.count);
                $('.wlfmc-products-counter-waitlist .total-products .wlfmc-total-count').text(data.count);
                $('.wlfmc-add-to-waitlist.wlfmc-add-to-waitlist-' + data_row_id).removeClass('exists');
              }

              if (data.count < 1 || !table.find('[data-row-id]').length) {
                load_fragments();
              }
              /*if ((data.count < 1 || ! table.find( '[data-row-id]' ).length) && typeof data.fragments !== 'undefined') {
              	replace_fragments( data.fragments );
              }*/

            }

            init_handling_after_ajax();
          }
        });
        return false;
      });
      t.on('adding_to_cart', 'body', function (ev, button, data) {
        if (typeof button !== 'undefined' && typeof data !== 'undefined' && button.closest('.wlfmc-wishlist-table,.wlfmc-save-for-later-table').length) {
          data.wishlist_id = button.closest('.wlfmc-wishlist-table,.wlfmc-save-for-later-table').data('id');
          data.wishlist_type = button.closest('.wlfmc-wishlist-table,.wlfmc-save-for-later-table').data('wishlist-type');
          data.customer_id = button.closest('.wlfmc-wishlist-table,.wlfmc-save-for-later-table').data('customer-id');
          data.is_owner = button.closest('.wlfmc-wishlist-table,.wlfmc-save-for-later-table').data('is-owner');
          typeof wc_add_to_cart_params !== 'undefined' && (wc_add_to_cart_params.cart_redirect_after_add = wlfmc_l10n.redirect_to_cart);
          /*let product_meta                            = button.data( 'wlfmc_product_meta' );
          if (product_meta) {
          	$.each(
          		product_meta,
          		function (k,value) {
          			data[k] = value;
          		}
          	);
          	data.wlfmc_product_meta = true;
          }*/
        }
      });
      t.on('added_to_cart', 'body', function (ev, fragments, carthash, button) {
        if (typeof button !== 'undefined' && button.closest('.wlfmc-wishlist-table').length) {
          typeof wc_add_to_cart_params !== 'undefined' && (wc_add_to_cart_params.cart_redirect_after_add = cart_redirect_after_add);
          var tr = button.closest('[data-row-id]'),
              table = tr.closest('.wlfmc-wishlist-fragment'),
              options = table.data('fragment-options'),
              data_row_id = tr.data('row-id'),
              wishlist_id = table.find('.wlfmc-wishlist-table').data('id'),
              wishlist_token = table.find('.wlfmc-wishlist-table').data('token');
          button.removeClass('added');
          tr.find('.added_to_cart').remove();

          if (wlfmc_l10n.remove_from_wishlist_after_add_to_cart && options.is_user_owner) {
            if (typeof product_in_list !== 'undefined' && product_in_list !== null) {
              var product_count = product_in_list.length;

              for (var i = 0; i <= product_count - 1; i++) {
                if (typeof product_in_list[i] !== 'undefined' && product_in_list[i].wishlist_id == wishlist_id && product_in_list[i].product_id == data_row_id) {
                  product_in_list.splice(i, 1);
                }
              }

              set_products_hash(JSON.stringify(product_in_list));
            }

            $('.wlfmc-products-counter-wrapper').find('[data-row-id="' + data_row_id + '"]').remove();
            $('.wlfmc-wishlist-form').find('[data-row-id="' + data_row_id + '"]').remove();
            $('.wlfmc-products-counter-wrapper  .products-counter-number').text(product_in_list.length);
            $('.wlfmc-products-counter-wishlist .total-products .wlfmc-total-count').text(product_in_list.length);
            $('.wlfmc-add-to-wishlist.wlfmc-add-to-wishlist-' + data_row_id).removeClass('exists');

            if (!product_in_list.length || product_in_list.length === 0 || !table.find('[data-row-id]').length) {
              $('.wlfmc-wishlist-table-wrapper').empty();
              load_fragments();
            }
          }
        } else if (typeof button !== 'undefined' && button.closest('.wlfmc-save-for-later-table').length) {
          var tr = button.closest('[data-item-id]'),
              table = tr.closest('.wlfmc-wishlist-fragment'),
              options = table.data('fragment-options'),
              data_item_id = tr.data('item-id');
          button.removeClass('added');
          tr.find('.added_to_cart').remove();

          if (options.is_user_owner) {
            $('.wlfmc-save-for-later-form').find('[data-item-id="' + data_item_id + '"]').remove();

            if (!$('.wlfmc-save-for-later-items-wrapper .save-for-later-items-wrapper tr').length) {
              $('.wlfmc-save-for-later-table-wrapper').empty();
            }
          }
        }
      });
      t.on('add_to_cart_message', 'body', function (e, message, t) {
        var wrapper = $('.woocommerce-notices-wrapper .woocommerce-error,.woocommerce-notices-wrapper .woocommerce-message');
        t.removeClass('loading');

        if (wrapper.length === 0) {
          $('#wlfmc-wishlist-form').prepend(message);
        } else {
          wrapper.fadeOut(300, function () {
            $(this).closest('.woocommerce-notices-wrapper').replaceWith(message).fadeIn();
          });
        }
      });
      t.on('cart_page_refreshed', 'body', init_handling_after_ajax);
      t.on('click', '.wlfmc_delete_item', function () {
        var t = $(this),
            product_id = t.attr('data-product-id'),
            wishlist_id = t.attr('data-wishlist-id'),
            item_id = t.attr('data-item-id'),
            el_wrap = $('.wlfmc-add-to-wishlist-' + product_id),
            data = {
          action: wlfmc_l10n.actions.delete_item_action,
          nonce: wlfmc_l10n.ajax_nonce.delete_item_nonce,
          context: 'frontend',
          wishlist_id: wishlist_id,
          item_id: item_id //fragments: retrieve_fragments( product_id )

        };
        $.ajax({
          url: wlfmc_l10n.ajax_url,
          data: data,
          method: 'post',
          dataType: 'json',
          beforeSend: function beforeSend(xhr) {
            if (wlfmc_l10n.ajax_mode === 'rest_api') {
              xhr.setRequestHeader('X-WP-Nonce', wlfmc_l10n.nonce);
            } //block( t );


            loading(t);
          },
          complete: function complete() {
            //unblock( t );
            unloading(t);
          },
          success: function success(response) {
            var fragments = response.fragments,
                response_message = response.message;

            if ('true' === response.result) {
              el_wrap.removeClass('exists');

              if (typeof product_in_list !== 'undefined' && product_in_list !== null) {
                product_in_list = $.grep(product_in_list, function (e) {
                  return e.item_id !== parseInt(item_id);
                });
                set_products_hash(JSON.stringify(product_in_list));
              }
            }

            if (!t.closest('.wlfmc-remove-button').length && '' !== $.trim(response_message)) {
              toastr.error(response_message);
            }

            if ('true' === response.result && '' !== $.trim(wlfmc_l10n.labels.product_removed_text)) {
              toastr.error(wlfmc_l10n.labels.product_removed_text);
            }

            load_fragments();
            /*if (typeof fragments !== 'undefined') {
            	replace_fragments( fragments );
            }*/

            init_handling_after_ajax();
            $('body').trigger('wlfmc_removed_from_wishlist', [t, el_wrap, response]);
          }
        });
        return false;
      });
      t.on('change', '#bulk_add_to_cart,#bulk_add_to_cart2', function () {
        var t = $(this),
            checkboxes = t.closest('.wlfmc-wishlist-table,.wlfmc-save-for-later-table').find('[data-row-id]').find('input[type="checkbox"]:not(:disabled)');

        if (t.is(':checked')) {
          checkboxes.prop('checked', 'checked').trigger('change');
          $('#bulk_add_to_cart').prop('checked', 'checked');
          $('#bulk_add_to_cart2').prop('checked', 'checked');
        } else {
          checkboxes.prop('checked', false).trigger('change');
          $('#bulk_add_to_cart').prop('checked', false);
          $('#bulk_add_to_cart2').prop('checked', false);
        }
      });
      t.on('submit', '.wlfmc-popup-form', function () {
        return false;
      });
      t.on('found_variation', function (ev, variation) {
        var t = $(ev.target),
            product_id = t.data('product_id'),
            variation_data = variation;
        variation_data.product_id = product_id;
        $(document).trigger('wlfmc_show_variation', variation_data);
      });
      t.on('wlfmc_show_variation', function (ev, data) {
        var t = $(ev.target),
            product_id = data.product_id,
            variation_id = data.variation_id,
            targets = $('.wlfmc-add-to-wishlist [data-parent-product-id="' + product_id + '"]'),
            enable_outofstock = targets.closest('.wlfmc-add-to-wishlist').data('enable-outofstock');

        if (!product_id || !variation_id || !targets.length) {
          return;
        }

        if (!enable_outofstock && !data.is_in_stock) {
          targets.closest('.wlfmc-add-to-wishlist').addClass('hide');
        } else {
          targets.closest('.wlfmc-add-to-wishlist').removeClass('hide');
        }

        var popupId = targets.closest('.wlfmc-add-to-wishlist').attr('data-popup-id');

        if (popupId) {
          var popup = $('#' + popupId);

          if (popup.length) {
            var product_title = popup.data('product-title');
            var desc = wlfmc_l10n.labels.popup_content;
            var title = wlfmc_l10n.labels.popup_title;
            var image_size = popup.data('image-size');
            var img = popup.find('.wlfmc-popup-header img').data('src');
            var original_price = popup.find('.wlfmc-parent-product-price').html();
            var product_price = '' !== data.price_html ? data.price_html : original_price;
            desc = desc.replace('{product_price}', product_price);
            desc = desc.replace('{product_name}', product_title);
            title = title.replace('{product_price}', product_price);
            title = title.replace('{product_name}', product_title);

            if (data.image_id && 'true' == popup.data('use-featured')) {
              img = 'large' === image_size ? data.image.full_src : 'thumbnail' === image_size ? data.image.thumb_src : data.image.src;
            }

            popup.find('.wlfmc-popup-title').html(title);
            popup.find('.wlfmc-popup-desc').html(desc);
            popup.find('.wlfmc-popup-header img').attr('src', img);
          }
        }

        targets.each(function () {
          var t = $(this),
              container = t.closest('.wlfmc-add-to-wishlist');
          t.attr('data-parent-product-id', product_id);
          t.attr('data-product-id', variation_id);

          if (container.length) {
            container.removeClass(function (i, classes) {
              return classes.match(/wlfmc-add-to-wishlist-\S+/g).join(' ');
            }).addClass('wlfmc-add-to-wishlist-' + variation_id).removeClass('exists');
          }

          container.find('.wlfmc-addtowishlist a').attr('href', container.attr('data-add-url').replace("#product_id", variation_id));
          container.find('.wlfmc-removefromwishlist a').attr('href', container.attr('data-remove-url').replace("#product_id", variation_id));
          $.each(product_in_list, function (i, v) {
            if (typeof v !== 'undefined' && v.product_id && v.product_id == variation_id) {
              container.addClass('exists');
              container.find('.wlfmc_delete_item').attr('data-wishlist-id', v.wishlist_id);
              container.find('.wlfmc_delete_item').attr('data-item-id', v.item_id);
            }
          });
        });
      });
      t.on('reset_data', function (ev) {
        var t = $(ev.target),
            product_id = t.data('product_id'),
            targets = $('.wlfmc-add-to-wishlist [data-parent-product-id="' + product_id + '"]');

        if (!product_id || !targets.length) {
          return;
        }

        targets.closest('.wlfmc-add-to-wishlist').removeClass('hide');
        var popupId = targets.closest('.wlfmc-add-to-wishlist').attr('data-popup-id');

        if (popupId) {
          var popup = $('#' + popupId);

          if (popup.length) {
            var original_price = popup.find('.wlfmc-parent-product-price').html();
            var product_title = popup.data('product-title');
            var desc = wlfmc_l10n.labels.popup_content;
            var title = wlfmc_l10n.labels.popup_title;
            var img = popup.find('.wlfmc-popup-header img').data('src');
            desc = desc.replace('{product_price}', original_price);
            desc = desc.replace('{product_name}', product_title);
            title = title.replace('{product_price}', original_price);
            title = title.replace('{product_name}', product_title);
            popup.find('.wlfmc-popup-title').html(title);
            popup.find('.wlfmc-popup-desc').html(desc);
            popup.find('.wlfmc-popup-header img').attr('src', img);
          }
        }

        targets.each(function () {
          var t = $(this),
              container = t.closest('.wlfmc-add-to-wishlist');
          t.attr('data-parent-product-id', product_id);
          t.attr('data-product-id', product_id);

          if (container.length) {
            container.removeClass(function (i, classes) {
              return classes.match(/wlfmc-add-to-wishlist-\S+/g).join(' ');
            }).addClass('wlfmc-add-to-wishlist-' + product_id).removeClass('exists');
          }

          container.find('.wlfmc-addtowishlist a').attr('href', container.attr('data-add-url').replace("#product_id", product_id));
          container.find('.wlfmc-removefromwishlist a').attr('href', container.attr('data-remove-url').replace("#product_id", product_id));
          $.each(product_in_list, function (i, v) {
            if (typeof v !== 'undefined' && v.product_id && v.product_id == product_id) {
              container.addClass('exists');
              container.find('.wlfmc_delete_item').attr('data-wishlist-id', v.wishlist_id);
              container.find('.wlfmc_delete_item').attr('data-item-id', v.item_id);
            }
          });
        });
      });
      t.on('wlfmc_reload_fragments', load_fragments);
      t.on('wlfmc_fragments_loaded', function (ev, original, update, firstLoad) {
        if (!firstLoad) {
          return;
        }

        $('.variations_form').find('.variations select').last().trigger('change');
      });
      /* === TABS === */

      t.on('click', '.wlfmc-tabs a:not(.external-link)', function (event) {
        event.preventDefault();
        var content = $(this).data('content');
        $('.wlfmc-tab-content').hide();
        $(this).closest('.wlfmc-tabs-wrapper').removeClass('active-tab-cart active-tab-save-for-later');
        $(this).closest('.wlfmc-tabs-wrapper').addClass('active-tab-' + content);
        $(this).closest('.wlfmc-tabs-wrapper').find('.wlfmc-tabs a').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        $('.wlfmc_content_' + content).show();
        window.history.replaceState('', '', updateURLParameter(window.location.href, "tab", content));
        return false;
      });
      /* === CLOSE NOTICE === */

      t.on('click', '.wlfmc-save-for-later-form a.wlfmc-close-notice', function (event) {
        event.preventDefault();

        if (!is_cookie_enabled()) {
          window.alert(wlfmc_l10n.labels.cookie_disabled);
          return;
        }

        setCookie('wlfmc_save_for_later_notice', true);
        $(this).closest('.wlfmc-notice-wrapper').animate({
          opacity: 0
        }, "slow").remove();
        return false;
      });
      /* === ACCORDION === */

      t.on('click', '.wlfmc-accordion-header', function (ev) {
        ev.preventDefault();

        if (!$(this).hasClass('active')) {
          $(this).addClass("active");
          $(this).next(".wlfmc-accordion-panel").slideDown();
        } else {
          $(this).removeClass("active");
          $(this).next(".wlfmc-accordion-panel").slideUp();
        }

        return false;
      });
      /* === DROPDOWN COUNTER === */

      if ('ontouchstart' in window || window.DocumentTouch && document instanceof DocumentTouch) {
        var wlfmc_swipe_trigger;
        t.on('touchstart', '.wlfmc-counter-wrapper.show-list-on-hover,.wlfmc-counter-wrapper.show-list-on-click', function (e) {
          wlfmc_swipe_trigger = false;
        });
        t.on('touchmove', '.wlfmc-counter-wrapper.show-list-on-hover,.wlfmc-counter-wrapper.show-list-on-click', function (e) {
          wlfmc_swipe_trigger = true;
        });
        t.on('touchend', '.wlfmc-counter-wrapper.show-list-on-hover .wlfmc-counter.has-dropdown,.wlfmc-counter-wrapper.show-list-on-click  .wlfmc-counter.has-dropdown', function (e) {
          var elem = $(this).closest('.wlfmc-counter-wrapper');

          if (elem.hasClass('wlfmc-first-touch')) {
            if (!wlfmc_swipe_trigger) {
              hide_mini_wishlist.call($('.wlfmc-counter-wrapper'), e);
            }
          } else {
            e.preventDefault();
            show_mini_wishlist.call(this, e);
            elem.addClass('wlfmc-first-touch');
          }
        });
        t.on('touchend', ':not(.wlfmc-counter-wrapper.show-list-on-hover):not(.wlfmc-counter-wrapper.show-list-on-click)', function (e) {
          if ($(e.target).closest('.wlfmc-counter-wrapper').length === 0) {
            hide_mini_wishlist.call($('.wlfmc-counter-wrapper'), e);
          }
        }); // fix url in dropdown in iphone devices

        t.on('touchend', '.wlfmc-counter-wrapper .wlfmc-counter.has-dropdown a:not(.remove_from_wishlist)', function (ev) {
          ev.preventDefault();
          window.location.href = $(this).attr('href');
          return false;
        });
      } else {
        t.on('click', '.wlfmc-counter-wrapper.show-list-on-click .wlfmc-counter.has-dropdown', function (ev) {
          ev.preventDefault();
          var elem = $('.dropdown_' + $(this).attr('data-id')) || $(this).closest('.wlfmc-counter-wrapper').find('.wlfmc-counter-dropdown');
          appendtoBody(elem.closest('.wlfmc-counter-wrapper'));
          prepare_mini_wishlist(elem);
          elem.toggleClass('lists-show');
          return false;
        });
        t.on("click", function (ev) {
          var $trigger = $(".wlfmc-counter-wrapper.show-list-on-click .wlfmc-counter.has-dropdown");

          if ($trigger !== ev.target && !$trigger.has(ev.target).length) {
            $('.wlfmc-counter-dropdown').removeClass("lists-show");
          }
        });
        t.on('mouseover', '.wlfmc-counter-wrapper.show-list-on-hover .wlfmc-counter-dropdown', function (ev) {
          ev.preventDefault();
          $(this).addClass("lists-show");
          return false;
        });
        t.on('mouseout', '.wlfmc-counter-wrapper.show-list-on-hover .wlfmc-counter-dropdown', function (ev) {
          ev.preventDefault();
          $(this).removeClass("lists-show");
          return false;
        });
        t.on('mouseover', '.wlfmc-counter-wrapper.show-list-on-hover .wlfmc-counter.has-dropdown', function (ev) {
          ev.preventDefault();
          var elem = $('.dropdown_' + $(this).attr('data-id')) || $(this).closest('.wlfmc-counter-wrapper').find('.wlfmc-counter-dropdown');
          $(elem).addClass("lists-show");
          appendtoBody(elem.closest('.wlfmc-counter-wrapper'));
          prepare_mini_wishlist(elem);
          return false;
        });
        t.on('mouseout', '.wlfmc-counter-wrapper.show-list-on-hover .wlfmc-counter.has-dropdown', function (ev) {
          ev.preventDefault();
          var elem = $('.dropdown_' + $(this).attr('data-id'));
          $(elem).removeClass("lists-show");
          return false;
        });
        $('.wlfmc-counter-wrapper.show-list-on-hover .wlfmc-counter.has-dropdown').hoverIntent({
          interval: 0,
          timeout: 100,
          over: show_mini_wishlist,
          out: hide_mini_wishlist
        });
      }

      init_prepare_qty_links();
      init_wishlist_popup();
      init_quantity();
      init_checkbox_handling();
      init_copy_wishlist_link();
      init_tooltip();
      init_components();
      init_popups();
      init_layout();
      init_drag_n_drop();
      init_popup_checkbox_handling();
    }).trigger('wlfmc_init'); // fix with jet woo builder plugin.

    $(document).on('jet-filter-content-rendered', reInit_wlfmc).on('jet-woo-builder-content-rendered', reInit_wlfmc).on('jet-engine/listing-grid/after-load-more', reInit_wlfmc).on('jet-engine/listing-grid/after-lazy-load', reInit_wlfmc).on('jet-cw-loaded', reInit_wlfmc);
    $(document).on('ready', load_fragments); // load fragment for fix filter everything ajax response.

    var observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        if ($('.woocommerce-product-gallery__wrapper .wlfmc-top-of-image').length > 0) {
          init_fix_on_image_single_position();
        } // fix top of image for powerpack single product.


        if ($('.pp-single-product .entry-summary > .wlfmc-top-of-image').length > 0 && $('.pp-single-product .entry-summary .single-product-image').length > 0) {
          $('.pp-single-product').each(function () {
            var $wlfmcTopOfImage = $(this).find('.wlfmc-top-of-image');
            var $singleProductImage = $(this).find('.single-product-image');

            if ($wlfmcTopOfImage.length > 0 && $singleProductImage.length > 0) {
              $wlfmcTopOfImage.appendTo($singleProductImage);
            }
          });
        }
      });
    });
    observer.observe($('body')[0], {
      childList: true,
      subtree: true
    });

    function init_prepare_qty_links() {
      var qty = document.querySelectorAll('.wlfmc-wishlist-table .quantity');

      if (qty.length < 1) {
        return false;
      }

      for (var i = 0; i < qty.length; i++) {
        if (qty[i].classList.contains('hidden')) {
          return false;
        }

        var plus = qty[i].querySelector('.botiga-quantity-plus, a.plus, .ct-increase'),
            minus = qty[i].querySelector('.botiga-quantity-minus, a.minus, .ct-decrease');

        if (!plus || !minus || plus.length < 1 || minus.length < 1) {
          return false;
        }

        plus.classList.add('show');
        minus.classList.add('show');
        var plus_clone = plus.cloneNode(true),
            minus_clone = minus.cloneNode(true);
        plus_clone.addEventListener('click', function (e) {
          e.preventDefault();
          var input = this.parentNode.querySelector('.qty'),
              val = parseFloat(input.value, 10) || 0,
              changeEvent = document.createEvent('HTMLEvents');
          var max_val = input.getAttribute("max") && parseFloat(input.getAttribute("max"), 0) || 1 / 0;
          input.value = val < max_val ? Math.round(100 * (val + parseFloat(input.step || "1"))) / 100 : max_val; // input.value = input.value === '' ? 0 : parseInt( input.value ) + 1;

          changeEvent.initEvent('change', true, false);
          input.dispatchEvent(changeEvent);
          return false;
        });
        minus_clone.addEventListener('click', function (e) {
          e.preventDefault();
          var input = this.parentNode.querySelector('.qty'),
              val = parseFloat(input.value, 10) || 0,
              changeEvent = document.createEvent('HTMLEvents');
          var min_val = input.getAttribute("min") ? Math.round(100 * parseFloat(input.getAttribute("min"), 0)) / 100 : 0;
          input.value = val > min_val ? Math.round(100 * (val - parseFloat(input.step || "1"))) / 100 : min_val; // input.value = parseInt( input.value ) > 0 ? parseInt( input.value ) - 1 : 0;

          changeEvent.initEvent('change', true, false);
          input.dispatchEvent(changeEvent);
          return false;
        });
        qty[i].replaceChild(plus_clone, plus);
        qty[i].replaceChild(minus_clone, minus);
      }
    }
    /* === DROPDOWN COUNTER === */


    function prepare_mini_wishlist(a) {
      if (a.hasClass('position-absolute')) {
        var ao = a.offset(),
            al = ao.left,
            at = ao.top,
            aw = a.outerWidth(),
            ah = a.outerHeight(),
            la = parseFloat(a.css('left')),
            ta = parseFloat(a.css('top')),
            aol = al - la,
            aot = at - ta,
            _la = la,
            _ta = ta,
            ww = $(window).width(),
            dh = $(document).height(),
            os = 50,
            r = ww - aol - aw - os,
            l = os - aol,
            b = dh - aot - ah - os;

        if (ww <= aw) {
          _la = -1 * aol;
        } else if (0 > ww - (aw + os * 2)) {
          _la = (ww - aw) / 2 - aol;
        } else if (0 < l) {
          _la = l;
        } else if (0 > r) {
          _la = r;
        }

        if (dh < ah) {
          a.height(dh - a.outerHeight() + a.height());
          ah = a.outerHeight();
        }

        if (dh <= ah) {
          _ta = -1 * aot;
        } else if (0 > dh - (ah + os * 2)) {
          _ta = (dh - ah) / 2 - aot;
        } else if (0 > b) {
          _ta = b;
        }

        a.css({
          left: _la,
          top: _ta
        });
      } else {
        var p = $('.wlfmc-counter-wrapper.' + a.attr('data-id'));

        if (typeof p !== 'undefined' && 0 < p.length) {
          var po = p.offset(),
              st = $(window).scrollTop(),
              _la = po.left,
              _ta = po.top + p.height() - st,
              aw = a.outerWidth(),
              ww = $(window).width();

          if (_la + aw > ww) {
            _la = ww - aw - 20;
          }

          a.css({
            left: _la,
            top: _ta
          });
        }
      }
    }

    $(window).on("scroll resize", function () {
      $(".wlfmc-counter-dropdown").each(function () {
        prepare_mini_wishlist($(this));
      });
    });

    function appendtoBody(elem) {
      if (!elem.closest('.wlfmc-counter-wrapper').find('.position-fixed').length > 0) {
        return;
      }

      var counter_type = elem.closest('.wlfmc-counter-wrapper').find('.wlfmc-counter-items').hasClass('wlfmc-lists-counter-dropdown') ? 'wlfmc-premium-list-counter' : elem.closest('.wlfmc-counter-wrapper').hasClass('wlfmc-waitlist-counter-wrapper') ? 'wlfmc-waitlist-counter' : 'wlfmc-wishlist-counter';

      if (elem.closest('.elementor-widget-wlfmc-wishlist-counter').length > 0 || elem.closest('.elementor-widget-wlfmc-waitlist-counter').length > 0 || elem.closest('.elementor-widget-wlfmc-premium-list-counter').length > 0) {
        var widgetId = elem.closest('.elementor-widget-wlfmc-wishlist-counter').data("id") || elem.closest('.elementor-widget-wlfmc-waitlist-counter').data("id") || elem.closest('.elementor-widget-wlfmc-premium-list-counter').data("id");
        var elementId = elem.closest('[data-elementor-id]').data("elementor-id");
        var elementor = "<div class='wlfmc-elementor elementor elementor-" + elementId + " " + counter_type + "'><div class='elementor-element elementor-element-" + widgetId + "'></div></div>";
        $(elementor).appendTo("body");
        $(".wlfmc-elementor.elementor-" + elementId + " .elementor-element-" + widgetId).append(elem);
      } else if (!elem.closest('.wlfmc-elementor').length > 0) {
        var widgetId = elem.closest('.wlfmc-counter-wrapper').find('.wlfmc-counter-items').data("id");
        var elementor = "<div class='wlfmc-elementor no-elementor-" + widgetId + " " + counter_type + "'></div>";
        $(elementor).appendTo("body");
        $(".wlfmc-elementor.no-elementor-" + widgetId).append(elem);
      }
    }

    function show_mini_wishlist() {
      $('.wlfmc-counter-dropdown').removeClass("lists-show");
      var elem = $('.dropdown_' + $(this).attr('data-id')) || $(this).closest('.wlfmc-counter-wrapper').find('.wlfmc-counter-dropdown');
      appendtoBody(elem.closest('.wlfmc-counter-wrapper'));
      prepare_mini_wishlist(elem);
      elem.addClass('lists-show');
    }

    function hide_mini_wishlist() {
      var elem = $(this).closest('.wlfmc-counter-wrapper').find('.wlfmc-counter-dropdown');
      $('.wlfmc-first-touch').removeClass('wlfmc-first-touch');
      $('.wlfmc-first-click').removeClass('wlfmc-first-click');
      elem.removeClass('lists-show');
    }

    function validateForm(form) {
      var isValid = true;
      form.find('input, select, textarea').each(function () {
        if ($(this).prop('required') && !$(this).val()) {
          isValid = false;
          return false;
        }
      });
      return isValid;
    }

    function reInit_wlfmc() {
      $(document).trigger('wlfmc_init');
    }
    /**
     * Handle Drag & Drop of items for sorting
     *
     * @return void
     * @since 1.6.8
     */


    function init_drag_n_drop() {
      $('table.wlfmc-wishlist-items-wrapper').filter('.sortable').not('.no-interactions').each(function () {
        var t = $(this),
            jqxhr = false;
        t.sortable({
          items: '[data-row-id]',
          handle: ".sortable-handle",
          placeholder: "ui-sortable-placeholder",
          scroll: true,
          tolerance: "pointer",
          helper: function helper(e, ui) {
            ui.children().each(function () {
              var elems = $(this).closest('.wishlist-items-wrapper').find('.show-meta-data');

              if (elems.length > 0) {
                elems.removeClass('show-meta-data');
              }

              elems = $(this).closest('.wishlist-items-wrapper').find('.wlfmc-row-meta-data');

              if (elems.length > 0) {
                elems.addClass('hide');
              }
            });
            return ui;
          },
          update: function update(event, ui) {
            var row = t.find('[data-row-id]'),
                positions = [],
                i = 0;

            if (!row.length) {
              return;
            }

            if (jqxhr) {
              jqxhr.abort();
            }

            row.each(function () {
              var t = $(this);
              t.find('input[name*="[position]"]').val(i++);
              positions.push(t.data('row-id'));
              var elements = t.closest('.wishlist-items-wrapper').find('.parent-row-id-' + t.data('row-id'));

              if (elements.length > 0) {
                t.after(elements);
              }
            });
            jqxhr = $.ajax({
              data: {
                action: wlfmc_l10n.actions.sort_wishlist_items,
                nonce: wlfmc_l10n.ajax_nonce.sort_wishlist_items_nonce,
                context: 'frontend',
                positions: positions,
                wishlist_token: t.data('token'),
                page: t.data('page'),
                per_page: t.data('per-page')
              },
              method: 'POST',
              url: wlfmc_l10n.admin_url
            });
          }
        }).disableSelection();
      });
    }
    /* === Tooltip === */


    function init_tooltip() {
      var wlfmc_tooltip = function wlfmc_tooltip() {
        var instance;

        var _self = this;

        this.idSelector = 'wlfmc-tooltip';
        this.text = '';
        this.top = 0;
        this.left = 0;
        this.direction = typeof this.direction !== 'undefined' ? this.direction : 'bottom';
        this.t_type = typeof this.t_type !== 'undefined' ? this.t_type : 'default';
        this.target = ''; // Create actual element and tie element to object for reference.

        this.node = document.getElementById(this.idSelector);

        if (!this.node) {
          this.node = document.createElement("div");
          this.node.setAttribute("id", this.idSelector);
          this.node.className = this.node.className + "tooltip__hidden";
          this.node.innerHTML = this.text;
          document.body.appendChild(this.node);
        }

        this.show = function () {
          // Rerender tooltip.
          var location_order = ['top', 'right', 'bottom', 'left'];
          _self.node.innerHTML = _self.text;
          var direction = _self.direction;
          var t_type = _self.t_type;

          if (direction) {
            $(this.node).addClass('tooltip__expanded tooltip__expanded-' + direction);
          } else {
            $(this.node).addClass('tooltip__expanded');
          }

          $(this.node).addClass('wlfmc-tooltip-' + t_type);
          $(this.node).removeClass('tooltip__hidden');

          if (offscreen($(wlfmcTooltip.node))) {
            wlfmcTooltip.hide();
            wlfmcTooltip.direction = location_order[location_order.indexOf(wlfmcTooltip.direction) + 1];
            moveTip(wlfmcTooltip.node, wlfmcTooltip.target);
          }
        };

        this.hide = function () {
          // Hide tooltip.
          $(_self.node).css('top', '0');
          $(_self.node).css('left', '0');
          $(_self.node).attr('class', '');
          $(_self.node).addClass('tooltip__hidden');
        };
      }; // Move tip to proper location before display.


      var offscreen = function offscreen(el) {
        return el.offsetLeft + el.offsetWidth < 0 || el.offsetTop + el.offsetHeight < 0 || el.offsetLeft + el.offsetWidth > window.innerWidth || el.offsetTop + el.offsetHeight > window.innerHeight;
      };

      var moveTip = function moveTip(ell, target) {
        var $target = $(target);
        var $ell = $(ell);
        var body = $("body").offset();
        $("body").css({
          'position': 'relative'
        }); // fix $ell size after change new tooltip text.

        wlfmcTooltip.show();
        wlfmcTooltip.hide();
        var buu = 7; // Default padding size in px.
        // var center_height = -($ell.outerHeight() / 2) / 2;

        var center_height = ($target.outerHeight() - $ell.outerHeight()) / 2;
        var center_width = -($ell.outerWidth() / 2) + $target.outerWidth() / 2;
        var locations = {
          'top': [-$ell.outerHeight() - buu, center_width],
          'right': [center_height, $target.outerWidth() + buu],
          'bottom': [$target.outerHeight() + buu, center_width],
          'left': [center_height, -$ell.outerWidth() - buu]
        };
        var location_order = ['top', 'right', 'bottom', 'left'];
        var location_keys = Object.keys(locations);

        if (wlfmcTooltip.direction === 'top' || wlfmcTooltip.direction === 'bottom') {
          $ell.css('top', $target.offset().top - body.top + locations[wlfmcTooltip.direction][0]);
          $ell.css('left', $target.offset().left - body.left + buu / 2 + locations[wlfmcTooltip.direction][1]);
        } else {
          // $ell.css( 'top', $target.offset().top - (body.top) + (buu / 2) + locations[wlfmcTooltip.direction][0] );
          var top = locations[wlfmcTooltip.direction][0] - buu / 2;
          top = top < 0 ? top + buu / 2 : top;
          $ell.css('top', $target.offset().top - body.top + top);
          $ell.css('left', $target.offset().left - body.left + locations[wlfmcTooltip.direction][1]);
        }

        if (offscreen($ell)) {
          wlfmcTooltip.direction = location_order[location_order.indexOf(wlfmcTooltip.direction) + 1];
          wlfmcTooltip.show();
        } else {
          wlfmcTooltip.show();
        }
      }; // Create global wlfmc_tooltip.


      var wlfmcTooltip = new wlfmc_tooltip(); // Mouseover to show.

      $(".wlfmc-tooltip").on("mouseover", function (ee) {
        var _self = this;

        wlfmcTooltip.target = _self; // Default to self.

        var name_classes = _self.className.split(' ');

        name_classes.forEach(function (cc) {
          if (cc.indexOf('wlfmc-tooltip-') != -1) {
            // Find a directional tag.
            wlfmcTooltip.direction = cc.split('-')[cc.split('-').length - 1];
          }
        });

        if ($(this).attr('data-tooltip-type')) {
          wlfmcTooltip.t_type = $(this).attr('data-tooltip-type');
        }

        if ($(this).attr('data-tooltip-text')) {
          wlfmcTooltip.text = $(this).attr('data-tooltip-text');
          moveTip(wlfmcTooltip.node, wlfmcTooltip.target);
        }
      });
      $('.wlfmc-tooltip').on("mouseout", function (ee) {
        // Re-hide tooltip.
        wlfmcTooltip.hide();
      });
    }

    function init_fix_on_image_single_position() {
      if ($('.woocommerce-product-gallery__wrapper .wlfmc-top-of-image').length > 0) {
        $('.woocommerce-product-gallery__wrapper .wlfmc-top-of-image').each(function () {
          $(this).insertAfter($(this).parent());
        });
      }

      var topOfImageElems = document.querySelectorAll('.wlfmc-top-of-image');

      for (var i = 0; i < topOfImageElems.length; i++) {
        var currentElem = topOfImageElems[i]; // Set the margin top of the next sibling element to the height of the current element.

        if (currentElem.nextElementSibling) {
          var positionClass = _toConsumableArray(currentElem.nextElementSibling.classList).find(function (className) {
            return className.startsWith("wlfmc_position_image_");
          });

          var currentPosition = _toConsumableArray(currentElem.classList).find(function (className) {
            return className.startsWith("wlfmc_position_image_");
          });

          if (positionClass === currentPosition) {
            if ('wlfmc_position_image_top_left' === positionClass || 'wlfmc_position_image_top_right' === positionClass) {
              var marginTop = "".concat(currentElem.offsetHeight + 5, "px"); // Check for previous siblings with the same position class and add their heights and gap values to marginTop.

              var prevSibling = currentElem.previousElementSibling;

              while (prevSibling && prevSibling.classList.contains('wlfmc-top-of-image') && prevSibling.classList.contains(positionClass)) {
                marginTop = "calc( ".concat(marginTop, " + ").concat(prevSibling.offsetHeight + 5, "px )");
                prevSibling = prevSibling.previousElementSibling;
              }

              currentElem.nextElementSibling.style.marginTop = marginTop;
            } else if ('wlfmc_position_image_bottom_left' === positionClass || 'wlfmc_position_image_bottom_right' === positionClass) {
              var marginBottom = "".concat(currentElem.offsetHeight + 5, "px"); // Check for previous siblings with the same position class and add their heights and gap values to marginBottom.

              var _prevSibling = currentElem.previousElementSibling;

              while (_prevSibling && _prevSibling.classList.contains('wlfmc-top-of-image') && _prevSibling.classList.contains(positionClass)) {
                marginBottom = "calc( ".concat(marginBottom, " + ").concat(_prevSibling.offsetHeight + 5, "px )");
                _prevSibling = _prevSibling.previousElementSibling;
              }

              currentElem.nextElementSibling.style.marginBottom = marginBottom;
            }
          }
        }
      }
    }
    /* === INIT FUNCTIONS === */

    /**
     * Init popup for all links with the plugin that open a popup
     *
     * @return void
     */


    function init_wishlist_popup() {
      // add & remove class to body when popup is opened.
      var callback = function callback(node, op) {
        if (typeof node.classList !== 'undefined' && node.classList.contains('wlfmc-overlay')) {
          var method = 'remove' === op ? 'removeClass' : 'addClass';
          $('body')[method]('wlfmc-with-popup');
        }
      },
          callbackAdd = function callbackAdd(node) {
        callback(node, 'add');
      },
          callbackRemove = function callbackRemove(node) {
        callback(node, 'remove');
      },
          observer = new MutationObserver(function (mutationsList) {
        for (var i in mutationsList) {
          var mutation = mutationsList[i];

          if (mutation.type === 'childList') {
            if (typeof mutation.addedNodes !== 'undefined') {
              mutation.addedNodes.forEach(callbackAdd);
            }

            if (typeof mutation.removedNodes !== 'undefined') {
              mutation.removedNodes.forEach(callbackRemove);
            }
          }
        }
      });

      observer.observe(document.body, {
        childList: true
      });
    }
    /**
     * Init checkbox handling
     *
     * @return void
     */


    function init_checkbox_handling() {
      var checkboxes = $('.wlfmc-wishlist-table, .wlfmc-save-for-later-table').find('tbody .product-checkbox input[type="checkbox"]');
      var link = $('.multiple-product-move,.multiple-product-copy');
      checkboxes.off('change').on('change', function (e) {
        e.preventDefault();
        var t = $(this),
            p = t.parent();

        if (!t.is(':checked')) {
          $('input[name="' + t.attr('name') + '"]').prop('checked', false);
          $('#bulk_add_to_cart').prop('checked', false);
          $('#bulk_add_to_cart2').prop('checked', false);
        }

        p.removeClass('checked').removeClass('unchecked').addClass(t.is(':checked') ? 'checked' : 'unchecked');

        if (link.length > 0) {
          var isChecked = checkboxes.is(':checked');

          if (isChecked) {
            link.show();
          } else {
            link.hide();
          }

          var row = $(this).closest('tr');
          var itemId = row.attr('data-item-id');
          var existingItemId = link.attr('data-item-id');

          if (t.is(':checked')) {
            if (existingItemId) {
              existingItemId = existingItemId.split(',');
              existingItemId.push(itemId);
              existingItemId = existingItemId.join(',');
            } else {
              existingItemId = itemId;
            }
          } else {
            if (existingItemId) {
              existingItemId = existingItemId.split(',');
              var index = existingItemId.indexOf(itemId);

              if (index !== -1) {
                existingItemId.splice(index, 1);
              }

              existingItemId = existingItemId.join(',');
            }
          }

          link.attr('data-item-id', existingItemId);
        }

        return false;
      }).trigger('change');
    }
    /**
     * Init js handling on wishlist table items after ajax update
     *
     * @return void
     */


    function init_handling_after_ajax() {
      init_prepare_qty_links();
      init_checkbox_handling();
      init_quantity();
      init_copy_wishlist_link();
      init_tooltip(); //init_components();

      init_layout();
      init_drag_n_drop();
      init_popup_checkbox_handling();
      $(document).trigger('wlfmc_init_after_ajax');
    }
    /**
     * Handle quantity input change for each wishlist item
     *
     * @return void
     */


    function init_quantity() {
      var jqxhr, timeout;
      $('.wlfmc-wishlist-table , .wlfmc-save-for-later-table').on('change', '.quantity :input', function () {
        var t = $(this),
            row = t.closest('[data-row-id]'),
            product_id = row.data('row-id'),
            cart_item_key = row.data('cart-item-key'),
            table = t.closest('.wlfmc-wishlist-table,.wlfmc-save-for-later-table'),
            token = table.data('token');
        clearTimeout(timeout); // set add to cart link to add specific qty to cart.

        row.find('.add_to_cart_button').attr('data-quantity', t.val());
        timeout = setTimeout(function () {
          if (jqxhr) {
            jqxhr.abort();
          }

          jqxhr = $.ajax({
            url: wlfmc_l10n.ajax_url,
            data: {
              action: wlfmc_l10n.actions.update_item_quantity,
              nonce: wlfmc_l10n.ajax_nonce.update_item_quantity_nonce,
              context: 'frontend',
              product_id: product_id,
              cart_item_key: cart_item_key,
              wishlist_token: token,
              quantity: t.val() //fragments: retrieve_fragments()

            },
            method: 'POST',
            beforeSend: function beforeSend(xhr) {
              if (wlfmc_l10n.ajax_mode === 'rest_api') {
                xhr.setRequestHeader('X-WP-Nonce', wlfmc_l10n.nonce);
              }

              block(row);
            },
            complete: function complete() {
              unblock(row);
            },
            success: function success(response) {
              load_fragments();
              /*if (typeof response.fragments !== 'undefined') {
              	replace_fragments( response.fragments );
              	init_handling_after_ajax();
              }*/
            }
          });
        }, 1000);
      });
    }

    function init_popups() {
      $(document).on('click', '.wlfmc-popup-trigger:not(.wlfmc-disabled)', function (ev) {
        ev.preventDefault();
        var id = $(this).data('popup-id');
        var elem = $('#' + id);
        var defaultOptions = {
          absolute: false,
          color: '#333',
          transition: 'all 0.3s',
          horizontal: elem.data('horizontal'),
          vertical: elem.data('vertical')
        };
        elem.popup(defaultOptions);
        $('#' + id).popup('show');
        return false;
      });
      $(document).on('click', '.wlfmc-popup-close', function (ev) {
        ev.preventDefault();
        var id = $(this).data('popup-id');
        $('#' + id).popup('hide');
        return false;
      });
    }

    function init_layout() {
      var jqxhr, timeout;
      $('.wlfmc-toggle-layout').on('click', function (ev) {
        ev.preventDefault();
        var elem = $(this);
        elem.toggleClass('list').toggleClass('grid');
        elem.closest('.wlfmc-wishlist-form').find('.wlfmc-list').toggleClass('view-mode-list').toggleClass('view-mode-grid');
        clearTimeout(timeout);
        timeout = setTimeout(function () {
          if (jqxhr) {
            jqxhr.abort();
          }

          jqxhr = $.ajax({
            url: wlfmc_l10n.ajax_url,
            data: {
              action: wlfmc_l10n.actions.change_layout,
              nonce: wlfmc_l10n.ajax_nonce.change_layout_nonce,
              context: 'frontend',
              new_layout: elem.hasClass('list') ? 'grid' : 'list'
            },
            method: 'POST',
            beforeSend: function beforeSend(xhr) {
              if (wlfmc_l10n.ajax_mode === 'rest_api') {
                xhr.setRequestHeader('X-WP-Nonce', wlfmc_l10n.nonce);
              }
            },
            complete: function complete() {// anything.
            }
          });
        }, 1000);
        return false;
      });
    }

    function init_components() {
      $(document).on('click', '.wlfmc-list .product-components', function (e) {
        e.preventDefault();
        var $this = $(this);
        var elem = $this.closest('tr');
        var $metaData = elem.find('.wlfmc-absolute-meta-data');
        var $next = elem.next('.wlfmc-row-meta-data').filter('.wlfmc-row-meta-data');
        var isNextHidden = $next.hasClass('hide');
        $metaData.fadeToggle();
        $next.toggleClass('hide');
        elem.toggleClass('show-meta-data', isNextHidden);
        return false;
      });
      $(document).on('click', '.wlfmc-list .close-components', function (e) {
        e.preventDefault();
        var elem = $(this).closest('tr');
        elem.find('.wlfmc-absolute-meta-data').fadeToggle();
        elem.removeClass('show-meta-data');
        return false;
      });
    }

    function init_popup_checkbox_handling() {
      $(document).on('change', '.list-item-checkbox', function () {
        var selectedItem = $(this).closest('.list-item');
        var parentContainer = $(this).closest('.wlfmc-add-to-list-container, .wlfmc-move-to-list-wrapper, .wlfmc-copy-to-list-wrapper');

        if (parentContainer.hasClass('wlfmc-add-to-list-container')) {
          if ($(this).is(':checked')) {
            selectedItem.addClass('selected');
          } else {
            selectedItem.removeClass('selected');
          }
        }

        if (parentContainer.hasClass('wlfmc-move-to-list-wrapper') || parentContainer.hasClass('wlfmc-copy-to-list-wrapper')) {
          var checkboxes = parentContainer.find('input[type="checkbox"]');
          parentContainer.find('.list-item').removeClass('selected');

          if ($(this).is(':checked')) {
            selectedItem.addClass('selected');
            checkboxes.not($(this)).prop('checked', false);
          }
        }
      });
    }
    /**
     * Init handling for copy button
     *
     * @return void
     */


    function init_copy_wishlist_link() {
      $('.copy-link-trigger').on('click', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        var obj_to_copy = $(this);
        var hidden = $('<input/>', {
          val: obj_to_copy.attr('data-href'),
          type: 'text'
        });
        $('body').append(hidden);

        if (isOS()) {
          hidden[0].setSelectionRange(0, 9999);
        } else {
          hidden.select();
        }

        document.execCommand('copy');
        hidden.remove();
        toastr.success(wlfmc_l10n.labels.link_copied);
        return false;
      });
    }
    /* === EVENT HANDLING === */


    function load_automations(product_id, wishlist_id, customer_id, list_type) {
      $.ajax({
        url: wlfmc_l10n.ajax_url,
        data: {
          action: wlfmc_l10n.actions.load_automations,
          nonce: wlfmc_l10n.ajax_nonce.load_automations_nonce,
          context: 'frontend',
          product_id: parseInt(product_id),
          wishlist_id: parseInt(wishlist_id),
          customer_id: parseInt(customer_id),
          list_type: list_type
        },
        method: 'POST',
        beforeSend: function beforeSend(xhr) {
          if (wlfmc_l10n.ajax_mode === 'rest_api') {
            xhr.setRequestHeader('X-WP-Nonce', wlfmc_l10n.nonce);
          }
        },
        complete: function complete() {// anything.
        }
      });
    }

    function add_to_save_for_later(cart_item_key, elem) {
      var data = {
        action: wlfmc_l10n.actions.add_to_save_for_later_action,
        nonce: wlfmc_l10n.ajax_nonce.add_to_save_for_later_nonce,
        context: 'frontend',
        add_to_save_for_later: cart_item_key //fragments: retrieve_fragments()

      };

      if (!is_cookie_enabled()) {
        window.alert(wlfmc_l10n.labels.cookie_disabled);
        return;
      }

      $.ajax({
        url: wlfmc_l10n.save_for_later_ajax_url,
        data: data,
        type: 'POST',
        //dataType: 'json',
        cache: false,
        beforeSend: function beforeSend(xhr) {
          if (wlfmc_l10n.ajax_mode === 'rest_api') {
            xhr.setRequestHeader('X-WP-Nonce', wlfmc_l10n.nonce);
          }

          if (elem && elem.length) {
            //block( elem );
            loading(elem);
          }
        },
        complete: function complete() {
          if (elem && elem.length) {
            //unblock( elem );
            unloading(elem);
          }
        },
        success: function success(response) {
          var response_result = response.result,
              response_message = response.message,
              show_toast = true;

          if ('true' === response_result) {
            load_fragments();
            /*if (typeof response.fragments !== 'undefined') {
            	replace_fragments( response.fragments );
            }*/

            if (show_toast && '' !== $.trim(wlfmc_l10n.labels.sfl_product_added_text)) {
              toastr.success(wlfmc_l10n.labels.sfl_product_added_text);
            }

            load_automations(response.product_id, response.wishlist_id, response.customer_id, 'save-for-later');

            if (response.count !== 'undefined') {
              $('.wlfmc-tabs-wrapper').attr('data-count', response.count);
            }

            $(document.body).trigger('wc_update_cart');
          }

          if (show_toast && '' !== $.trim(response.message) && response_result !== 'true') {
            toastr.error(response_message);
          }
        }
      });
    }

    function check_waitlist_modules(data, product_id, variation_id) {
      if (!product_id || !variation_id) {
        return;
      }

      var product_type = 'variation';

      if (!data) {
        variation_id = product_id;
        product_type = 'variable';
      }

      var targets = $('.wlfmc-add-to-waitlist [data-parent-product-id="' + product_id + '"]'),
          target_boxes = $('.wlfmc-add-to-outofstock [data-parent-product-id="' + product_id + '"]');
      target_boxes.each(function () {
        var t = $(this),
            container = t.closest('.wlfmc-add-to-outofstock');
        t.attr('data-parent-product-id', product_id);
        t.attr('data-product-id', variation_id);
        container.removeClass(function (i, classes) {
          return classes.match(/wlfmc-add-to-outofstock-\S+/g).join(' ');
        }).addClass('wlfmc-add-to-outofstock-' + variation_id).removeClass('exists');
        var outofstockbox = $('.wlfmc-add-to-outofstock-' + variation_id);

        if (outofstockbox.length === 0) {
          outofstockbox = $('.wlfmc-add-to-outofstock-' + product_id);
        }

        if (null !== data) {
          if (data.is_in_stock || isTrue(data.exclude_back_in_stock)) {
            outofstockbox.addClass('hide');
          } else {
            outofstockbox.removeClass('hide'); // show outofstock box.
          }
        } else {
          var out_of_stock = outofstockbox.data('product-outofstock');

          if (isTrue(out_of_stock)) {
            outofstockbox.removeClass('hide');
          } else {
            outofstockbox.addClass('hide');
          }
        }

        $.each(product_in_waitlist, function (i, v) {
          if (typeof v !== 'undefined' && v.product_id && v.product_id === variation_id) {
            var outofstockbox = $('.wlfmc-add-to-outofstock-' + v.product_id);
            outofstockbox.removeClass('exists');
            outofstockbox.each(function () {
              if (isTrue(v.back_in_stock)) {
                $(this).addClass('exists');
              }
            });
          }
        });
        outofstockbox.find('.wlfmc-add-button > a').attr('data-parent-product-id', product_id).attr('data-product-id', variation_id).attr('data-product-type', product_type);
      });
      targets.each(function () {
        var t = $(this),
            container = t.closest('.wlfmc-add-to-waitlist'),
            popup = $('#' + t.closest('.wlfmc-add-to-waitlist').data('popup-id')),
            backinstock_checkbox = popup.length ? popup.find('input[name="list_back-in-stock"]') : false,
            pricechange_checkbox = popup.length ? popup.find('input[name="list_price-change"]') : false,
            lowstock_checkbox = popup.length ? popup.find('input[name="list_low-stock"]') : false,
            onsale_checkbox = popup.length ? popup.find('input[name="list_on-sale"]') : false,
            available_modules = container.data('available-lists');
        container.removeClass('opacity-half');

        if (data) {
          if (data.is_in_stock) {
            if (lowstock_checkbox.length > 0) {
              if (data.enable_low_stock && isTrue(data.enable_low_stock) && !isTrue(data.exclude_low_stock)) {
                lowstock_checkbox.closest('.list-item').removeClass('hide');
              } else {
                lowstock_checkbox.closest('.list-item').addClass('hide');
              }
            }

            if (pricechange_checkbox.length > 0) {
              if (isTrue(data.exclude_price_change)) {
                pricechange_checkbox.closest('.list-item').addClass('hide');
              } else {
                pricechange_checkbox.closest('.list-item').removeClass('hide');
              }
            }

            if (backinstock_checkbox.length > 0) {
              backinstock_checkbox.closest('.list-item').addClass('hide');
            }

            if (onsale_checkbox.length > 0) {
              if (data.display_price !== data.display_regular_price || isTrue(data.exclude_on_sale)) {
                onsale_checkbox.closest('.list-item').addClass('hide');
              } else {
                onsale_checkbox.closest('.list-item').removeClass('hide');
              }
            } // hide add to waitlist buttons if not active modules in popup.


            if (!popup.find('.wlfmc-waitlist-select-type .list-item:not(.hide)').length) {
              container.addClass('opacity-half');
            }
          } else if (_typeof2(available_modules) === "object" && 'back-in-stock' in available_modules && !isTrue(data.exclude_back_in_stock)) {
            if (backinstock_checkbox.length > 0) {
              backinstock_checkbox.closest('.list-item').removeClass('hide');
            }

            if (pricechange_checkbox.length > 0) {
              pricechange_checkbox.closest('.list-item').addClass('hide');
            }

            if (lowstock_checkbox.length > 0) {
              lowstock_checkbox.closest('.list-item').addClass('hide');
            }

            if (onsale_checkbox.length > 0) {
              onsale_checkbox.closest('.list-item').addClass('hide');
            }
          } else {
            container.addClass('opacity-half'); // hide add to waitlist buttons.
          }
        }

        t.attr('data-parent-product-id', product_id);
        t.attr('data-product-id', variation_id);
        popup.find('input[name="list_back-in-stock"]').prop('checked', false);
        popup.find('input[name="list_price-change"]').prop('checked', false);
        popup.find('input[name="list_on-sale"]').prop('checked', false);
        popup.find('input[name="list_low-stock"]').prop('checked', false);
        container.removeClass(function (i, classes) {
          return classes.match(/wlfmc-add-to-waitlist-\S+/g).join(' ');
        }).addClass('wlfmc-add-to-waitlist-' + variation_id).removeClass('exists');
        $.each(product_in_waitlist, function (i, v) {
          if (typeof v !== 'undefined' && v.product_id && v.product_id == variation_id) {
            if (isTrue(v.price_change) || isTrue(v.on_sale) || isTrue(v.low_stock) || isTrue(v.back_in_stock) && popup.find('input[name="list_back-in-stock"]').length > 0) {
              container.addClass('exists');
            }

            if (isTrue(v.back_in_stock)) {
              popup.find('input[name="list_back-in-stock"]').prop('checked', true);
            }

            if (isTrue(v.price_change)) {
              popup.find('input[name="list_price-change"]').prop('checked', true);
            }

            if (isTrue(v.on_sale)) {
              popup.find('input[name="list_on-sale"]').prop('checked', true);
            }

            if (isTrue(v.low_stock)) {
              popup.find('input[name="list_low-stock"]').prop('checked', true);
            }
          }
        });
        popup.find('.wlfmc_add_to_waitlist').attr('data-parent-product-id', product_id).attr('data-product-id', variation_id).attr('data-product-type', product_type);

        if ('variable' === product_type && isTrue(wlfmc_l10n.waitlist_required_product_variation)) {
          container.find('.wlfmc-popup-trigger').addClass('wlfmc-disabled');
        } else {
          container.find('.wlfmc-popup-trigger').removeClass('wlfmc-disabled');
        }
      });
    }
    /* === UTILS === */

    /**
     * Add loading to element
     *
     * @param item jQuery object
     * @return void
     */


    function loading(item) {
      if (item.find('i').length > 0) {
        item.addClass('wlfmc-action wlfmc-loading');
      } else {
        item.addClass('wlfmc-action wlfmc-loading-alt');
      }
    }
    /**
     * Remove loading to element
     *
     * @param item jQuery object
     * @return void
     */


    function unloading(item) {
      item.removeClass('wlfmc-loading wlfmc-loading-alt');
    }
    /**
     * Block item if possible
     *
     * @param item jQuery object
     * @return void
     */


    function block(item) {
      if (typeof $.fn.block !== 'undefined' && wlfmc_l10n.enable_ajax_loading) {
        item.fadeTo('400', '0.6').block({
          message: null,
          overlayCSS: {
            background: 'transparent url(' + wlfmc_l10n.ajax_loader_url + ') no-repeat center',
            backgroundSize: '40px 40px',
            opacity: 1
          }
        });
      }
    }

    function table_block() {
      if (typeof $.fn.block !== 'undefined') {
        $('.wlfmc-wishlist-table-wrapper, .wlfmc-save-for-later-table-wrapper').fadeTo('400', '0.6').block({
          message: null,
          overlayCSS: {
            background: 'transparent url(' + wlfmc_l10n.ajax_loader_url + ') no-repeat center',
            backgroundSize: '80px 80px',
            opacity: 1
          }
        });
      }
    }
    /**
     * Unblock item if possible
     *
     * @param item jQuery object
     * @return void
     */


    function unblock(item) {
      if (typeof $.fn.unblock !== 'undefined') {
        item.stop(true).css('opacity', '1').unblock();
        $('.tooltip__expanded').removeClass().addClass('tooltip__hidden');
      }
    }
    /**
     * Check if cookies are enabled
     *
     * @return bool
     */


    function is_cookie_enabled() {
      if (navigator.cookieEnabled) {
        return true;
      } // set and read cookie.


      document.cookie = 'cookietest=1';
      var ret = document.cookie.indexOf('cookietest=') !== -1; // delete cookie.

      document.cookie = 'cookietest=1; expires=Thu, 01-Jan-1970 00:00:01 GMT';
      return ret;
    }

    function setCookie(cookie_name, value) {
      var exdate = new Date();
      exdate.setDate(exdate.getDate() + 365 * 25);
      document.cookie = cookie_name + "=" + escape(value) + "; expires=" + exdate.toUTCString() + "; path=/";
    }

    function getCookie(cookie_name) {
      if (document.cookie.length > 0) {
        cookie_start = document.cookie.indexOf(cookie_name + "=");

        if (cookie_start != -1) {
          cookie_start = cookie_start + cookie_name.length + 1;
          cookie_end = document.cookie.indexOf(";", cookie_start);

          if (cookie_end == -1) {
            cookie_end = document.cookie.length;
          }

          return unescape(document.cookie.substring(cookie_start, cookie_end));
        }
      }

      return "";
    }
    /**
     * Retrieve fragments that need to be refreshed in the page
     *
     * @param search string Ref to search among all fragments in the page
     * @return object Object containing a property for each fragment that matches search
     */


    function retrieve_fragments(search) {
      var options = {},
          fragments = null;

      if (search) {
        if (_typeof2(search) === 'object') {
          search = $.extend({
            fragments: null,
            s: '',
            container: $(document),
            firstLoad: false
          }, search);

          if (!search.fragments) {
            fragments = search.container.find('.wlfmc-wishlist-fragment');
          } else {
            fragments = search.fragments;
          }

          if (search.s) {
            fragments = fragments.not('[data-fragment-ref]').add(fragments.filter('[data-fragment-ref="' + search.s + '"]'));
          }

          if (search.firstLoad) {
            fragments = fragments.filter('.on-first-load');
          }
        } else {
          fragments = $('.wlfmc-wishlist-fragment');

          if (typeof search === 'string' || typeof search === 'number') {
            fragments = fragments.not('[data-fragment-ref]').add(fragments.filter('[data-fragment-ref="' + search + '"]'));
          }
        }
      } else {
        fragments = $('.wlfmc-wishlist-fragment');
      }

      if (fragments.length) {
        fragments.each(function () {
          var t = $(this),
              id = t.attr('class').split(' ').filter(function (val) {
            return val.length && val !== 'exists';
          }).join(wlfmc_l10n.fragments_index_glue);
          options[id] = t.data('fragment-options');
        });
      } else {
        return null;
      }

      return options;
    }
    /**
     * Load fragments on page loading
     *
     * @param search string Ref to search among all fragments in the page
     * @param success function
     * @param successArgs array
     */


    function load_fragments(search, _success, successArgs) {
      clearTimeout(fragmenttimeout);
      fragmenttimeout = setTimeout(function () {
        if (fragmentxhr) {
          fragmentxhr.abort();
        }

        search = $.extend({
          firstLoad: true
        }, search);
        var fragments = retrieve_fragments(search); // create a new FormData object.

        var formData = new FormData();
        formData.append('action', wlfmc_l10n.actions.load_fragments);
        formData.append('nonce', wlfmc_l10n.ajax_nonce.load_fragments_nonce);
        formData.append('context', 'frontend');

        if (fragments) {
          // convert object to JSON string.
          var fragmentJson = JSON.stringify(fragments); // create a file from JSON string.

          var file = new File([fragmentJson], 'fragment.json');
          formData.append('fragments_file', file);
        }

        fragmentxhr = $.ajax({
          url: wlfmc_l10n.ajax_url,
          data: formData,
          type: 'POST',
          contentType: false,
          processData: false,
          beforeSend: function beforeSend(xhr) {
            if (wlfmc_l10n.ajax_mode === 'rest_api') {
              xhr.setRequestHeader('X-WP-Nonce', wlfmc_l10n.nonce);
            }
          },
          success: function success(data) {
            if (typeof data.fragments !== 'undefined') {
              if (typeof _success === 'function') {
                _success.apply(null, successArgs);
              }

              replace_fragments(data.fragments);
              init_handling_after_ajax(); // $( document ).trigger( 'wlfmc_fragments_loaded', [fragments, data.fragments, search.firstLoad] );
            }

            if (typeof data.products !== 'undefined') {
              set_products_hash(JSON.stringify(data.products));
            }

            if (typeof data.waitlist !== 'undefined') {
              set_waitlist_hash(JSON.stringify(data.waitlist));
            }

            if (typeof data.lang !== 'undefined') {
              set_lang_hash(data.lang);
            }
          }
        });
      }, 100);
    }
    /**
     * Replace fragments with template received
     *
     * @param fragments array Array of fragments to replace
     */


    function replace_fragments(fragments) {
      $.each(fragments, function (i, v) {
        var itemSelector = '.' + i.split(wlfmc_l10n.fragments_index_glue).filter(function (val) {
          return val.length && val !== 'exists' && val !== 'with-count';
        }).join('.'),
            toReplace = $(itemSelector); // find replace template.

        var replaceWith = $(v).filter(itemSelector);

        if (!replaceWith.length) {
          replaceWith = $(v).find(itemSelector);
        }

        if (toReplace.length && replaceWith.length) {
          toReplace.replaceWith(replaceWith);
        }
      });
    }
    /**
     * Check if device is an IOS device
     */


    function isOS() {
      return navigator.userAgent.match(/ipad|iphone/i);
    }
    /**
     * Check if passed value could be considered true
     */


    function isTrue(value) {
      return true === value || 'yes' === value || '1' === value || 1 === value || 'true' === value;
    }

    function validateEmail(email) {
      var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return re.test(String(email).toLowerCase());
    }

    function updateURLParameter(url, param, paramVal) {
      var newAdditionalURL = "";
      var tempArray = url.split("?");
      var baseURL = tempArray[0];
      var additionalURL = tempArray[1];
      var temp = "";

      if (additionalURL) {
        tempArray = additionalURL.split("&");

        for (var i = 0; i < tempArray.length; i++) {
          if (tempArray[i].split('=')[0] !== param) {
            newAdditionalURL += temp + tempArray[i];
            temp = "&";
          }
        }
      }

      var rows_txt = temp + "" + param + "=" + paramVal.replace('#', '');
      return baseURL + "?" + newAdditionalURL + rows_txt;
    }

    function getUrlParameter(url, sParam) {
      var sPageURL = decodeURIComponent(url.substring(1)),
          sURLVariables = sPageURL.split(/[&|?]+/),
          sParameterName,
          i;

      for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
          return sParameterName[1] === undefined ? true : sParameterName[1];
        }
      }
    }

    function check_products(products) {
      if (null !== products) {
        product_in_list = [];
        var counter_items = $('.wlfmc-products-counter-wrapper .wlfmc-counter-item');

        if (counter_items.length && product_in_list.length) {
          counter_items.each(function () {
            var p_id = $(this).attr('data-row-id');

            if (!$.grep(product_in_list, function (item) {
              return item.product_id === p_id;
            }).length) {
              $('.wlfmc-products-counter-wrapper').find('[data-row-id="' + p_id + '"]').remove();
            }
          });
        }

        var table_items = $('.wlfmc-wishlist-form .wlfmc-table-item');

        if (table_items.length && product_in_list.length) {
          table_items.each(function () {
            var p_id = $(this).attr('data-row-id');

            if (!$.grep(product_in_list, function (item) {
              return item.product_id === p_id;
            }).length) {
              $('.wlfmc-wishlist-form').find('[data-row-id="' + p_id + '"]').remove();
            }
          });
        }

        $('.wlfmc-add-to-wishlist').removeClass('exists');
        $.each(products, function (id, itemData) {
          var same_products = $('.wlfmc-add-to-wishlist-' + itemData.product_id);
          same_products.each(function () {
            $(this).addClass('exists');
            $(this).find('.wlfmc_delete_item').attr('data-item-id', itemData.item_id);
            $(this).find('.wlfmc_delete_item').attr('data-wishlist-id', itemData.wishlist_id);
          });
          $('.wlfmc-products-counter-wrapper  .products-counter-number').text(itemData.length);
          $('.wlfmc-products-counter-wishlist .total-products .wlfmc-total-count').text(itemData.length);
          product_in_list.push(itemData);
        });
      }
    }

    function check_waitlist_products(products) {
      if (null !== products) {
        product_in_waitlist = [];
        var counter_items = $('.wlfmc-waitlist-counter-wrapper .wlfmc-counter-item');

        if (counter_items.length && product_in_waitlist.length) {
          counter_items.each(function () {
            var p_id = $(this).attr('data-row-id');

            if (!$.grep(product_in_waitlist, function (item) {
              return item.product_id === p_id;
            }).length) {
              $('.wlfmc-waitlist-counter-wrapper').find('[data-row-id="' + p_id + '"]').remove();
            }
          });
        }

        var table_items = $('.wlfmc-waitlist-table .wlfmc-table-item');

        if (table_items.length && product_in_waitlist.length) {
          table_items.each(function () {
            var p_id = $(this).attr('data-row-id');

            if (!$.grep(product_in_waitlist, function (item) {
              return item.product_id === p_id;
            }).length) {
              $('.wlfmc-waitlist-table').find('[data-row-id="' + p_id + '"]').remove();
            }
          });
        }

        $('.wlfmc-add-to-outofstock').removeClass('exists');
        $('.wlfmc-add-to-waitlist').removeClass('exists');
        $.each(products, function (id, itemData) {
          var same_products = $('.wlfmc-add-to-waitlist-' + itemData.product_id);
          same_products.each(function () {
            var popup = $('#' + $(this).data('popup-id'));

            if (isTrue(itemData.price_change) || isTrue(itemData.on_sale) || isTrue(itemData.low_stock) || isTrue(itemData.back_in_stock) && popup.find('input[name="list_back-in-stock"]').length > 0) {
              $(this).addClass('exists');
            }

            if (isTrue(itemData.back_in_stock)) {
              popup.find('input[name="list_back-in-stock"]').prop('checked', true);
            } else {
              popup.find('input[name="list_back-in-stock"]').prop('checked', false);
            }

            if (isTrue(itemData.price_change)) {
              popup.find('input[name="list_price-change"]').prop('checked', true);
            } else {
              popup.find('input[name="list_price-change"]').prop('checked', false);
            }

            if (isTrue(itemData.on_sale)) {
              popup.find('input[name="list_on-sale"]').prop('checked', true);
            } else {
              popup.find('input[name="list_on-sale"]').prop('checked', false);
            }

            if (isTrue(itemData.low_stock)) {
              popup.find('input[name="list_low-stock"]').prop('checked', true);
            } else {
              popup.find('input[name="list_low-stock"]').prop('checked', false);
            }
          });
          var outofstockbox = $('.wlfmc-add-to-outofstock-' + itemData.product_id);
          outofstockbox.each(function () {
            if (isTrue(itemData.back_in_stock)) {
              $(this).addClass('exists');
            }
          });
          $('.wlfmc-waitlist-counter-wrapper  .products-counter-number').text(itemData.length);
          $('.wlfmc-products-counter-waitlist .total-products .wlfmc-total-count').text(itemData.length);
          product_in_waitlist.push(itemData);
        });
      }
    }
    /* Storage Handling */


    var $supports_html5_storage = true,
        wishlist_hash_key = wlfmc_l10n.wishlist_hash_key,
        products_hash_key = wishlist_hash_key + '_products',
        waitlist_hash_key = wishlist_hash_key + '_waitlist',
        lang_hash_key = wishlist_hash_key + '_lang';

    try {
      $supports_html5_storage = 'sessionStorage' in window && window.sessionStorage !== null;
      window.sessionStorage.setItem('wlfmc', 'test');
      window.sessionStorage.removeItem('wlfmc');
      window.localStorage.setItem('wlfmc', 'test');
      window.localStorage.removeItem('wlfmc');
    } catch (err) {
      $supports_html5_storage = false;
    }
    /** Set the wishlist hash in both session and local storage */


    function set_products_hash(products) {
      if ($supports_html5_storage) {
        localStorage.setItem(products_hash_key, products);
        sessionStorage.setItem(products_hash_key, products);
      }

      check_products(JSON.parse(products));
    }
    /** Set the waitlist hash in both session and local storage */


    function set_waitlist_hash(products) {
      if ($supports_html5_storage) {
        localStorage.setItem(waitlist_hash_key, products);
        sessionStorage.setItem(waitlist_hash_key, products);
      }

      check_waitlist_products(JSON.parse(products));
    }

    function set_lang_hash(lang) {
      if ($supports_html5_storage) {
        localStorage.setItem(lang_hash_key, lang);
        sessionStorage.setItem(lang_hash_key, lang);
      }
    }

    if (wlfmc_l10n.is_cache_enabled) {
      table_block();
    }
    /* Wishlist Handling */


    if ($supports_html5_storage) {
      // Refresh when storage changes in another tab.
      $(window).on('storage onstorage', function (e) {
        if (products_hash_key === e.originalEvent.key && localStorage.getItem(products_hash_key) !== sessionStorage.getItem(products_hash_key) || waitlist_hash_key === e.originalEvent.key && localStorage.getItem(waitlist_hash_key) !== sessionStorage.getItem(waitlist_hash_key)) {
          load_fragments();
        }
      }); // Refresh when page is shown after back button (safari).

      $(window).on('pageshow', function (e) {
        if (e.originalEvent.persisted) {
          load_fragments();
        }
      });

      try {
        if (wlfmc_l10n.is_cache_enabled) {
          throw 'Need Update wishlist data';
        }

        if (wlfmc_l10n.update_wishlists_data || null !== lang && lang !== localStorage.getItem(lang_hash_key) || localStorage.getItem(products_hash_key) !== JSON.stringify(wishlist_items) || localStorage.getItem(waitlist_hash_key) !== JSON.stringify(waitlist_items)) {
          localStorage.setItem(products_hash_key, '');
          localStorage.setItem(waitlist_hash_key, '');
          localStorage.setItem(lang_hash_key, '');
          check_products(wishlist_items);
          check_waitlist_products(waitlist_items);
          throw 'Need Update wishlist data';
        }

        if (localStorage.getItem(products_hash_key)) {
          var data = JSON.parse(localStorage.getItem(products_hash_key));

          if ('object' === _typeof(data) && null !== data) {
            check_products(data);
          }
        }

        if (localStorage.getItem(waitlist_hash_key)) {
          var data = JSON.parse(localStorage.getItem(waitlist_hash_key));

          if ('object' === _typeof(data) && null !== data) {
            check_waitlist_products(data);
          }
        }

        unblock($('.wlfmc-wishlist-table-wrapper, .wlfmc-save-for-later-table-wrapper'));
      } catch (err) {
        console.log(err);
        load_fragments();
      }
    } else {
      load_fragments();
    } // Customer support.


    var hasSelectiveRefresh = 'undefined' !== typeof wp && wp.customize && wp.customize.selectiveRefresh && wp.customize.widgetsPreview && wp.customize.widgetsPreview.WidgetPartial;

    if (hasSelectiveRefresh) {
      wp.customize.selectiveRefresh.bind('partial-content-rendered', function () {
        load_fragments();
      });
    }
  });
})(jQuery);