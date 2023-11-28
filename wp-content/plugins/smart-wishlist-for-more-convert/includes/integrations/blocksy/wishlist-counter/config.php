<?php
/**
 * WLFMC wishlist integration with blocksy theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.6.6
 */

$config = array(
	'name'              => __( 'Wishlist Counter', 'wc-wlfmc-wishlist' ),
	'clone'             => true,
	'selective_refresh' => array(
		'display_mini_wishlist_for_counter',
		'enable_counter_add_link_title',
		'wishlist_counter_text',
	),

	'translation_keys'  => array(
		array( 'key' => 'wishlist_counter_text' ),
	),
);

