<?php
/**
 * WLFMC wishlist integration with Kadence theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.6.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_kadence_integrate' );

/**
 * Integration with kadence theme
 *
 * @return void
 */
function wlfmc_kadence_integrate() {

	if ( defined( 'KADENCE_VERSION' ) ) {

		add_filter( 'wlfmc_loop_positions', 'wlfmc_kadence_fix_loop_position' );
		add_filter( 'wlfmc_custom_css_output', 'wlfmc_kadence_custom_css' );
		require_once MC_WLFMC_INC . 'integrations/kadence/class-header-wishlist.php';
		require_once MC_WLFMC_INC . 'integrations/kadence/hooks.php';
	}
}

/**
 * Fix loop position
 *
 * @param array $positions all loop positions.
 *
 * @return array
 */
function wlfmc_kadence_fix_loop_position( array $positions ): array {
	$positions['before_add_to_cart']['priority'] = 2;
	$positions['after_add_to_cart']['priority']  = 3;
	return $positions;
}


/**
 * Add inline custom css.
 *
 * @param string $generated_code generated css codes.
 *
 * @return string
 */
function wlfmc_kadence_custom_css( $generated_code ) {
	$generated_code .= '#masthead { z-index: 12 !important}';
	return $generated_code;
}
