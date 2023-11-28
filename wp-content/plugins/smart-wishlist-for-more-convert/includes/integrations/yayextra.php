<?php
/**
 * WLFMC wishlist integration with YayExtra Lite - WooCommerce Extra Product Options ( free and premium)
 *
 * @plugin_name YayExtra Lite - WooCommerce Extra Product Options
 * @version 1.2.6
 * @slug yayextra
 * @url  https://yaycommerce.com/yayextra-woocommerce-extra-product-options
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.6.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'wlfmc_yayextra_integrate' );

/**
 * Integration with YayExtra Lite & Pro plugin
 *
 * @return void
 */
function wlfmc_yayextra_integrate() {

	if ( defined( 'YAYE_VERSION' ) ) {
		add_filter( 'wlfmc_wishlist_item_price', 'wlfmc_yayextra_item_price', 9, 4 );
	}

}

/**
 *  Modify wishlist item price for YayExtra Pro
 *
 * @param string              $price item price.
 * @param null|array          $product_meta product meta.
 * @param WC_Product          $product  $product Woocommerce Product.
 * @param WLFMC_Wishlist_Item $item Wishlist item object.
 *
 * @throws Exception Exception.
 * @return float|int|mixed
 */
function wlfmc_yayextra_item_price( $price, $product_meta, $product, $item ) {
	if ( is_object( $product ) && isset( $product_meta['yaye_custom_option'] ) && isset( $product_meta['yaye_total_option_cost'] ) ) {
		$price += floatval( $product_meta['yaye_total_option_cost'] );
	}
	return $price;
}
