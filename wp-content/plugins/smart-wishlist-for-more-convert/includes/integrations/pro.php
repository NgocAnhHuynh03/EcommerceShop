<?php
/**
 * WLFMC wishlist integration with pro theme
 *
 * Theme Name: Pro
 * Theme URI: https://theme.co/pro
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.6.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'after_setup_theme', 'wlfmc_after_setup_pro' );
/**
 * Pro theme integrate
 *
 * @return void
 */
function wlfmc_after_setup_pro() {

	if ( defined( 'X_SLUG' ) && X_SLUG === 'pro' ) {
		add_filter( 'wlfmc_loop_positions', 'wlfmc_pro_fix_loop_position' );
		remove_action( 'woocommerce_after_shop_loop_item_title', 'x_woocommerce_after_shop_loop_item_title', 10 );
		add_action( 'woocommerce_after_shop_loop_item_title', 'wlfmc_x_woocommerce_after_shop_loop_item_title', 10 );
		add_filter( 'wlfmc_custom_css_output', 'wlfmc_pro_fix_css' );
	}

}

/**
 * Add new hook for loop position
 *
 * @return void
 */
function wlfmc_x_woocommerce_after_shop_loop_item_title() {
	$has_action = has_action( 'wlfmc_x_woocommerce_before_add_to_cart' ) || has_action( 'wlfmc_x_woocommerce_after_add_to_cart' );
	if ( $has_action ) {
		echo '<div class="wlfmc-inline-buttons-no-mar">';
		do_action( 'wlfmc_x_woocommerce_before_add_to_cart' );
	}
	woocommerce_template_loop_add_to_cart();
	if ( $has_action ) {
		do_action( 'wlfmc_x_woocommerce_after_add_to_cart' );
		echo '</div>';
	}
	echo '</header></div>';
}

/**
 * Fix loop position
 *
 * @param array $positions all loop positions.
 *
 * @return array
 */
function wlfmc_pro_fix_loop_position( array $positions ): array {
	$positions['before_add_to_cart']['hook'] = 'wlfmc_x_woocommerce_before_add_to_cart';
	$positions['after_add_to_cart']['hook']  = 'wlfmc_x_woocommerce_after_add_to_cart';
	return $positions;
}

/**
 * Generate css plugin
 *
 * @param string $generated_css generated css codes.
 *
 * @return string
 */
function wlfmc_pro_fix_css( string $generated_css ) {
	$generated_css .= '.x-ethos .entry-header .wlfmc-inline-buttons-no-mar > .button { flex:1 }.x-blank .entry-header .wlfmc-inline-buttons-no-mar,.x-starter .entry-header .wlfmc-inline-buttons-no-mar, .x-stack-icon .entry-header .wlfmc-inline-buttons-no-mar, .x-renew .entry-header .wlfmc-inline-buttons-no-mar,.x-integrity .entry-header .wlfmc-inline-buttons-no-mar{justify-content: unset !important;}';
	return $generated_css;
}
