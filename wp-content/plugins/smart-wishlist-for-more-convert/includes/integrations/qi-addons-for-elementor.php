<?php
/**
 * WLFMC wishlist integration with Qi Addons For Elementor plugin
 *
 * @plugin_name Qi Addons For Elementor
 * @version 1.5.9
 * @slug qi-addons-for-elementor
 * @url  https://qodeinteractive.com
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.5.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'QI_ADDONS_FOR_ELEMENTOR_VERSION' ) && version_compare( QI_ADDONS_FOR_ELEMENTOR_VERSION, '1.5.9', '>' ) ) {
	add_filter( 'qi_addons_for_elementor_filter_product_list_extra_options', 'wlfmc_qi_addons_for_elementor_add_wishlist' );
	add_filter( 'qi_addons_for_elementor_filter_product_slider_extra_options', 'wlfmc_qi_addons_for_elementor_add_wishlist' );
	add_action( 'qi_addons_for_elementor_action_product_list_item_additional_image_content', 'wlfmc_qi_addons_for_elementor_add_button_1' );
	add_action( 'qi_addons_for_elementor_action_product_slider_item_additional_image_content', 'wlfmc_qi_addons_for_elementor_add_button_1' );
	add_action( 'qi_addons_for_elementor_action_product_list_item_additional_content', 'wlfmc_qi_addons_for_elementor_add_button_2' );
	add_action( 'qi_addons_for_elementor_action_product_slider_item_additional_content', 'wlfmc_qi_addons_for_elementor_add_button_2' );
} else {
	add_action( 'init', 'wlfmc_qi_addons_integrate' );

}


/**
 * Integration with Qi Addons For Elementor plugin
 *
 * @return void
 */
function wlfmc_qi_addons_integrate() {

	if ( class_exists( 'QiAddonsForElementor' ) ) {
		add_filter( 'wlfmc_loop_positions', 'wlfmc_qi_addons_fix_loop_position' );
		add_filter( 'wlfmc_custom_css_output', 'wlfmc_qi_addons_fix_css' );
	}
}


/**
 * Add wishlist setting to elementor widget
 *
 * @param array $options array of all options.
 *
 * @return array|array[]
 */
function wlfmc_qi_addons_for_elementor_add_wishlist( $options ) {
	$info_below_options = array(
		array(
			'field_type' => 'select',
			'name'       => 'show_wishlist',
			'title'      => esc_html__( 'Show Wishlist', 'qi-addons-for-elementor' ),
			'options'    => qi_addons_for_elementor_get_select_type_options_pool( 'yes_no' ),
			'group'      => esc_html__( 'Layout', 'qi-addons-for-elementor' ),
		),
		array(
			'field_type'    => 'select',
			'name'          => 'wishlist_position',
			'title'         => esc_html__( 'Wishlist Position', 'qi-addons-for-elementor' ),
			'options'       => array(
				'mc-content' => esc_html__( 'Below Content', 'qi-addons-for-elementor' ),
				'mc-image'   => esc_html__( 'On Image', 'qi-addons-for-elementor' ),
			),
			'default_value' => 'mc-content',
			'dependency'    => array(
				'relation' => 'or',
				'hide'     => array(
					'layout'        => array(
						'values'        => array( 'info-below', 'info-below-swap', 'info-on-image' ),
						'default_value' => '',
					),
					'show_wishlist' => array(
						'values'        => array( 'no' ),
						'default_value' => '',
					),
				),
			),
			'group'         => esc_html__( 'Layout', 'qi-addons-for-elementor' ),
		),
	);
	return array_merge( $options, $info_below_options );
}

/**
 * Show add to wishlist button
 *
 * @param array $params arguments.
 *
 * @return void
 */
function wlfmc_qi_addons_for_elementor_add_button_1( $params ) {
	if ( isset( $params['layout'] ) && isset( $params['show_wishlist'] ) && wlfmc_is_true( $params['show_wishlist'] ) && in_array( $params['layout'], array( 'info-on-image', 'info-below-with-hover', 'info-below-hover-inset' ), true ) ) {
		if ( 'info-on-image' === $params['layout'] || ( isset( $params['wishlist_position'] ) && 'mc-image' === $params['wishlist_position'] ) ) {
			echo do_shortcode( '[wlfmc_add_to_wishlist position="after_add_to_cart" ]' );
		}
	}
}

/**
 * Show add to wishlist button
 *
 * @param array $params arguments.
 *
 * @return void
 */
