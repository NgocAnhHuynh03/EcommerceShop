<?php
/**
 * WLFMC wishlist integration with Woolementor plugin
 *
 * @plugin_name Woolementor
 * @version 3.9
 * @slug woolementor
 * @url https://wordpress.org/plugins/woolementor/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_woolementor_integrate' );

/**
 * Integration with Woolementor plugin
 *
 * @return void
 */
function wlfmc_woolementor_integrate() {

	if ( defined( 'WOOLEMENTOR' ) ) {
		add_filter( 'wlfmc_loop_positions', 'wlfmc_woolementor_fix_loop_position' );
		add_filter( 'wlfmc_custom_css_output', 'wlfmc_woolementor_fix_css' );
	}
}

/**
 * Fix loop position
 *
 * @param array $positions all loop positions.
 *
 * @return array
 */
function wlfmc_woolementor_fix_loop_position( array $positions ): array {

	if ( isset( $positions['before_add_to_cart']['hook'] ) ) {
		$positions['before_add_to_cart'] = array(
			array(
				'hook'     => $positions['before_add_to_cart']['hook'],
				'priority' => $positions['before_add_to_cart']['priority'],
			),
			array(
				'hook'     => 'codesigner_before_cart_button',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['before_add_to_cart'] ) ) {
		$positions['before_add_to_cart'][] = array(
			'hook'     => 'codesigner_before_cart_button',
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
				'hook'     => 'codesigner_after_cart_button',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['after_add_to_cart'] ) ) {
		$positions['after_add_to_cart'][] = array(
			'hook'     => 'codesigner_after_cart_button',
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
				'hook'     => 'codesigner_before_shop_loop_item',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['image_top_left'] ) ) {
		$positions['image_top_left'][] = array(
			'hook'     => 'codesigner_before_shop_loop_item',
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
				'hook'     => 'codesigner_before_shop_loop_item',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['image_top_right'] ) ) {
		$positions['image_top_right'][] = array(
			'hook'     => 'codesigner_before_shop_loop_item',
			'priority' => 10,
		);
	}

	return $positions;
}

/**
 * Generate css for Woolementor plugin
 *
 * @param string $generated_css generated css codes.
 *
 * @return string
 */
function wlfmc_woolementor_fix_css( string $generated_css ) {
	$generated_css .= '.wl-sc-single-product {position:relative;}';
	$generated_css .= '.wl .wl-sc-info-icons .wlfmc_position_before_add_to_cart > div, .wl .wl-sc-info-icons .wlfmc_position_after_add_to_cart > div { margin:0 !important;}';
	$generated_css .= '.wl .wl-sc-info-icons .wlfmc_position_before_add_to_cart > div a, .wl .wl-sc-info-icons .wlfmc_position_after_add_to_cart > div a { width: 35px !important;height: 35px !important;border-radius: 100%;';
	return $generated_css;
}
