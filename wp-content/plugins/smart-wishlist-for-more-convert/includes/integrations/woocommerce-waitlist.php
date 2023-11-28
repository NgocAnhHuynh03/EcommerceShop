<?php
/**
 * WLFMC wishlist integration with WooCommerce Waitlist  plugin
 *
 * @plugin_name WooCommerce Waitlist
 * @version 2.3.5
 * @slug woocommerce-waitlist
 * @url https://woocommerce.com/document/woocommerce-waitlist/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action( 'init', 'wlfmc_woocommerce_waitlist_integrate' );

/**
 * Integration with WooCommerce Waitlist plugin
 *
 * @return void
 */
function wlfmc_woocommerce_waitlist_integrate() {

	if ( defined( 'WCWL_VERSION' ) ) {
		add_filter( 'wlfmc_remove_args_from_posted_data', 'wlfmc_fix_woocommerce_waitlist_posted_data' );
	}
}

/**
 * Remove unused posted key
 *
 * @param array $args arguments.
 *
 * @return array
 */
function wlfmc_fix_woocommerce_waitlist_posted_data( $args ) {
	foreach ( $_POST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification
		if ( strpos( $key, 'wcwl_' ) === 0 ) {
			$args[] = sanitize_key( $key );
		}
	}
	return $args;
}
