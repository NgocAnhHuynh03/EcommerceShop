<?php
/**
 * WLFMC wishlist integration with Booking & Appointment Plugin for WooCommerce plugin
 *
 * @plugin_name Booking & Appointment Plugin for WooCommerce
 * @version 5.16.0
 * @slug woocommerce-bookings
 * @url https://www.tychesoftwares.com/products/woocommerce-booking-and-appointment-plugin/

 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_woocommerce_booking_integrate' );

/**
 * Integration with WooCommerce Square plugin
 *
 * @return void
 */
function wlfmc_woocommerce_booking_integrate() {

	if ( class_exists( 'woocommerce_booking' ) ) {
		add_filter( 'wlfmc_before_add_to_cart', 'wlfmc_fix_woocommerce_booking_posted_data' );
	}
}
/**
 * Remove unused posted key
 */
function wlfmc_fix_woocommerce_booking_posted_data() {
	foreach ( $_POST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification
		if ( in_array( $key, array( 'date_time_call', 'total_price_calculated' ), true ) || strpos( $key, 'wapbk_' ) === 0 || strpos( $key, 'booking_calender' ) === 0 || strpos( $key, 'block_option' ) === 0 || strpos( $key, 'bkap_' ) === 0 ) {
			unset( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification
			unset( $_REQUEST[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification
		}
	}
}
