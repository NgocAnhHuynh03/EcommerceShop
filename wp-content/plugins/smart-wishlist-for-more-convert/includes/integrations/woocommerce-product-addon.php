<?php
/**
 * WLFMC wishlist integration with PPOM for WooCommerce plugin
 *
 * @plugin_name WooCommerce Product Add-ons
 * @version 32.0.1
 * @slug woocommerce-product-addon
 * @url https://themeisle.com/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_woocommerce_product_addon_integrate' );

/**
 * Integration with PPOM for WooCommerce plugin
 *
 * @return void
 */
function wlfmc_woocommerce_product_addon_integrate() {

	if ( defined( 'PPOM_VERSION' ) ) {
		add_filter( 'wlfmc_wishlist_item_price', 'wlfmc_ppom_wishlist_item_price', 9, 4 );
	}
}


/**
 *  Modify wishlist item price for woocommerce product bundles
 *
 * @param string              $price item price.
 * @param null|array          $product_meta product meta.
 * @param WC_Product          $product  $product Woocommerce Product.
 * @param WLFMC_Wishlist_Item $item Wishlist item object.
 *
 * @throws Exception Exception.
 * @return float|int|mixed
 */
function wlfmc_ppom_wishlist_item_price( $price, $product_meta, $product, $item ) {
	if ( is_object( $product ) && isset( $product_meta['ppom'] ) && function_exists( 'ppom_price_controller' ) ) {
		$cart_item = ppom_price_controller( $item->get_cart_item(), $product_meta );
		$price     = $cart_item['data']->get_price();
	}
	return $price;
}
