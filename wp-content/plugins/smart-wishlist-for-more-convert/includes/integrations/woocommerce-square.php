<?php
/**
 * WLFMC wishlist integration with WooCommerce Square plugin
 *
 * @plugin_name WooCommerce Square
 * @version 3.2.0
 * @slug woocommerce-square
 * @url https://woocommerce.com/products/square/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action( 'init', 'wlfmc_woocommerce_square_integrate' );

/**
 * Integration with WooCommerce Square plugin
 *
 * @return void
 */
function wlfmc_woocommerce_square_integrate() {

	if ( class_exists( 'WooCommerce_Square_Loader' ) ) {
		add_filter( 'wlfmc_remove_args_from_posted_data', 'wlfmc_fix_woocommerce_square_posted_data' );
	}
}

/**
 * Remove unused posted key
 *
 * @param array $args arguments.
 *
 * @return array
 */
function wlfmc_fix_woocommerce_square_posted_data( $args ) {
	foreach ( $_POST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification
		if ( strpos( $key, 'nds-pmd' ) === 0 ) {
			$args[] = sanitize_key( $key );
		}
	}
	return $args;
}
