<?php
/**
 * WLFMC wishlist integration with Kadence theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.6.5
 */

namespace Kadence_Wlfmc;

use function Kadence\kadence;
use function add_action;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Desktop wishlist
 */
function header_wishlist() {
	$enable_mini_wishlist = kadence()->option( 'display_mini_wishlist_for_counter' );
	$show_products        = 'counter-only' !== $enable_mini_wishlist;
	$show_list_on_hover   = 'on-click' !== $enable_mini_wishlist;
	$add_link_title       = '';
	if ( 'counter-only' === $enable_mini_wishlist ) {
		$add_link_title = kadence()->option( 'enable_counter_add_link_title' );
	}
	echo do_shortcode( "[wlfmc_wishlist_counter add_link_title='$add_link_title' show_products='$show_products' show_list_on_hover='$show_list_on_hover' ]" );
}
add_action( 'kadence_header_wishlist', 'Kadence_Wlfmc\header_wishlist' );

/**
 * Mobile wishlist
 */
function mobile_wishlist() {
	$enable_mini_wishlist = kadence()->option( 'mobile_display_mini_wishlist_for_counter' );
	$show_products        = 'counter-only' !== $enable_mini_wishlist;
	$show_list_on_hover   = 'on-click' !== $enable_mini_wishlist;
	$add_link_title       = '';
	if ( 'counter-only' === $enable_mini_wishlist ) {
		$add_link_title = kadence()->option( 'mobile_enable_counter_add_link_title' );
	}
	echo do_shortcode( "[wlfmc_wishlist_counter add_link_title='$add_link_title' show_products='$show_products' show_list_on_hover='$show_list_on_hover' ]" );
}
add_action( 'kadence_mobile_wishlist', 'Kadence_Wlfmc\mobile_wishlist', 10 );
