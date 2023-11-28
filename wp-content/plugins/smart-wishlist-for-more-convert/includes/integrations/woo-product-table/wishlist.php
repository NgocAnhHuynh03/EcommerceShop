<?php
/**
 * Add to wishlist template for woo-product-table
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.4.4
 */

/**
 * Template variables:
 *
 * @var $data array all variables
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$wpt_wishlist  = false;
$wpt_wishlist .= do_shortcode( '[wlfmc_add_to_wishlist position="shortcode" product_id=' . $data['id'] . '  is_single=""]' );

echo wp_kses_post( $wpt_wishlist );
