<?php
/**
 * WLFMC wishlist integration with ShopLentor – WooCommerce Builder for Elementor & Gutenberg
 *
 * @plugin_name ShopLentor – WooCommerce Builder for Elementor & Gutenberg
 * @version 2.6.2
 * @slug shoplentor
 * @url https://wordpress.org/plugins/woolentor-addons/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.5.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_shoplentor_integrate' );

/**
 * Integration with Shoplentor plugin
 *
 * @return void
 */
function wlfmc_shoplentor_integrate() {

	if ( defined( 'WOOLENTOR_VERSION' ) && function_exists( 'woolentor_get_option' ) && wlfmc_is_true( woolentor_get_option( 'wishlist', 'woolentor_others_tabs', 'off' ) ) ) {
		add_filter( 'woolentor_has_wishlist_plugin', '__return_true' );
		add_filter( 'woolentor_add_to_wishlist_output', 'wlfmc_shoplentor_button', 10 );
	}
}

/**
 * Add wishlist button to shoplentor
 *
 * @param string $output html output.
 *
 * @return string
 */
function wlfmc_shoplentor_button( $output ) {
	$html = do_shortcode( '[wlfmc_add_to_wishlist]' );

	$before_html = wlfmc_get_action_output( 'wlfmc_before_shoplentor_wishlist_button' );
	$after_html  = wlfmc_get_action_output( 'wlfmc_after_shoplentor_wishlist_button' );

	if ( ! empty( $before_html ) ) {
		$before_html .= '</li><li>';
	}
	if ( ! empty( $after_html ) ) {
		$html .= '</li><li>';
	}

	return $before_html . $html . $after_html;
}
