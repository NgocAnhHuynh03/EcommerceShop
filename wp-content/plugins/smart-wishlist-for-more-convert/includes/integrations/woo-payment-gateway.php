<?php
/**
 * WLFMC wishlist integration with Braintree For WooCommerce plugin
 *
 * @plugin_name Braintree For WooCommerce
 * @version 3.2.41
 * @slug woo-payment-gateway
 * @url https://wordpress.org/plugins/woo-payment-gateway/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action( 'init', 'wlfmc_woo_payment_gateway_integrate' );

/**
 * Integration with Braintree For WooCommerce plugin
 *
 * @return void
 */
function wlfmc_woo_payment_gateway_integrate() {

	if ( defined( 'WC_BRAINTREE_PLUGIN_NAME' ) ) {
		add_filter( 'wlfmc_remove_args_from_posted_data', 'wlfmc_fix_woo_payment_gateway_posted_data' );
	}
}

/**
 * Remove unused posted key
 *
 * @param array $args arguments.
 *
 * @return array
 */
function wlfmc_fix_woo_payment_gateway_posted_data( $args ) {
	foreach ( $_POST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification
		if ( strpos( $key, 'shipping_' ) === 0 || strpos( $key, 'billing_' ) === 0 || strpos( $key, 'wc_braintree_' ) === 0 ) {
			$args[] = sanitize_key( $key );
		}
	}
	return $args;
}
