<?php
/**
 * WLFMC wishlist integration with Additional Variation Images Gallery for WooCommerce plugin
 *
 * @plugin_name Additional Variation Images Gallery for WooCommerce
 * @version 1.3.17
 * @slug woo-variation-gallery
 * @url  https://wordpress.org/plugins/woo-variation-gallery/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.5.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'wlfmc_woo_variation_gallery_integrate' );

/**
 * Integration with Additional Variation Images Gallery for WooCommerce plugin
 *
 * @return void
 */
function wlfmc_woo_variation_gallery_integrate() {

	if ( class_exists( 'Woo_Variation_Gallery' ) ) {
		add_filter( 'wlfmc_button_positions', 'wlfmc_woo_variation_gallery_button_position' );
	}
}


/**
 * Fix single position
 *
 * @param array $positions all loop positions.
 *
 * @return array
 */
function wlfmc_woo_variation_gallery_button_position( array $positions ): array {

	if ( isset( $positions['image_bottom_left']['hook'] ) ) {
		$positions['image_bottom_left'] = array(
			array(
				'hook'     => $positions['image_bottom_left']['hook'],
				'priority' => $positions['image_bottom_left']['priority'],
			),
			array(
				'hook'     => 'woo_variation_product_gallery_slider_start',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['image_bottom_left'] ) ) {
		$positions['image_bottom_left'][] = array(
			'hook'     => 'woo_variation_product_gallery_slider_start',
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
				'hook'     => 'woo_variation_product_gallery_slider_start',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['image_bottom_right'] ) ) {
		$positions['image_bottom_right'][] = array(
			'hook'     => 'woo_variation_product_gallery_slider_start',
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
				'hook'     => 'woo_variation_product_gallery_slider_start',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['image_top_left'] ) ) {
		$positions['image_top_left'][] = array(
			'hook'     => 'woo_variation_product_gallery_slider_start',
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
				'hook'     => 'woo_variation_product_gallery_slider_start',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['image_top_right'] ) ) {
		$positions['image_top_right'][] = array(
			'hook'     => 'woo_variation_product_gallery_slider_start',
			'priority' => 10,
		);
	}

	return $positions;
}

