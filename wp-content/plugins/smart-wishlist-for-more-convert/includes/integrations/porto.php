<?php
/**
 * WLFMC wishlist integration with porto theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_porto_integrate' );

/**
 * Integration with Astra theme
 *
 * @return void
 */
function wlfmc_porto_integrate() {

	if ( function_exists( 'porto_setup' ) ) {

		add_filter( 'wlfmc_button_positions', 'wlfmc_porto_fix_single_position' );
		add_filter( 'woocommerce_single_product_image_html', 'wlfmc_porto_fix_single_on_image_positions', 9999, 2 );

	}

}

/**
 * Fix single position
 *
 * @param array $positions all single positions.
 *
 * @return array|
 */
function wlfmc_porto_fix_single_position( array $positions ): array {

	$positions['image_bottom_left']  = array();
	$positions['image_bottom_right'] = array();

	return $positions;
}

/**
 * Fix on image single position
 *
 * @param string $html    content.
 * @param int    $product_id product id.
 *
 * @return string|void
 */
function wlfmc_porto_fix_single_on_image_positions( $html, $product_id ) {

	if ( ! apply_filters( 'wlfmc_show_add_to_wishlist', true ) ) {
		return;
	}
	$options         = new MCT_Options( 'wlfmc_options' );
	$single_position = $options->get_option( 'wishlist_button_position', 'image_top_left' );
	if ( in_array( $single_position, array( 'image_bottom_left', 'image_bottom_right' ), true ) ) {
		ob_start();
		echo do_shortcode( '[wlfmc_add_to_wishlist product_id="' . $product_id . '" is_single="true"]' );
		$html .= ob_get_clean();
	}

	return $html;
}
