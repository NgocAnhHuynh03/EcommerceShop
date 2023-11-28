<?php
/**
 * Offer email content (plain)
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.0
 */

/**
 * Template variables:
 *
 * @var $email_heading string Email heading string
 * @var $email_footer string Email footer string
 * @var $email WC_Email Email object
 * @var $email_content string Email content (HTML)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( $email_heading ) {
	echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
	echo esc_html( wp_strip_all_tags( $email_heading ) );
	echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
}


echo esc_html( wp_strip_all_tags( $email_content ) );

echo "\n----------------------------------------\n\n";

if ( $email_footer ) {
	echo esc_html( wp_strip_all_tags( $email_footer ) );
}
