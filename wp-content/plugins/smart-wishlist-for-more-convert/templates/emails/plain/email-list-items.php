<?php
/**
 * List items (plain)
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.5.0
 */

/**
 * Template variables:
 *
 * @var $items array list items
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

foreach ( $items as $item_id => $item ) :
	if ( apply_filters( 'wlfmc_email_item_visible', true, $item ) ) {

		// phpcs:ignore Generic.Commenting.DocComment
		/**
		 * @var $item WLFMC_Wishlist_Item
		 */
		$product = $item->get_product();

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wp_kses_post( apply_filters( 'wlfmc_email_item_name', $product->get_title(), $item, false ) );
		echo ' (x' . apply_filters( 'wlfmc_email_item_quantity', $item->get_quantity(), $item ) . ')';
		echo ' = ' . $item->get_formatted_original_price() . "\n";
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	echo "\n\n";
endforeach;
