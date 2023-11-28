<?php
/**
 * WLFMC wishlist integration with GTM4WP plugin
 *
 * @plugin_name GTM4WP
 * @version 1.16.1
 * @slug duracelltomi-google-tag-manager
 * @url https://wordpress.org/plugins/duracelltomi-google-tag-manager/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action( 'init', 'wlfmc_gtm4wp_integrate' );

/**
 * Integration with GTM4WP plugin
 *
 * @return void
 */
function wlfmc_gtm4wp_integrate() {

	if ( defined( 'GTM4WP_PATH' ) ) {
		add_filter( 'wlfmc_remove_args_from_posted_data', 'wlfmc_fix_gtm4wp_posted_data' );
	}
}

/**
 * Remove unused posted key
 *
 * @param array $args arguments.
 *
 * @return array
 */
function wlfmc_fix_gtm4wp_posted_data( $args ) {
	foreach ( $_POST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification
		if ( strpos( $key, 'gtm4wp_' ) === 0 ) {
			$args[] = sanitize_key( $key );
		}
	}
	return $args;
}
