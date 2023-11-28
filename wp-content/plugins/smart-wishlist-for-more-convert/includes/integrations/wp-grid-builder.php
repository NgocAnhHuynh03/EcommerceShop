<?php
/**
 * WLFMC wishlist integration with WP Grid Builder plugin
 *
 * @plugin_name WP Grid Builder
 * @version 1.7.0
 * @slug wp-grid-builder
 * @url https://www.wpgridbuilder.com
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( defined( 'WPGB_VERSION' ) ) {

	/**
	 * Add custom block to show wishlist button.
	 *
	 * @param array $blocks wp grid blocks.
	 *
	 * @return array
	 */
	function wlfmc_wpgb_block_add_to_wishlist( $blocks ) {
		$blocks['wishlist_button'] = array(
			'name'            => __( 'Add To Wishlist', 'wc-wlfmc-wishlist' ),
			'render_callback' => 'wlfmc_wpgb_add_to_wishlist',
			'icon'            => MC_WLFMC_URL . 'assets/backend/images/heart.svg',
		);

		return $blocks;
	}

	add_filter( 'wp_grid_builder/blocks', 'wlfmc_wpgb_block_add_to_wishlist', 10, 1 );

	/**
	 * Output wishlist button.
	 */
	function wlfmc_wpgb_add_to_wishlist() {
		$post = wpgb_get_post();
		if ( ! isset( $post->post_type ) && 'product' !== $post->post_type ) {
			return;
		}

		global $product;
		$product = wc_get_product( $post->ID );

		// Output loop button.
		echo do_shortcode( '[wlfmc_add_to_wishlist  is_single="" position="shortcode" product_id="' . $post->ID . '"]' );
	}
}
