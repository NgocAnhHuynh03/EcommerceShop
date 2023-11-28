<?php
/**
 * WLFMC wishlist integration with WooCommerce Product Add-Ons Ultimate plugin
 *
 * @plugin_name WooCommerce Product Add-Ons Ultimate
 * @version 3.11.0
 * @slug product-extras-for-woocommerce
 * @url  https://pluginrepublic.com/wordpress-plugins/woocommerce-product-add-ons-ultimate/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'wlfmc_pewc_integrate' );
/**
 * Integration with WooCommerce Product Add-Ons Ultimate plugin
 *
 * @return void
 */
function wlfmc_pewc_integrate() {
	if ( defined( 'PEWC_FILE' ) ) {
		add_filter( 'wlfmc_wishlist_item_price', 'wlfmc_pewc_wishlist_item_price', 9, 4 );
		add_action( 'wlfmc_before_wishlist_table', 'wlfmc_pewc_fix_cart_settings' );
		add_action( 'wlfmc_before_add_to_cart_validation', 'wlfmc_pewc_fix_post_data' );
		add_action( 'wlfmc_before_add_to_cart', 'wlfmc_pewc_fix_post_data' );
	}
}

/**
 * Fixed Posted data Files
 *
 * @return void
 */
function wlfmc_pewc_fix_post_data() {
	// phpcs:disable WordPress.Security
	if ( isset( $_POST['pewc_file_data'] ) && ! empty( $_POST['pewc_file_data'] ) ) {
		foreach ( $_POST['pewc_file_data'] as $k => $value ) {
			$_POST['pewc_file_data'][ $k ] = addslashes( $value );
		}
	}
	// phpcs:enable WordPress.Security
}

/**
 * Fix settings for show product meta in wishlist.
 *
 * @return void
 */
function wlfmc_pewc_fix_cart_settings() {
	add_filter( 'pewc_force_always_display_thumbs', '__return_true' );
	add_filter( 'pewc_display_child_product_meta', '__return_true' );
	add_filter( 'pewc_indent_child_product', '__return_true' );
	add_filter( 'pewc_show_option_prices_in_cart', '__return_true' );
	add_filter( 'pewc_filter_item_value_in_cart', 'wlfmc_pewc_replace_child_ids_with_titles', 10, 2 );
}

/**
 *  Modify wishlist item price for WooCommerce Product Add-Ons Ultimate
 *
 * @param string              $price item price.
 * @param null|array          $product_meta product meta.
 * @param WC_Product          $product  $product Woocommerce Product.
 * @param WLFMC_Wishlist_Item $item Wishlist item object.
 *
 * @return float
 * @throws Exception Exception.
 */
function wlfmc_pewc_wishlist_item_price( $price, $product_meta, $product, $item ) {
	if ( ! empty( $product_meta['product_extras'] ) && isset( $product_meta['product_extras']['price_with_extras'] ) ) {
		$price = (float) $product_meta['product_extras']['price_with_extras'];
	}
	return $price;

}

/**
 * Replace child product IDs with product names in cart meta
 *
 * @param string $value value.
 * @param array  $field field.
 *
 * @return string
 */
function wlfmc_pewc_replace_child_ids_with_titles( $value, $field ) {

	if ( isset( $field['type'] ) && ( 'product' === $field['type'] || 'product-categories' === $field['type'] ) ) {

		$ids = explode( ',', $value );
		if ( $ids ) {
			$new_value = array();
			foreach ( $ids as $id ) {
				$id      = trim( $id );
				$product = wc_get_product( $id );
				if ( is_object( $product ) ) {
					$new_value[] = $product->get_title();
				}
			}
			return join( ', ', $new_value );
		}
	}

	return $value;

}
