<?php
/**
 * WLFMC wishlist integration with Nasa Core plugin
 *
 * @plugin_name Nasa Core
 * @version 2.3.7
 * @slug nasa-core
 * @url https://nasatheme.com
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action( 'init', 'wlfmc_nasa_core_integrate' );

/**
 * Integration with Nasa Core plugin
 *
 * @return void
 */
function wlfmc_nasa_core_integrate() {

	if ( defined( 'NASA_CORE_ACTIVED' ) ) {
		add_filter( 'wlfmc_remove_args_from_posted_data', 'wlfmc_fix_nasa_core_posted_data' );
	}
}

/**
 * Remove unused posted key
 *
 * @param array $args arguments.
 *
 * @return array
 */
function wlfmc_fix_nasa_core_posted_data( $args ) {
	$nasa = false;
	foreach ( $_POST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification
		if ( strpos( $key, 'nasa' ) === 0 ) {
			$nasa   = true;
			$args[] = sanitize_key( $key );
		}
	}

	if ( $nasa ) {
		$args[] = 'data-product_id';
		$args[] = 'data-type';
		$args[] = 'data-from_wishlist';
	}
	return $args;
}
