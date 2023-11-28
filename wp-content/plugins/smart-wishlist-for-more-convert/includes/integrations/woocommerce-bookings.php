<?php
/**
 * WLFMC wishlist integration with woocommerce bookings plugin
 *
 * @plugin_name WooCommerce Bookings
 * @version 1.15.63
 * @slug woocommerce-bookings
 * @url https://woocommerce.com/products/woocommerce-bookings/

 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'wlfmc_adding_to_wishlist', 'wlfmc_fix_woocommerce_bookings_add_to_wishlist' );

/**
 * Fix add to wishlist woocommerce bookings
 *
 * @return void
 */
function wlfmc_fix_woocommerce_bookings_add_to_wishlist() {
	if ( class_exists( 'WC_Bookings_Addons' ) ) {
		wlfmc_remove_filters( 'woocommerce_add_cart_item_data', 'WC_Booking_Cart_Manager', 'add_cart_item_data', 10 );
		add_filter( 'wlfmc_add_cart_item_data', 'wlfmc_woocommerce_bookings_cart_item_data', 10, 2 );

		if ( class_exists( 'WC_Product_Addons' ) ) {
			// Woocommerce addons integrate.
			$wc_bookings_addons = new WC_Bookings_Addons();
			add_filter( 'wlfmc_woocommerce_bookings_add_cart_item_data', array( $wc_bookings_addons, 'add_cart_item_data_adjust_booking_cost' ), 20 );

		}
	}
}

/**
 * Fix woocommerce booking cart item data.
 *
 * @param array $cart_item_data cart item data.
 * @param int   $product_id product_id.
 *
 * @return array
 */
function wlfmc_woocommerce_bookings_cart_item_data( $cart_item_data, $product_id ) {
	$product = wc_get_product( $product_id );
	if ( ! is_wc_booking_product( $product ) ) {
		return $cart_item_data;
	}

	if ( ! key_exists( 'booking', $cart_item_data ) ) {
		$cart_item_data['booking'] = wc_bookings_get_posted_data( $_POST, $product ); // phpcs:ignore WordPress.Security.NonceVerification
	}
	$cart_item_data['booking']['_cost'] = WC_Bookings_Cost_Calculation::calculate_booking_cost( $cart_item_data['booking'], $product );

	if ( $cart_item_data['booking']['_cost'] instanceof WP_Error ) {
		$cart_item_data['booking']['_cost'] = 0;
	}
	return (array) apply_filters( 'wlfmc_woocommerce_bookings_add_cart_item_data', $cart_item_data, $product_id );
}


