<?php
/**
 * WLFMC wishlist integration with Premium Addons for Elementor plugin
 *
 * @plugin_name Premium Addons for Elementor
 * @version 4.9.53
 * @slug premium-addons-for-elementor
 * @url  https://wordpress.org/plugins/premium-addons-for-elementor/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.5.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'wlfmc_premium_addons_for_elementor_integrate' );

/**
 * Integration with Premium Addons for Elementor plugin
 *
 * @return void
 */
function wlfmc_premium_addons_for_elementor_integrate() {

	if ( defined( 'PREMIUM_ADDONS_VERSION' ) ) {
		add_filter( 'wlfmc_loop_positions', 'wlfmc_premium_addons_for_elementor_fix_loop_position' );
		add_filter( 'wlfmc_custom_css_output', 'wlfmc_premium_addons_for_elementor_fix_css' );
	}
}


/**
 * Fix loop position
 *
 * @param array $positions all loop positions.
 *
 * @return array
 */
function wlfmc_premium_addons_for_elementor_fix_loop_position( array $positions ): array {

	if ( isset( $positions['before_add_to_cart']['hook'] ) ) {
		$positions['before_add_to_cart'] = array(
			array(
				'hook'     => $positions['before_add_to_cart']['hook'],
				'priority' => $positions['before_add_to_cart']['priority'],
			),
			array(
				'hook'     => 'pa_woo_product_before_cta',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['before_add_to_cart'] ) ) {
		$positions['before_add_to_cart'][] = array(
			'hook'     => 'pa_woo_product_before_cta',
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
				'hook'     => 'pa_woo_product_after_cta',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['after_add_to_cart'] ) ) {
		$positions['after_add_to_cart'][] = array(
			'hook'     => 'pa_woo_product_after_cta',
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
				'hook'     => 'pa_woo_product_after_details_wrap_end',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['image_top_left'] ) ) {
		$positions['image_top_left'][] = array(
			'hook'     => 'pa_woo_product_after_details_wrap_end',
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
				'hook'     => 'pa_woo_product_after_details_wrap_end',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['image_top_right'] ) ) {
		$positions['image_top_right'][] = array(
			'hook'     => 'pa_woo_product_after_details_wrap_end',
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
function wlfmc_premium_addons_for_elementor_fix_css( string $generated_css ) {
	$generated_css .= '.premium-woo-product-wrapper{ position:relative;}';
	return $generated_css;
}
