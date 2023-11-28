<?php
/**
 * WLFMC wishlist integration with woocommerce product addons plugin
 *
 * @plugin_name WooCommerce Product Add-ons
 * @version 4.9.0
 * @slug woocommerce-product-addons
 * @url https://woocommerce.com/products/product-add-ons/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_woocommerce_product_addons_integrate' );

/**
 * Integration with woocommerce product addons plugin
 *
 * @return void
 */
function wlfmc_woocommerce_product_addons_integrate() {

	if ( class_exists( 'WC_Product_Addons' ) ) {
		add_filter( 'wlfmc_add_cart_item_data', 'wlfmc_remove_empty_addons_product_meta', 10, 3 );
		add_filter( 'wlfmc_before_add_to_cart', 'wlfmc_fix_addons_product_posted_data' );
	}
}



/**
 * Remove empty 'addons' key from product meta.
 *
 * @param null|array $product_meta product meta.
 * @param int        $product_id product id.
 *
 * @return array
 */
function wlfmc_remove_empty_addons_product_meta( $product_meta, $product_id ) {
	if ( isset( $product_meta['addons'] ) && empty( $product_meta['addons'] ) ) {
		$product_addons = WC_Product_Addons_Helper::get_product_addons( $product_id );
		if ( ! $product_addons ) {
			unset( $product_meta['addons'] );
		}
	}
	return $product_meta;
}

/**
 * Remove unused posted key
 */
function wlfmc_fix_addons_product_posted_data() {
	foreach ( $_POST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification
		if ( strpos( $key, 'addon-' ) === 0 ) {
			unset( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification
			unset( $_REQUEST[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification
		}
	}

	foreach ( $_FILES as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification
		if ( strpos( $key, 'addon-' ) === 0 ) {
			unset( $_FILES[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification
		}
	}
}
