<?php
/**
 * WLFMC wishlist integration with Clever Swatches plugin
 *
 * @plugin_name Clever Swatches
 * @version 2.2.3
 * @slug clever-swatches
 * @url http://cleverswatches.wp3.zootemplate.com/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action( 'init', 'wlfmc_clever_swatches_integrate' );

/**
 * Integration with Clever Swatches plugin
 *
 * @return void
 */
function wlfmc_clever_swatches_integrate() {

	if ( class_exists( 'Zoo_Clever_Swatch_Install' ) ) {
		add_filter( 'wlfmc_remove_args_from_posted_data', 'wlfmc_fix_clever_swatches_posted_data' );
		add_action( 'wp_enqueue_scripts', 'wlfmc_add_to_wishlist_clever_swatches', 100, 1 );
	}
}

/**
 * Remove unused posted key
 *
 * @param array $args arguments.
 *
 * @return array
 */
function wlfmc_fix_clever_swatches_posted_data( $args ) {

	$args[] = 'old_variation_id';
	return $args;
}

/**
 * Add inline script to fix clever swatches
 *
 * @return void
 */
function wlfmc_add_to_wishlist_clever_swatches() {

	wp_add_inline_script(
		'wlfmc-main',
		"
	jQuery(document).ready(function($){
		  $(document).on('cleverswatch_update_gallery cleverswatch_update_cw_gallery',function (e, data) {
				if (data.product_id === data.variation_id){
					$(data.form_add_to_cart).trigger('hide_variation');
				} else {
					$(data.form_add_to_cart).trigger('show_variation', data, true);
				}
		  });
		  $(document).on('wlfmc_add_to_wishlist_data', function (e, el, data) {

		        var button = el;
		        var wrapper = button.closest('div.wlfmc-add-to-wishlist');
		        if (wrapper.hasClass('wlfmc-loop-btn')){
		            var container = wrapper.closest('*.product');
		            if ( container.find('a.add_to_cart_button').length > 0  ) {
		                if ( 'undefined' !== typeof container.find('a.add_to_cart_button').data('variation_id') ){
	                        data.add_to_wishlist = container.find('a.add_to_cart_button').data('variation_id');
			            }
			            if ( 'undefined' !== typeof container.find('a.add_to_cart_button').data('selected_options') ){
		                     $.each( container.find('a.add_to_cart_button').data('selected_options') , function ( key, value ) {
		                            data[key] = value
		                     });
			            }
		            }

		        }
		  });
		  $(document).on('cleverswatch_button_add_cart', function (e, data) {
		        if ( data.selector ) {

		            var add_to_cart_button = $(data.selector);
		            if ( 'undefined' !== typeof add_to_cart_button.data( 'variation_id' ) ) {

						var variation_data = {
							product_id : add_to_cart_button.data( 'product_id' ),
							variation_id : add_to_cart_button.data( 'variation_id' ),
							is_in_stock : true ,
						};
						$(document).trigger('wlfmc_show_variation', variation_data );
		            }

		        }
		  });
		   $(document).on('cleverswatch_button_out_stock', function (e, data) {
		        if ( data.selector ) {

		            var add_to_cart_button = $(data.selector);
		            if ( 'undefined' !== typeof add_to_cart_button.data( 'variation_id' ) ) {

						var variation_data = {
							product_id : add_to_cart_button.data( 'product_id' ),
							variation_id : add_to_cart_button.data( 'variation_id' ),
						};
						$(document).trigger('wlfmc_show_variation', variation_data );
		            }

		        }
		  });
    });
    "
	);
}


