<?php
/**
 * WLFMC wishlist integration with Anti-Spam by CleanTalk plugin
 *
 * @plugin_name Anti-Spam by CleanTalk
 * @version 5.188
 * @slug cleantalk-spam-protect
 * @url https://wordpress.org/plugins/cleantalk-spam-protect/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action( 'init', 'wlfmc_apbct_integrate' );

/**
 * Integration with Anti-Spam by CleanTalk plugin
 *
 * @return void
 */
function wlfmc_apbct_integrate() {

	if ( defined( 'APBCT_NAME' ) ) {
		add_filter( 'wlfmc_remove_args_from_posted_data', 'wlfmc_fix_apbct_posted_data' );
	}
}

/**
 * Remove unused posted key
 *
 * @param array $args arguments.
 *
 * @return array
 */
function wlfmc_fix_apbct_posted_data( $args ) {
	foreach ( $_POST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification
		if ( strpos( $key, 'apbct_' ) === 0 ) {
			$args[] = sanitize_key( $key );
		}
	}
	return $args;
}
