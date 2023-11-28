<?php
/**
 * WLFMC wishlist integration with WPC Product Bundles for WooCommerce ( free and Premium ) plugin
 *
 * @plugin_name WPC Product Bundles for WooCommerce ( free and Premium )
 * @version 6.6.3 free & 6.6.2 premium
 * @slug woo-product-bundle
 * @url https://wpclever.net/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_woo_product_bundle_integrate' );

/**
 * Integration with WPC Product Bundles for WooCommerce ( free and Premium ) plugin
 *
 * @return void
 */
function wlfmc_woo_product_bundle_integrate() {

	if ( defined( 'WOOSB_VERSION' ) ) {
		add_action( 'wlfmc_before_wishlist_table', 'wlfmc_woo_product_bundle_show_in_table' );
		add_action( 'wlfmc_after_wishlist_table', 'wlfmc_woo_product_bundle_hide_in_table' );
		add_action( 'wlfmc_before_add_to_cart_validation', 'wlfmc_woo_product_bundle_fix_woosb_post_data' );
		add_filter( 'wlfmc_wishlist_item_price', 'wlfmc_woo_product_bundle_wishlist_item_price', 8, 4 );
	}
}

/**
 * Add hook before table to Show products in wishlist table.
 *
 * @return void
 */
function wlfmc_woo_product_bundle_show_in_table() {
	add_filter( 'woocommerce_get_item_data', array( WPCleverWoosb(), 'get_item_data' ), 10, 2 );
}

/**
 * Remove added hook after wishlist table.
 *
 * @return void
 */
function wlfmc_woo_product_bundle_hide_in_table() {
	remove_filter( 'woocommerce_get_item_data', array( WPCleverWoosb(), 'get_item_data' ), 10 );
}

/**
 * Fix $_REQUEST  for add to card validation
 *
 * @return void
 */
function wlfmc_woo_product_bundle_fix_woosb_post_data() {
	if ( isset( $_POST['woosb_ids'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$_REQUEST['woosb_ids'] = WPCleverWoosb_Helper::clean_ids( sanitize_text_field( wp_unslash( $_POST['woosb_ids'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}
}

/**
 *  Modify wishlist item price for WPC Product Bundles for WooCommerce ( free and Premium)
 *
 * @param string              $price item price.
 * @param null|array          $product_meta product meta.
 * @param WC_Product          $product  $product Woocommerce Product.
 * @param WLFMC_Wishlist_Item $item Wishlist item object.
 *
 * @throws Exception Exception.
 * @return float|int
 */
function wlfmc_woo_product_bundle_wishlist_item_price( $price, $product_meta, $product, $item ) {

	if ( $product->is_type( 'woosb' ) && isset( $product_meta['woosb_ids'] ) ) {
		$ids = WPCleverWoosb_Helper::clean_ids( $product_meta['woosb_ids'] );
		$product->build_items( $ids );
		$items = $product->get_items();
		if ( $items && is_array( $items ) && ( count( $items ) > 0 ) && ! $product->is_fixed_price() ) {
			$price                 = 0;
			$exclude_unpurchasable = WPCleverWoosb_Helper::get_setting( 'exclude_unpurchasable', 'no' );
			$discount_amount       = $product->get_discount_amount();
			$discount_percentage   = $product->get_discount_percentage();
			foreach ( $items as $item ) {
				$_qty     = $item['qty'];
				$_product = wc_get_product( $item['id'] );

				if ( ! $_product || ( $_qty <= 0 ) || in_array(
					$_product->get_type(),
					array(
						'bundle',
						'woosb',
						'composite',
						'grouped',
						'woosg',
						'external',
					),
					true
				) ) {
					continue;
				}

				if ( ( ! $_product->is_purchasable() || ! $_product->is_in_stock() ) && $exclude_unpurchasable ) {
					// exclude unpurchasable.
					continue;
				}
				$_price = (float) WPCleverWoosb_Helper::get_price( $_product );
				if ( $discount_amount ) {
					$_price -= (float) $discount_amount;
				} elseif ( $discount_percentage ) {
					$_price *= (float) ( 100 - $discount_percentage ) / 100;
				}
				$price += $_price * $_qty;
			}
		}
	}

	return $price;
}