function wlfmc_qi_addons_for_elementor_add_button_2( $params ) {
	if ( isset( $params['layout'] ) && isset( $params['show_wishlist'] ) && wlfmc_is_true( $params['show_wishlist'] ) && in_array( $params['layout'], array( 'info-below', 'info-below-swap', 'info-below-with-hover', 'info-below-hover-inset' ), true ) ) {
		if ( in_array( $params['layout'], array( 'info-below', 'info-below-swap' ), true ) || ( isset( $params['wishlist_position'] ) && 'mc-content' === $params['wishlist_position'] ) ) {
			echo do_shortcode( '[wlfmc_add_to_wishlist position="after_add_to_cart" ]' );
		}
	}
}

/**
 * Fix loop position
 *
 * @param array $positions all loop positions.
 *
 * @return array
 */
function wlfmc_qi_addons_fix_loop_position( array $positions ): array {

	if ( isset( $positions['before_add_to_cart']['hook'] ) ) {
		$positions['before_add_to_cart'] = array(
			array(
				'hook'     => $positions['before_add_to_cart']['hook'],
				'priority' => $positions['before_add_to_cart']['priority'],
			),
			array(
				'hook'     => 'qi_addons_for_elementor_action_product_list_item_additional_content',
				'priority' => 10,
			),
			array(
				'hook'     => 'qi_addons_for_elementor_action_product_slider_item_additional_content',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['before_add_to_cart'] ) ) {
		$positions['before_add_to_cart'][] = array(
			'hook'     => 'qi_addons_for_elementor_action_product_list_item_additional_content',
			'priority' => 10,
		);
		$positions['before_add_to_cart'][] = array(
			'hook'     => 'qi_addons_for_elementor_action_product_slider_item_additional_content',
			'priority' => 10,
		);
	}

	if ( isset( $positions['after_add_to_cart']['hook'] ) ) {
		$positions['after_add_to_cart'] = array(
			array(
				'hook'     => $positions['after_add_to_cart']['hook'],
				'priority' => $positions['after_add_to_cart']['priority'],
			),
			array(
				'hook'     => 'qi_addons_for_elementor_action_product_list_item_additional_content',
				'priority' => 10,
			),
			array(
				'hook'     => 'qi_addons_for_elementor_action_product_slider_item_additional_content',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['after_add_to_cart'] ) ) {
		$positions['after_add_to_cart'][] = array(
			'hook'     => 'qi_addons_for_elementor_action_product_list_item_additional_content',
			'priority' => 10,
		);
		$positions['after_add_to_cart'][] = array(
			'hook'     => 'qi_addons_for_elementor_action_product_slider_item_additional_content',
			'priority' => 10,
		);
	}

	if ( isset( $positions['image_top_left']['hook'] ) ) {
		$positions['image_top_left'] = array(
			array(
				'hook'     => $positions['image_top_left']['hook'],
				'priority' => $positions['image_top_left']['priority'],
			),
			array(
				'hook'     => 'qi_addons_for_elementor_action_product_list_item_additional_image_content',
				'priority' => 10,
			),
			array(
				'hook'     => 'qi_addons_for_elementor_action_product_slider_item_additional_image_content',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['image_top_left'] ) ) {
		$positions['image_top_left'][] = array(
			'hook'     => 'qi_addons_for_elementor_action_product_list_item_additional_image_content',
			'priority' => 10,
		);
		$positions['image_top_left'][] = array(
			'hook'     => 'qi_addons_for_elementor_action_product_slider_item_additional_image_content',
			'priority' => 10,
		);
	}

	if ( isset( $positions['image_top_right']['hook'] ) ) {
		$positions['image_top_right'] = array(
			array(
				'hook'     => $positions['image_top_right']['hook'],
				'priority' => $positions['image_top_right']['priority'],
			),
			array(
				'hook'     => 'qi_addons_for_elementor_action_product_list_item_additional_image_content',
				'priority' => 10,
			),
			array(
				'hook'     => 'qi_addons_for_elementor_action_product_slider_item_additional_image_content',
				'priority' => 10,
			),
		);
	} elseif ( ! empty( $positions['image_top_right'] ) ) {
		$positions['image_top_right'][] = array(
			'hook'     => 'qi_addons_for_elementor_action_product_list_item_additional_image_content',
			'priority' => 10,
		);
		$positions['image_top_right'][] = array(
			'hook'     => 'qi_addons_for_elementor_action_product_slider_item_additional_image_content',
			'priority' => 10,
		);
	}

	return $positions;
}

/**
 * Generate css plugin
 *
 * @param string $generated_css generated css codes.
 *
 * @return string
 */
function wlfmc_qi_addons_fix_css( string $generated_css ) {
	$generated_css .= '.qodef-e-product-inner .wlfmc-add-button {position: relative; z-index:4}';
	$generated_css .= '.qodef-item-layout--info-on-image-centered .wlfmc-top-of-image ,.qodef-item-layout--info-on-image .wlfmc-top-of-image{ position:relative;top:0;right:0;left:0;}';
	return $generated_css;
}
