<?php
/**
 * WLFMC wishlist integration with PowerPack Pro for Elementor plugin
 *
 * @plugin_name PowerPack Pro for Elementor
 * @version 2.9.17
 * @slug powerpack-elements
 * @url  http://powerpackelements.com
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.5.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'wlfmc_powerpack_elements_integrate' );

/**
 * Integration with PowerPack Pro for Elementor plugin
 *
 * @return void
 */
function wlfmc_powerpack_elements_integrate() {

	if ( defined( 'POWERPACK_ELEMENTS_VER' ) ) {
		add_filter( 'wlfmc_loop_positions', 'wlfmc_powerpack_elements_fix_loop_position' );
		add_filter( 'wlfmc_button_positions', 'wlfmc_powerpack_elements_fix_single_position' );
		add_filter( 'wlfmc_custom_css_output', 'wlfmc_powerpack_elements_fix_css' );
	}
}


/**
 * Fix loop position
 *
 * @param array $positions all loop positions.
 *
 * @return array
 */
function wlfmc_powerpack_elements_fix_loop_position( array $positions ): array {

	if ( isset( $positions['before_add_to_cart']['hook'] ) ) {
		$positions['before_add_to_cart'] = array(
			array(
				'hook'     => $positions['before_add_to_cart']['hook'],
				'priority' => $positions['before_add_to_cart']['priority'],
			),
			array(
				'hook'     => 'pp_woo_products_add_to_cart_before',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['before_add_to_cart'] ) ) {
		$positions['before_add_to_cart'][] = array(
			'hook'     => 'pp_woo_products_add_to_cart_before',
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
				'hook'     => 'pp_woo_products_add_to_cart_after',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['after_add_to_cart'] ) ) {
		$positions['after_add_to_cart'][] = array(
			'hook'     => 'pp_woo_products_add_to_cart_after',
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
				'hook'     => 'pp_woo_products_before_summary_wrap',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['image_top_left'] ) ) {
		$positions['image_top_left'][] = array(
			'hook'     => 'pp_woo_products_before_summary_wrap',
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
				'hook'     => 'pp_woo_products_before_summary_wrap',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['image_top_right'] ) ) {
		$positions['image_top_right'][] = array(
			'hook'     => 'pp_woo_products_before_summary_wrap',
			'priority' => 10,
		);
	}

	return $positions;
}


/**
 * Fix single position
 *
 * @param array $positions all loop positions.
 *
 * @return array
 */
function wlfmc_powerpack_elements_fix_single_position( array $positions ): array {

	if ( isset( $positions['image_bottom_left']['hook'] ) ) {
		$positions['image_bottom_left'] = array(
			array(
				'hook'     => $positions['image_bottom_left']['hook'],
				'priority' => $positions['image_bottom_left']['priority'],
			),
			array(
				'hook'     => 'pp_single_product_before_image_wrap',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['image_bottom_left'] ) ) {
		$positions['image_bottom_left'][] = array(
			'hook'     => 'pp_single_product_before_image_wrap',
			'priority' => 10,
		);
	}

	if ( isset( $positions['image_bottom_right']['hook'] ) ) {
		$positions['image_bottom_right'] = array(
			array(
				'hook'     => $positions['image_bottom_right']['hook'],
				'priority' => $positions['image_bottom_right']['priority'],
			),
			array(
				'hook'     => 'pp_single_product_before_image_wrap',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['image_bottom_right'] ) ) {
		$positions['image_bottom_right'][] = array(
			'hook'     => 'pp_single_product_before_image_wrap',
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
				'hook'     => 'pp_single_product_before_image_wrap',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['image_top_left'] ) ) {
		$positions['image_top_left'][] = array(
			'hook'     => 'pp_single_product_before_image_wrap',
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
				'hook'     => 'pp_single_product_before_image_wrap',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['image_top_right'] ) ) {
		$positions['image_top_right'][] = array(
			'hook'     => 'pp_single_product_before_image_wrap',
			'priority' => 10,
		);
	}

	return $positions;
}

/**
 * Generate css plugin
 *
 * @param string $generated_css generated css codes.
 *
 * @return string
 */
function wlfmc_powerpack_elements_fix_css( string $generated_css ) {
	$generated_css .= '.pp-woo-product-wrapper {position: relative;}';
	return $generated_css;
}
