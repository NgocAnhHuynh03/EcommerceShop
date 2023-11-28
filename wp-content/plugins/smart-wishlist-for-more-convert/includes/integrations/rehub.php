<?php
/**
 * WLFMC wishlist integration with rehub theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_rehub_integrate' );

/**
 * Integration with Rehub theme
 *
 * @return void
 */
function wlfmc_rehub_integrate() {

	if ( function_exists( 'rehub_theme_after_setup' ) ) {

		add_filter( 'wlfmc_loop_positions', 'wlfmc_rehub_fix_loop_position' );

		add_filter( 'wlfmc_button_positions', 'wlfmc_rehub_fix_single_position' );
	}

}

/**
 * Fix single position
 *
 * @param array $positions all single positions.
 *
 * @return array
 */
function wlfmc_rehub_fix_single_position( array $positions ): array {
	$positions['before_add_to_cart'] = array(
		'hook'     => 'rh_woo_single_product_price',
		'priority' => '10',
	);

	return $positions;
}

/**
 * Fixed loop positions
 *
 * @param array $positions all single positions.
 *
 * @return array
 */
function wlfmc_rehub_fix_loop_position( array $positions ): array {

	$positions['after_add_to_cart'] = array(
		'hook'     => 'rh_woo_button_loop',
		'priority' => '10',
	);

	return $positions;
}

