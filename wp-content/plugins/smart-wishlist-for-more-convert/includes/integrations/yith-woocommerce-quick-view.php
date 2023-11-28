<?php
/**
 * WLFMC wishlist integration with Yith woocommerce quick view plugin
 *
 * @plugin_name YITH WooCommerce Quick View
 * @version 1.20.0
 * @slug yith-woocommerce-quick-view
 * @url  https://yithemes.com/themes/plugins/yith-woocommerce-quick-view
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'wlfmc_yith_woocommerce_quick_view_integrate' );

/**
 * Integration with YITH WooCommerce Quick View plugin
 *
 * @return void
 */
function wlfmc_yith_woocommerce_quick_view_integrate() {

	if ( defined( 'YITH_WCQV' ) ) {
		add_filter( 'wlfmc_remove_args_from_posted_data', 'wlfmc_fix_yith_woocommerce_quick_view_posted_data' );
		add_action( 'yith_wcqv_product_image', 'yith_woocommerce_quick_view_wlfmc_wishlist', 5 );
	}
}

/**
 * Remove unused posted key
 *
 * @param array $args arguments.
 *
 * @return array
 */
function wlfmc_fix_yith_woocommerce_quick_view_posted_data( $args ) {
	foreach ( $_POST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification
		if ( strpos( $key, 'yith_' ) === 0 ) {
			$args[] = sanitize_key( $key );
		}
	}
	return $args;
}


/**
 * Add shortcode to quick view
 *
 * @return void
 */
function yith_woocommerce_quick_view_wlfmc_wishlist() {

	if ( ! apply_filters( 'wlfmc_show_add_to_wishlist', true ) ) {
		return;
	}

	$options         = new MCT_Options( 'wlfmc_options' );
	$single_position = $options->get_option( 'wishlist_button_position', 'image_top_left' );
	if ( in_array( $single_position, array( 'image_top_left', 'image_top_right', 'image_bottom_left', 'image_bottom_right' ), true ) ) {
		echo do_shortcode( '[wlfmc_add_to_wishlist is_single="true"]' );
	}

}

