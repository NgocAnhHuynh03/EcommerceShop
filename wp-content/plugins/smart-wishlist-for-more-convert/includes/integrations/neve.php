<?php
/**
 * WLFMC wishlist integration with neve theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


add_action( 'after_setup_theme', 'wlfmc_neve' );
add_action( 'init', 'wlfmc_neve_integrate' );

/**
 * Integration with neve theme
 *
 * @return void
 */
function wlfmc_neve() {
	if ( function_exists( 'neve_run' ) ) {
		require_once MC_WLFMC_INC . 'integrations/neve/components/class-wishlist-counter.php';
		add_filter( 'hfg_theme_support_filter', 'wlfmc_neve_add_theme_support' );
		add_filter( 'hfg_template_locations', 'wlfmc_neve_add_module_template_location' );

	}
}

/**
 * Add module templates location
 *
 * @param array $locations the default templates locations.
 *
 * @return array
 */
function wlfmc_neve_add_module_template_location( $locations ) {
	$locations[] = MC_WLFMC_INC . 'integrations/neve/templates/';
	return $locations;
}

/**
 * Append to the theme support builders.
 *
 * @param array $theme_support The theme support array.
 *
 * @return array
 */
function wlfmc_neve_add_theme_support( $theme_support ) {
	if ( ! empty( $theme_support[0]['builders'] ) ) {
		$theme_support[0]['builders']['HFG\Core\Builder\Header'] = array_merge(
			$theme_support[0]['builders']['HFG\Core\Builder\Header'],
			array(
				'WLFMC_Neve\Wishlist_Counter',
			)
		);
	}
	return $theme_support;
}

/**
 * Integration with neve theme
 *
 * @return void
 */
function wlfmc_neve_integrate() {

	if ( function_exists( 'neve_run' ) ) {

		add_filter( 'wlfmc_loop_positions', 'wlfmc_neve_fix_loop_position' );
	}
	if ( class_exists( '\\Neve_Pro\\Core\\Loader' ) ) {
		add_filter( 'wlfmc_loop_positions', 'wlfmc_neve_pro_fix_loop_position' );
	}

}
/**
 * Fix loop position
 *
 * @param array $positions all loop positions.
 *
 * @return array
 */
function wlfmc_neve_fix_loop_position( array $positions ): array {

	$positions['image_top_left']['hook']      = 'woocommerce_before_shop_loop_item';
	$positions['image_top_left']['priority']  = 7;
	$positions['image_top_right']['hook']     = 'woocommerce_before_shop_loop_item';
	$positions['image_top_right']['priority'] = 7;

	return $positions;
}

/**
 * Fix loop position in PRO version
 *
 * @param array $positions all loop positions.
 *
 * @return array
 */
function wlfmc_neve_pro_fix_loop_position( array $positions ): array {

	$positions['image_top_left']['hook']         = 'woocommerce_before_shop_loop_item_title';
	$positions['image_top_left']['priority']     = 7;
	$positions['image_top_right']['hook']        = 'woocommerce_before_shop_loop_item_title';
	$positions['image_top_right']['priority']    = 7;
	$positions['before_add_to_cart']['hook']     = 'nv_shop_item_content_after';
	$positions['before_add_to_cart']['priority'] = 997;
	$positions['after_add_to_cart']['hook']      = 'nv_shop_item_content_after';
	$positions['after_add_to_cart']['priority']  = 999;

	return $positions;
}
