<?php
/**
 * WLFMC wishlist integration with Ultimate Addons for Elementor plugin
 *
 * @plugin_name  Ultimate Addons for Elementor
 * @version 1.36.7
 * @slug ultimate-elementor
 * @url  https://ultimateelementor.com/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.5.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'wlfmc_ultimate_elementor_integrate' );

/**
 * Integration with Ultimate Addons for Elementor plugin
 *
 * @return void
 */
function wlfmc_ultimate_elementor_integrate() {

	if ( defined( 'UAEL_FILE' ) ) {
		add_filter( 'wlfmc_loop_positions', 'wlfmc_ultimate_elementor_fix_loop_position' );
	}
}


/**
 * Fix loop position
 *
 * @param array $positions all loop positions.
 *
 * @return array
 */
function wlfmc_ultimate_elementor_fix_loop_position( array $positions ): array {

	if ( isset( $positions['before_add_to_cart']['hook'] ) ) {
		$positions['before_add_to_cart'] = array(
			array(
				'hook'     => $positions['before_add_to_cart']['hook'],
				'priority' => $positions['before_add_to_cart']['priority'],
			),
			array(
				'hook'     => 'uael_woo_products_add_to_cart_before',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['before_add_to_cart'] ) ) {
		$positions['before_add_to_cart'][] = array(
			'hook'     => 'uael_woo_products_add_to_cart_before',
			'priority' => 10,
		);
	}

	if ( isset( $positions['after_add_to_cart']['hook'] ) ) {
		$positions['after_add_to_cart'] = array(
			array(
				'hook'     => $positions['after_add_to_cart']['hook'],
				'priority' => $positions['after_add_to_cart']['priority'],
			),
			array(
				'hook'     => 'uael_woo_products_add_to_cart_after',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['after_add_to_cart'] ) ) {
		$positions['after_add_to_cart'][] = array(
			'hook'     => 'uael_woo_products_add_to_cart_after',
			'priority' => 10,
		);
	}

	if ( isset( $positions['image_top_left']['hook'] ) ) {
		$positions['image_top_left'] = array(
			array(
				'hook'     => $positions['image_top_left']['hook'],
				'priority' => $positions['image_top_left']['priority'],
			),
			array(
				'hook'     => 'uael_woo_products_before_summary_wrap',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['image_top_left'] ) ) {
		$positions['image_top_left'][] = array(
			'hook'     => 'uael_woo_products_before_summary_wrap',
			'priority' => 10,
		);
	}

	if ( isset( $positions['image_top_right']['hook'] ) ) {
		$positions['image_top_right'] = array(
			array(
				'hook'     => $positions['image_top_right']['hook'],
				'priority' => $positions['image_top_right']['priority'],
			),
			array(
				'hook'     => 'uael_woo_products_before_summary_wrap',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['image_top_right'] ) ) {
		$positions['image_top_right'][] = array(
			'hook'     => 'uael_woo_products_before_summary_wrap',
			'priority' => 10,
		);
	}

	return $positions;
}
