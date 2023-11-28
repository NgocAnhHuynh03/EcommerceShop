<?php
/**
 * WLFMC wishlist integration with Flexible Product Fields plugin ( Free & Pro )
 *
 * @plugin_name Flexible Product Fields
 * @version 2.3.12 free & 2.3.4 pro
 * @slug flexible-product-fields
 * @url  https://wordpress.org/plugins/flexible-product-fields/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.6.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'wlfmc_flexible_product_fields_integrate' );

/**
 * Integration with Flexible Product Fields plugin
 *
 * @return void
 */
function wlfmc_flexible_product_fields_integrate() {

	if ( defined( 'FLEXIBLE_PRODUCT_FIELDS_VERSION' ) ) {
		add_action( 'wlfmc_adding_to_waitlist', 'wlfmc_flexible_product_fields_adding_handler' );
		add_action( 'wlfmc_adding_to_wishlist', 'wlfmc_flexible_product_fields_adding_handler' );
		add_filter( 'wlfmc_wishlist_item_price', 'wlfmc_flexible_product_fields_wishlist_item_price', 9, 4 );
		add_filter( 'wlfmc_woocommerce_add_to_cart_validation', 'wlfmc_flexible_product_add_to_cart_validation', 10, 5 );
	}

}

/**
 * Prepare adding to list hook
 *
 * @param int $prod_id product id.
 * @return void
 */
function wlfmc_flexible_product_fields_adding_handler( $prod_id ) {
	$product = wc_get_product( $prod_id );
	if ( $product ) {
		do_action( 'wlfmc_add_to_list_handler', $product->get_type(), $product );
		remove_filter( 'wlfmc_wishlist_item_price', 'wlfmc_flexible_product_fields_wishlist_item_price', 9 );
	}
}

/**
 *  Modify wishlist item price for Flexible Product Fields
 *
 * @param string              $price item price.
 * @param null|array          $product_meta product meta.
 * @param WC_Product          $product  $product Woocommerce Product.
 * @param WLFMC_Wishlist_Item $item Wishlist item object.
 *
 * @throws Exception Exception.
 * @return float|int|mixed
 */
function wlfmc_flexible_product_fields_wishlist_item_price( $price, $product_meta, $product, $item ) {
	if ( is_object( $product ) && isset( $product_meta['flexible_product_fields'] ) ) {
		$cart_item         = $item->get_cart_item();
		$cart_item['data'] = $product;
		$cart_item         = apply_filters( 'wlfmc_third_party_item_price', $cart_item );
		if ( $cart_item['data'] ) {
			$price = $cart_item['data']->get_price( 'edit' );
		}
	}
	return $price;
}

/**
 *  Modify wishlist item price for Flexible Product Fields
 *
 * @param bool                $validation item price.
 * @param WC_Product          $product  $product Woocommerce Product.
 * @param null|array          $meta product meta.
 * @param WLFMC_Wishlist_Item $item Wishlist item object.
 * @param Array               $cart_item of other cart item data.
 *
 * @throws Exception Exception.
 * @return float|int|mixed
 */
function wlfmc_flexible_product_add_to_cart_validation( $validation, $product, $meta, $item, $cart_item ) {

	$post_data = $item->get_posted_data( 'view' );
	$post_data = is_array( $post_data ) && isset( $post_data['post'] ) ? $post_data['post'] : array();
	if ( is_object( $product ) && isset( $post_data['_fpf_product_id'] ) ) {
		try {
			$_POST     = $post_data;
			$cart_item = apply_filters( 'wlfmc_add_to_cart_handler', $cart_item, $cart_item['product_id'], $cart_item['variation_id'] );
		} catch ( Exception $e ) {
			return false;
		}
	}
	return $validation;
}
