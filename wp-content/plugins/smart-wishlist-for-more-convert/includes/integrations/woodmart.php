<?php
/**
 * WLFMC wishlist integration with woodmart theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_woodmart_integrate' );
add_filter( 'wlfmc_admin_options', 'wlfmc_woodmart_settings' );

/**
 * Integration with woodmart theme
 *
 * @return void
 */
function wlfmc_woodmart_integrate() {

	if ( defined( 'WOODMART_VERSION' ) ) {

		add_filter( 'wlfmc_loop_positions', 'wlfmc_woodmart_fix_loop_position' );
		add_filter( 'wlfmc_button_positions', 'wlfmc_woodmart_fix_single_position' );
		add_filter( 'wlfmc_add_to_wishlist_params', 'wlfmc_woodmart_change_container_class', 10, 2 );
	}

}

/**
 * Add woodmart container button class to wishlist container button
 *
 * @param array $params  shortcode additional params.
 * @param array $atts shortcode atts.
 *
 * @return array
 */
function wlfmc_woodmart_change_container_class( $params, $atts ) {
	if ( $params['container_classes'] && wlfmc_str_contains( $params['container_classes'], 'wlfmc_position_woodmart' ) ) {
		$params['container_classes'] = str_replace( 'wlfmc-single-btn', 'wlfmc-single-btn wd-action-btn', $params['container_classes'] );
	}

	return $params;
}

/**
 * Fix loop position
 *
 * @param array $positions all loop positions.
 *
 * @return array
 */
function wlfmc_woodmart_fix_loop_position( array $positions ): array {

	$positions['woodmart']        = array(
		'hook'     => 'woodmart_product_action_buttons',
		'priority' => 30,
	);
	$positions['image_top_right'] = array(
		'hook'     => 'woocommerce_before_shop_loop_item',
		'priority' => 7,
	);

	return $positions;
}

/**
 * Fix single position
 *
 * @param array $positions all single positions.
 *
 * @return array
 */
function wlfmc_woodmart_fix_single_position( array $positions ): array {

	$positions['woodmart'] = array(
		array(
			'hook'     => 'woocommerce_single_product_summary',
			'priority' => '33',
		),
		array(
			'hook'     => 'woodmart_sticky_atc_actions',
			'priority' => '20',
		),
	);
	return $positions;
}

/**
 * Fix position with woodmart
 *
 * @param array $settings wishlist single position settings.
 *
 * @return array
 */
function wlfmc_woodmart_settings( array $settings ): array {

	if ( defined( 'WOODMART_CORE_PLUGIN_VERSION' ) ) {

		$settings['button-display']['fields']['button']['wishlist_button_position']['options'] = array(
			'woodmart'  => __( 'Default Woodmart Theme', 'wc-wlfmc-wishlist' ),
			'shortcode' => __( 'Use shortcode', 'wc-wlfmc-wishlist' ),
		);
		$settings['button-display']['fields']['button']['loop_position']['options']            = array(
			'woodmart'  => __( 'Default Woodmart Theme', 'wc-wlfmc-wishlist' ),
			'shortcode' => __( 'Use shortcode', 'wc-wlfmc-wishlist' ),
		);
		$settings['button-display']['fields']['button']['wishlist_button_position']['default'] = 'woodmart';
		$settings['button-display']['fields']['button']['loop_position']['default']            = 'woodmart';
		$settings['button-display']['fields']['button']['button_type_loop']['options']         = array(
			'icon' => __( 'Icon only', 'wc-wlfmc-wishlist' ),
		);
		$settings['button-display']['fields']['button']['button_type_loop']['desc']            = __( 'For Woodmart Theme archive pages, just "icon only" mode is supported.', 'wc-wlfmc-wishlist' );
		$settings['button-display']['fields']['button']['button_type_single']['default']       = 'both';
		$settings['button-display']['fields']['button']['button_icon_single']['fields']['icon_font_size_single']['default']   = '14px';
		$settings['button-display']['fields']['button']['button_icon_single']['fields']['icon_color_single']['default']       = 'rgb(51, 51, 51)';
		$settings['button-display']['fields']['button']['button_icon_single']['fields']['icon_hover_color_single']['default'] = 'rgb(81, 81, 81)';
		$settings['button-display']['fields']['button']['button_text_single']['fields']['text_font_size_single']['default']   = '14px';
		$settings['button-display']['fields']['button']['button_text_single']['fields']['text_color_single']['default']       = 'rgb(51, 51, 51)';
		$settings['button-display']['fields']['button']['button_text_single']['fields']['text_hover_color_single']['default'] = 'rgb(81, 81, 81)';
		$settings['button-display']['fields']['button']['button_sizes_single']['fields']['button_height_single']['default']   = '20px';
		$settings['button-display']['fields']['button']['button_icon_loop']['fields']['icon_font_size_loop']['default']       = '16px';

		$settings['button-display']['fields']['counter']['counter_products_number_position']['default']                           = 'top-right';
		$settings['button-display']['fields']['counter']['counter_style']['fields']['counter_color']['default']                   = 'rgb(51, 51, 51)';
		$settings['button-display']['fields']['counter']['counter_style']['fields']['counter_icon_font_size']['default']          = '20px';
		$settings['button-display']['fields']['counter']['counter_style']['fields']['counter_number_background_color']['default'] = 'rgb(131,183,53)';

	}

	return $settings;
}
