<?php
/**
 * Smart Wishlist Uninstall
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.3.3
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb, $wp_version;

/**
 * Uninstall WlFMC
 *
 * @return void
 *
 * @version 1.6.1
 */
function wlfmc_uninstall() {
	global $wpdb;

	wp_clear_scheduled_hook( 'wlfmc_wishlist_delete_expired_wishlists' );
	wp_clear_scheduled_hook( 'wlfmc_send_offer_emails' );
	wp_clear_scheduled_hook( 'wlfmc_delete_expired_coupons' );
	wp_clear_scheduled_hook( 'wlfmc_send_automation_emails' );
	wp_clear_scheduled_hook( 'wlfmc_send_automation_trigger_emails' );
	/**
	 * Only remove ALL data if "Remove all data" is checked in plugin options.
	 * This is to prevent data loss when deleting the plugin from the backend
	 */
	if ( '1' === get_option( 'wlfmc_remove_all_data' ) ) {

		// define local private attribute.
		$wpdb->wlfmc_wishlist_customers   = $wpdb->prefix . 'wlfmc_wishlist_customers';
		$wpdb->wlfmc_wishlist_items       = $wpdb->prefix . 'wlfmc_wishlist_items';
		$wpdb->wlfmc_wishlists            = $wpdb->prefix . 'wlfmc_wishlists';
		$wpdb->wlfmc_wishlist_offers      = $wpdb->prefix . 'wlfmc_wishlist_offers';
		$wpdb->wlfmc_wishlist_automations = $wpdb->prefix . 'wlfmc_wishlist_automations';
		$wpdb->wlfmc_wishlist_analytics   = $wpdb->prefix . 'wlfmc_wishlist_analytics';
		// delete pages created for this plugin.
		wp_delete_post( get_option( 'wlfmc_wishlist_page_id' ), true );

		// Delete options.
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", 'widget_wlfmc%' ) ); // @codingStandardsIgnoreLine.
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", 'wlfmc_%' ) ); // @codingStandardsIgnoreLine.
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", 'wlfmc-%' ) ); // @codingStandardsIgnoreLine.
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", 'smart-wishlist-for-more-convert_%' ) ); // @codingStandardsIgnoreLine.

		// Delete user meta.
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE %s;", 'wlfmc_%' ) ); // @codingStandardsIgnoreLine.

		// Delete tables.
		$wpdb->query( "DROP TABLE IF EXISTS $wpdb->wlfmc_wishlist_customers" ); // @codingStandardsIgnoreLine.
		$wpdb->query( "DROP TABLE IF EXISTS $wpdb->wlfmc_wishlist_items" ); // @codingStandardsIgnoreLine.
		$wpdb->query( "DROP TABLE IF EXISTS $wpdb->wlfmc_wishlists" ); // @codingStandardsIgnoreLine.
		$wpdb->query( "DROP TABLE IF EXISTS $wpdb->wlfmc_wishlist_offers" ); // @codingStandardsIgnoreLine.
		$wpdb->query( "DROP TABLE IF EXISTS $wpdb->wlfmc_wishlist_automations" ); // @codingStandardsIgnoreLine.
		$wpdb->query( "DROP TABLE IF EXISTS $wpdb->wlfmc_wishlist_analytics" ); // @codingStandardsIgnoreLine.

		// Clear any cached data that has been removed.
		wp_cache_flush();

	}

}

if ( ! is_multisite() ) {
	wlfmc_uninstall();
} else {
	global $wpdb;
	$blog_ids         = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" ); // @codingStandardsIgnoreLine.
	$original_blog_id = get_current_blog_id();

	foreach ( $blog_ids as $blog__id ) {
		switch_to_blog( $blog__id );
		wlfmc_uninstall();
	}

	switch_to_blog( $original_blog_id );
}
