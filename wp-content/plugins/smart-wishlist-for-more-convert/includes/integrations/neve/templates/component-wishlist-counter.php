<?php
/**
 * Template used for component rendering wrapper.
 *
 * Name:    Header Footer Grid
 *
 * @version 1.0.0
 * @package HFG
 */

use WLFMC_Neve\Wishlist_Counter;
use function HFG\component_setting;

$enable_mini_wishlist = component_setting( Wishlist_Counter::DISPLAY_MINI_WISHLIST );
$show_products        = 'counter-only' !== $enable_mini_wishlist;
$show_list_on_hover   = 'on-click' !== $enable_mini_wishlist;
$add_link_title       = '';
$custom_title         = component_setting( Wishlist_Counter::WISHLIST_TITLE );
$title_shortcode      = '' !== $custom_title ? 'show_text="true" counter_text="' . esc_attr( $custom_title ) . '"' : '';
if ( 'counter-only' === $enable_mini_wishlist ) {
	$add_link_title = component_setting( Wishlist_Counter::ADD_LINK_TITLE );
}
echo do_shortcode( "[wlfmc_wishlist_counter add_link_title='$add_link_title' $title_shortcode show_products='$show_products' show_list_on_hover='$show_list_on_hover' ]" );

