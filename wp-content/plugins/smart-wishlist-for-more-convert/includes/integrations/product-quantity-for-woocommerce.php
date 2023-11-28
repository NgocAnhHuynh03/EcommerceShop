<?php
/**
 * WLFMC wishlist integration with All in One Product Quantity for WooCommerce  plugin
 *
 * @plugin_name All in One Product Quantity for WooCommerce
 * @version 4.4.3
 * @slug product-quantity-for-woocommerce
 * @url https://wordpress.org/plugins/product-quantity-for-woocommerce/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'wlfmc_product_quantity_for_woocommerce_integrate' );

/**
 * Integration with All in One Product Quantity for WooCommerce plugin
 *
 * @return void
 */
function wlfmc_product_quantity_for_woocommerce_integrate() {

	if ( class_exists( 'Alg_WC_PQ' ) ) {
		add_filter( 'wlfmc_remove_args_from_posted_data', 'wlfmc_fix_product_quantity_for_woocommerce_posted_data' );
		add_filter( 'wlfmc_woocommerce_add_to_cart_quantity', 'wlfmc_fix_quantity_product_quantity_for_woocommerce', 10, 4 );
		add_filter( 'wlfmc_product_with_meta_button_add_to_cart', '__return_true' );
		add_action( 'wlfmc_before_wishlist_table', 'wlfmc_fix_product_quantity_for_woocommerce_validation' );
	}
}

/**
 * Disable add to cart validation for show add to cart button
 *
 * @return void
 */
function wlfmc_fix_product_quantity_for_woocommerce_validation() {
	wlfmc_remove_filters( 'woocommerce_add_to_cart_validation', 'Alg_WC_PQ_Core', 'validate_on_add_to_cart', PHP_INT_MAX );
}
/**
 * Set quantity base on All in One Product Quantity for WooCommerce
 *
 * @param int        $qty product quantity.
 * @param WC_Product $product product.
 * @param int        $product_id product id.
 * @param int        $variation_id variation id.
 *
 * @return int
 */
function wlfmc_fix_quantity_product_quantity_for_woocommerce( $qty, $product, $product_id, $variation_id ) {

	$min_qty = alg_wc_pq()->core->get_product_qty_min_max( $product_id, $qty, 'min', $variation_id );
	$max_qty = alg_wc_pq()->core->get_product_qty_min_max( $product_id, $qty, 'max', $variation_id );
	return $min_qty > $qty ? $min_qty : min( $qty, $max_qty );
}

/**
 * Remove unused posted key
 *
 * @param array $args arguments.
 *
 * @return array
 */
function wlfmc_fix_product_quantity_for_woocommerce_posted_data( $args ) {
	foreach ( $_POST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification
		if ( strpos( $key, 'quantity_pq' ) === 0 ) {
			$args[] = sanitize_key( $key );
		}
	}
	return $args;
}
