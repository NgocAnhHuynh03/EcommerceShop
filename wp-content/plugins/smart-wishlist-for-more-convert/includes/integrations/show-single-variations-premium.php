<?php
/**
 * WLFMC wishlist integration with WooCommerce Show Single Variations by Iconic plugin
 *
 * @plugin_name WooCommerce Show Single Variations by Iconic
 * @version 1.10.0
 * @slug show-single-variations-premium
 * @url https://iconicwp.com/products/woocommerce-show-single-variations/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( class_exists( 'Iconic_WSSV_Product_Variation' ) ) {
	add_filter( 'wlfmc_before_wishlist_table', 'wlfmc_show_single_variations_premium_product_visibility_fix' );
}

/**
 * Fix product visibility
 *
 * @return void
 */
function wlfmc_show_single_variations_premium_product_visibility_fix() {
	add_filter( 'woocommerce_product_is_visible', '__return_true', 100, 2 );
}
