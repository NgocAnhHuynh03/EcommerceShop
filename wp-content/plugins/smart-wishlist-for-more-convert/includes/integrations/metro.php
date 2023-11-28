<?php
/**
 * WLFMC wishlist integration with metro theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_metro_integrate' );

/**
 * Integration with metro theme
 *
 * @return void
 */
function wlfmc_metro_integrate() {

	if ( class_exists( 'Metro_Main' ) ) {

		add_filter( 'wlfmc_button_positions', 'wlfmc_metro_fix_single_position' );
	}

}

/**
 * Fix single position
 *
 * @param array $positions all single positions.
 *
 * @return array
 */
function wlfmc_metro_fix_single_position( array $positions ): array {

	if ( class_exists( 'WooProductVariationGallery' ) ) {
		$positions['image_top_right']    = array(
			'hook'     => 'rtwpvg_product_badge',
			'priority' => 11,
		);
		$positions['image_bottom_right'] = array(
			'hook'     => 'rtwpvg_product_badge',
			'priority' => 11,
		);
		$positions['image_top_left']     = array(
			'hook'     => 'rtwpvg_product_badge',
			'priority' => 1,
		);
		$positions['image_bottom_left']  = array(
			'hook'     => 'rtwpvg_product_badge',
			'priority' => 11,
		);
	} else {
		$positions['image_top_right']    = array(
			'hook'     => 'woocommerce_before_single_product_summary',
			'priority' => 11,
		);
		$positions['image_bottom_right'] = array(
			'hook'     => 'woocommerce_before_single_product_summary',
			'priority' => 11,
		);
		$positions['image_top_left']     = array(
			'hook'     => 'woocommerce_before_single_product_summary',
			'priority' => 1,
		);
		$positions['image_bottom_left']  = array(
			'hook'     => 'woocommerce_before_single_product_summary',
			'priority' => 11,
		);
	}

	return $positions;
}

