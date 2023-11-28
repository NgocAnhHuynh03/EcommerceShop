<?php
/**
 * WLFMC wishlist integration with blocksy theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.6.6
 */

/**
 * Template Variables:
 *
 * @var $atts array Array of attributes
 * @var $section_id string section id.
 * @var $item_id string item id.
 * @var $attr array|mixed|string attributes.
 */

$enable_mini_wishlist = blocksy_default_akg( 'display_mini_wishlist_for_counter', $atts, 'counter-only' );
$show_products        = 'counter-only' !== $enable_mini_wishlist;
$show_list_on_hover   = 'on-click' !== $enable_mini_wishlist;
$custom_title         = blocksy_translate_dynamic( blocksy_default_akg( 'wishlist_counter_text', $atts, '' ), 'header:' . $section_id . ':' . $item_id . ':wishlist_counter_text' );
$title_shortcode      = '' !== $custom_title ? 'show_text="true" counter_text="' . esc_attr( $custom_title ) . '"' : '';
$add_link_title       = '';
if ( 'counter-only' === $enable_mini_wishlist ) {
	$add_link_title = wlfmc_is_true( blocksy_default_akg( 'enable_counter_add_link_title', $atts, '' ) );
	if ( $add_link_title ) {
		$add_link_title = " add_link_title='true' ";
	}
}
?>
<div class="blocksy-wlfmc-wishlist-counter-wrapper" <?php echo blocksy_attr_to_html( $attr );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php echo do_shortcode( "[wlfmc_wishlist_counter $add_link_title $title_shortcode show_products='$show_products' show_list_on_hover='$show_list_on_hover' ]" ); ?>
</div>
