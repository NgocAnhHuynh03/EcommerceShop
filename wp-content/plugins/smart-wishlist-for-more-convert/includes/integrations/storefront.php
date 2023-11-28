<?php
/**
 * WLFMC wishlist integration with storefront theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_storefront_integrate' );

/**
 * Integration with StoreFront theme
 *
 * @return void
 */
function wlfmc_storefront_integrate() {

	if ( class_exists( 'Storefront' ) ) {

		add_filter( 'wlfmc_loop_positions', 'wlfmc_storefront_fix_loop_position' );

	}

}

/**
 * Fix loop position
 *
 * @param array $positions all loop positions.
 *
 * @return array
 */
function wlfmc_storefront_fix_loop_position( array $positions ): array {

	$positions['image_top_left']['hook']  = 'woocommerce_after_shop_loop_item';
	$positions['image_top_right']['hook'] = 'woocommerce_after_shop_loop_item';

	return $positions;
}

