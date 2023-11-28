<?php
/**
 * Wishlist pages template; load template parts basing on the url
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.5.2
 */

/**
 * Template Variables:
 *
 * @var $var array Array of attributes that needs to be sent to sub-template
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php
/**
 * DO_ACTION: wlfmc_before_wishlist_content
 *
 * Allows to render some content or fire some action before the wishlist content.
 *
 * @param array $var Array of attributes that needs to be sent to sub-template
 */
do_action( 'wlfmc_before_wishlist_content', $var );
?>

<?php
/**
 * DO_ACTION: wlfmc_main_wishlist_content
 *
 * Allows to render some content or fire some action in the wishlist content.
 *
 * @param array $var Array of attributes that needs to be sent to sub-template
 */
do_action( 'wlfmc_main_wishlist_content', $var );
?>

<?php
/**
 * DO_ACTION: wlfmc_after_wishlist_content
 *
 * Allows to render some content or fire some action after the wishlist content.
 *
 * @param array $var Array of attributes that needs to be sent to sub-template
 */
do_action( 'wlfmc_after_wishlist_content', $var );
