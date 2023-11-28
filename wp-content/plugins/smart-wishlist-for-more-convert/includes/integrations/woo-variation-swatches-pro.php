<?php
/**
 * WLFMC wishlist integration with WooCommerce Variation Swatches Pro plugin
 *
 * @plugin_name WooCommerce Variation Swatches Pro
 * @version 2.0.12
 * @slug woo-variation-swatches-pro
 * @url https://getwooplugins.com/plugins/woocommerce-variation-swatches/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action( 'init', 'wlfmc_woo_variation_swatches_pro_integrate' );

/**
 * Integration with WooCommerce Variation Swatches Pro plugin
 *
 * @return void
 */
function wlfmc_woo_variation_swatches_pro_integrate() {

	if ( class_exists( 'Woo_Variation_Swatches_Pro' ) ) {
		add_action( 'wp_enqueue_scripts', 'wlfmc_add_to_wishlist_woo_variation_swatches_pro', 100, 1 );
	}
}

/**
 * Add inline script to fix WooCommerce Variation Swatches Pro
 *
 * @return void
 */
function wlfmc_add_to_wishlist_woo_variation_swatches_pro() {

	wp_add_inline_script(
		'wlfmc-main',
		"
		    jQuery(document).ready(function($) {
			  $(document).on('wlfmc_add_to_wishlist_data', function(e, el, data) {
			    var button = el;
			    var wrapper = button.closest('div.wlfmc-add-to-wishlist');
			    if (wrapper.hasClass('wlfmc-loop-btn')) {
			      var container = wrapper.closest('*.product');
			      if (container.find('a.add_to_cart_button').length > 0) {
			        var href = container.find('a.add_to_cart_button').attr('href');
			        if (href.startsWith('http') || href.startsWith('https') || href.startsWith('?')) {
			          var url, attributes;
			          if (href.startsWith('?')) {
			            // Remove the leading question mark
			            href = href.substring(1);
			          }
			          url = Object.fromEntries(new URLSearchParams(href));
			          attributes = {};
			          for (var attr_name in url) {
			            if ('attribute_' === attr_name.substring(0, 10)) {
			              data[attr_name] = url[attr_name];
			            }
			          }
			        } else {
			          console.log('Invalid URL:', href);
			        }
			      }
			    }
			  });
			});
    "
	);
}


