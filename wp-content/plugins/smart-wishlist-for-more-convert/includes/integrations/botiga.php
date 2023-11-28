<?php
/**
 * WLFMC wishlist integration with botiga theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.3.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_botiga_integrate' );

/**
 * Integration with Botiga theme
 *
 * @return void
 */
function wlfmc_botiga_integrate() {

	if ( function_exists( 'botiga_setup' ) ) {

		add_filter( 'wlfmc_button_positions', 'wlfmc_botiga_fix_single_position' );
		add_filter( 'wlfmc_loop_positions', 'wlfmc_botiga_fix_loop_position' );

		add_filter( 'botiga_quick_view_product_components', 'wlfmc_botiga_quick_view_components' );

	}

}

/**
 * Fix single position
 *
 * @param array $positions all single positions.
 *
 * @return array
 */
function wlfmc_botiga_fix_single_position( array $positions ): array {

	$positions['after_add_to_cart_button']  = array(
		array(
			'hook'     => 'woocommerce_after_add_to_cart_button',
			'priority' => - 1,
		),
		array(
			'hook'     => 'wlfmc_single_product_summary',
			'priority' => 1,
		),
	);
	$positions['before_add_to_cart_button'] = array(
		array(
			'hook'     => 'woocommerce_before_add_to_cart_button',
			'priority' => 90,
		),
		array(
			'hook'     => 'wlfmc_single_product_summary',
			'priority' => 1,
		),
	);

	$positions['summary'] = array(
		'hook'     => 'woocommerce_single_product_summary',
		'priority' => 31,
	);

	return $positions;
}

/**
 * Fix loop position
 *
 * @param array $positions all loop positions.
 *
 * @return array
 */
function wlfmc_botiga_fix_loop_position( array $positions ): array {

	$positions['before_add_to_cart'] = array(
		array(
			'hook'     => 'woocommerce_after_shop_loop_item',
			'priority' => 9,
		),

	);
	$positions['after_add_to_cart'] = array(
		array(
			'hook'     => 'woocommerce_after_shop_loop_item',
			'priority' => 10,
		),
	);

	return $positions;
}

/**
 * Fix quick view
 *
 * @param array $components all loop positions.
 *
 * @return array
 */
function wlfmc_botiga_quick_view_components( array $components ): array {
	$components[] = 'botiga_quick_view_summary_wlfmc_wishlist';

	return $components;
}

/**
 * Add shortcode to quick view
 *
 * @param WC_Product $product woocommerce product object.
 *
 * @return void
 */
function botiga_quick_view_summary_wlfmc_wishlist( WC_Product $product ) {

	if ( ! apply_filters( 'wlfmc_show_add_to_wishlist', true ) ) {
		return;
	}

	echo do_shortcode( '[wlfmc_add_to_wishlist product_id="' . $product->get_id() . '" is_single="true"]' );

}
