<?php
/**
 * WLFMC wishlist integration with Quick Buy Now Button for WooCommerce plugin
 *
 * @plugin_name Quick Buy Now Button for WooCommerce
 * @version 1.3.6
 * @slug buy-now-button-for-woocommerce
 * @url https://woocommerce.com/products/quick-buy-now-button-for-woocommerce/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action( 'init', 'wlfmc_aqbp_integrate' );

/**
 * Integration with Quick Buy Now Button for WooCommerce plugin
 *
 * @return void
 */
function wlfmc_aqbp_integrate() {

	if ( class_exists( 'Class_Addify_Quick_Buy' ) ) {
		add_filter( 'wlfmc_remove_args_from_posted_data', 'wlfmc_fix_aqbp_posted_data' );
	}
}

/**
 * Remove unused posted key
 *
 * @param array $args arguments.
 *
 * @return array
 */
function wlfmc_fix_aqbp_posted_data( $args ) {
	foreach ( $_POST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification
		if ( strpos( $key, 'aqbp' ) === 0 || strpos( $key, 'afqb' ) === 0 ) {
			$args[] = sanitize_key( $key );
		}
	}
	return $args;
}
