<?php
/**
 * WLFMC wishlist integration with WPC Variations Radio Buttons for WooCommerce plugin
 *
 * @plugin_name WPC Variations Radio Buttons for WooCommerce
 * @version 3.2.0
 * @slug wpc-variations-radio-buttons
 * @url https://wordpress.org/plugins/wpc-variations-radio-buttons/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action( 'init', 'wlfmc_wpc_variations_radio_buttons_integrate' );

/**
 * Integration with WPC Variations Radio Buttons for WooCommerce plugin
 *
 * @return void
 */
function wlfmc_wpc_variations_radio_buttons_integrate() {

	if ( defined( 'WOOVR_VERSION' ) ) {
		add_filter( 'wlfmc_remove_args_from_posted_data', 'wlfmc_fix_wpc_variations_radio_buttons_posted_data' );
	}
}

/**
 * Remove unused posted key
 *
 * @param array $args arguments.
 *
 * @return array
 */
function wlfmc_fix_wpc_variations_radio_buttons_posted_data( $args ) {
	foreach ( $_POST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification
		if ( strpos( $key, 'woovr_' ) === 0 ) {
			$args[] = sanitize_key( $key );
		}
	}
	return $args;
}
