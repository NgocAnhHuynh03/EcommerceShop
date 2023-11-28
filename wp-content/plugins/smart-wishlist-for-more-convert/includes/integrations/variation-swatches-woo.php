<?php
/**
 * WLFMC wishlist integration with Variation Swatches for WooCommerce plugin
 *
 * @plugin_name Variation Swatches for WooCommerce
 * @version 1.0.7
 * @slug variation-swatches-woo
 * @url https://wordpress.org/plugins/variation-swatches-woo/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.6.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'wlfmc_variation_swatches_woo_integrate' );

/**
 * Integration with Variation Swatches for WooCommerce plugin
 *
 * @return void
 */
function wlfmc_variation_swatches_woo_integrate() {

	if ( defined( 'CFVSW_VER' ) ) {
		add_action( 'wp_enqueue_scripts', 'wlfmc_add_to_wishlist_variation_swatches_woo', 100, 1 );
	}
}

/**
 * Add inline script to fix Variation Swatches for WooCommerce
 *
 * @return void
 */
function wlfmc_add_to_wishlist_variation_swatches_woo() {

	wp_add_inline_script(
		'wlfmc-main',
		"
		    jQuery(document).ready(function($) {
			  $(document).on('wlfmc_add_to_wishlist_data', function(e, el, data) {
			    var button = el;
			    var wrapper = button.closest('div.wlfmc-add-to-wishlist');
			    if (wrapper.hasClass('wlfmc-loop-btn')) {
			      var container = wrapper.closest('*.product');
			      if (container.find('.cfvsw_variations_form').length > 0) {
			        var attributes_field = container.find('.cfvsw-hidden-select select');
			        if (attributes_field.length > 0 ) {
			          attributes_field.each(function() {
			              if ( ''!== $(this).val() ) {
			              data[$(this).attr('name')] = $(this).val();
			              }
						});
			        } else {
			          console.log('Invalid Attributes:', attributes_field);
			        }
			      }
			    }
			  });
			  $(document).on('wlfmc_add_to_waitlist_data', function(e, el, data) {
			    var button = el;
			    var product_id = button.attr('data-parent-product-id');
			    var wrapper = $('div.cfvsw_variations_form[data-product_id=\"' + product_id + '\"]');
			   
			    if (wrapper.length > 0 ) {
			      var attributes_field = wrapper.find('.cfvsw-hidden-select select');
			        if (attributes_field.length > 0 ) {
			          attributes_field.each(function() {
			              if ( ''!== $(this).val() ) {
			              data[$(this).attr('name')] = $(this).val();
			              }
						});
			        } else {
			          console.log('Invalid Attributes:', attributes_field);
			        }
			    }
			  });
			  
			  $(document).on('wlfmc_add_to_multi_list_data', function(e, el, data) {
			    var button = el;
			    var product_id = button.attr('data-parent-product-id');
			    var wrapper = $('div.cfvsw_variations_form[data-product_id=\"' + product_id + '\"]');
			   
			    if (wrapper.length > 0 ) {
			      var attributes_field = wrapper.find('.cfvsw-hidden-select select');
			        if (attributes_field.length > 0 ) {
			          attributes_field.each(function() {
			              if ( ''!== $(this).val() ) {
			              data[$(this).attr('name')] = $(this).val();
			              }
						});
			        } else {
			          console.log('Invalid Attributes:', attributes_field);
			        }
			    }
			  });
			}); "
	);
}
