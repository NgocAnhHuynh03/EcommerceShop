<?php
/**
 * WLFMC wishlist integration with JetWooBuilder For Elementor
 *
 * @plugin_name JetWooBuilder For Elementor
 * @version 2.1.4
 * @slug jet-woo-builder
 * @url  https://crocoblock.com/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.5.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action( 'init', 'wlfmc_jetwoobuilder_integrate' );

/**
 * Integration with JetWooBuilder For Elementor plugin
 *
 * @return void
 */
function wlfmc_jetwoobuilder_integrate() {

	if ( class_exists( 'Jet_Woo_Builder' ) ) {
		add_filter( 'wlfmc_loop_positions', 'wlfmc_jetwoobuilder_fix_loop_position' );
		add_filter( 'wlfmc_custom_css_output', 'wlfmc_jetwoobuilder_fix_css' );

		// Add wishlist buttons content controls to Products Grid/List widgets from JetWooBuilder.
		add_action( 'elementor/element/jet-woo-products/section_general/after_section_end', 'wlfmc_register_wishlist_button_jet_woo_builder_controls', 10, 2 );
		add_action( 'elementor/element/jet-woo-products-list/section_general/after_section_end', 'wlfmc_register_wishlist_button_jet_woo_builder_controls', 10, 2 );

		wlfmc_remove_filters( 'elementor/element/jet-woo-products/section_general/after_section_end', 'Jet_CW_Wishlist_Integration', 'register_wishlist_button_content_controls', 10 );
		wlfmc_remove_filters( 'elementor/element/jet-woo-products-list/section_general/after_section_end', 'Jet_CW_Wishlist_Integration', 'register_wishlist_button_content_controls', 10 );
		wlfmc_remove_filters( 'elementor/element/jet-woo-products/section_button_style/after_section_end', 'Jet_CW_Wishlist_Integration', 'register_wishlist_button_style_controls', 10 );
		wlfmc_remove_filters( 'elementor/element/jet-woo-products-list/section_button_style/after_section_end', 'Jet_CW_Wishlist_Integration', 'register_wishlist_button_style_controls', 10 );
		wlfmc_remove_filters( 'elementor/element/woocommerce-archive-products/section_design_box/after_section_end', 'Jet_CW_Wishlist_Integration', 'register_wishlist_button_style_controls', 10 );

		wlfmc_remove_filters( 'jet-woo-builder/templates/jet-woo-products/wishlist-button', 'Jet_CW_Wishlist_Integration', 'add_wishlist_button', 10 );
		wlfmc_remove_filters( 'jet-woo-builder/templates/jet-woo-products-list/wishlist-button', 'Jet_CW_Wishlist_Integration', 'add_wishlist_button', 10 );
		wlfmc_remove_filters( 'woocommerce_after_shop_loop_item', 'Jet_CW_Wishlist_Integration', 'add_wishlist_button_default', 10 );
		wlfmc_remove_filters( 'woocommerce_single_product_summary', 'Jet_CW_Wishlist_Integration', 'add_wishlist_button_default', 10 );
		wlfmc_remove_filters( 'jet-woo-builder/jet-woo-products-grid/settings', 'Jet_CW_Wishlist_Integration', 'wishlist_button_icon', 10 );
		wlfmc_remove_filters( 'jet-woo-builder/jet-woo-products-list/settings', 'Jet_CW_Wishlist_Integration', 'wishlist_button_icon', 10 );

	}
}

/**
 * Register wishlist button content controls.
 *
 * @param object $obj  Widget instance.
 * @param array  $args Specific widget arguments list.
 */
function wlfmc_register_wishlist_button_jet_woo_builder_controls( $obj = null, $args = array() ) {

	$obj->start_controls_section(
		'section_wishlist_content',
		array(
			'label' => __( 'Wishlist', 'wc-wlfmc-wishlist' ),
		)
	);

	if ( 'jet-woo-products' === $obj->get_name() || 'jet-woo-products-list' === $obj->get_name() ) {
		$obj->add_control(
			'show_wishlist',
			array(
				'label'     => __( 'Wishlist Button', 'wc-wlfmc-wishlist' ),
				'type'      => Elementor\Controls_Manager::SWITCHER,
				'label_on'  => __( 'Show', 'jet-woo-builder' ),
				'label_off' => __( 'Hide', 'jet-woo-builder' ),
			)
		);
	} else {
		$obj->add_control(
			'show_wishlist',
			array(
				'label'   => __( 'Wishlist Button', 'wc-wlfmc-wishlist' ),
				'type'    => \Elementor\Controls_Manager::HIDDEN,
				'default' => 'yes',
			)
		);
	}

	$obj->end_controls_section();

}

/**
 * Fix loop position
 *
 * @param array $positions all loop positions.
 *
 * @return array
 */
function wlfmc_jetwoobuilder_fix_loop_position( array $positions ): array {

	if ( isset( $positions['before_add_to_cart']['hook'] ) ) {
		$positions['before_add_to_cart'] = array(
			array(
				'hook'     => $positions['before_add_to_cart']['hook'],
				'priority' => $positions['before_add_to_cart']['priority'],
			),
			array(
				'hook'     => 'jet-woo-builder/templates/jet-woo-products/wishlist-button',
				'priority' => 10,
			),
			array(
				'hook'     => 'jet-woo-builder/templates/jet-woo-products-list/wishlist-button',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['before_add_to_cart'] ) ) {
		$positions['before_add_to_cart'][] = array(
			'hook'     => 'jet-woo-builder/templates/jet-woo-products/wishlist-button',
			'priority' => 10,
		);
		$positions['before_add_to_cart'][] = array(
			'hook'     => 'jet-woo-builder/templates/jet-woo-products-list/wishlist-button',
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
				'hook'     => 'jet-woo-builder/templates/jet-woo-products/wishlist-button',
				'priority' => 10,
			),
			array(
				'hook'     => 'jet-woo-builder/templates/jet-woo-products-list/wishlist-button',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['after_add_to_cart'] ) ) {
		$positions['after_add_to_cart'][] = array(
			'hook'     => 'jet-woo-builder/templates/jet-woo-products/wishlist-button',
			'priority' => 10,
		);
		$positions['after_add_to_cart'][] = array(
			'hook'     => 'jet-woo-builder/templates/jet-woo-products-list/wishlist-button',
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
				'hook'     => 'jet-woo-builder/templates/jet-woo-products/wishlist-button',
				'priority' => 10,
			),
			array(
				'hook'     => 'jet-woo-builder/templates/jet-woo-products-list/wishlist-button',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['image_top_left'] ) ) {
		$positions['image_top_left'][] = array(
			'hook'     => 'jet-woo-builder/templates/jet-woo-products/wishlist-button',
			'priority' => 10,
		);
		$positions['image_top_left'][] = array(
			'hook'     => 'jet-woo-builder/templates/jet-woo-products-list/wishlist-button',
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
				'hook'     => 'jet-woo-builder/templates/jet-woo-products/wishlist-button',
				'priority' => 10,
			),
			array(
				'hook'     => 'jet-woo-builder/templates/jet-woo-products-list/wishlist-button',
				'priority' => 10,
			),
		);
	} elseif ( ! empty( $positions['image_top_right'] ) ) {
		$positions['image_top_right'][] = array(
			'hook'     => 'jet-woo-builder/templates/jet-woo-products/wishlist-button',
			'priority' => 10,
		);
		$positions['image_top_right'][] = array(
			'hook'     => 'jet-woo-builder/templates/jet-woo-products-list/wishlist-button',
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
function wlfmc_jetwoobuilder_fix_css( string $generated_css ) {
	$generated_css .= '.jet-woo-products-cqw-wrapper .wlfmc-top-of-image, .jet-woo-products-list__item-content .wlfmc-top-of-image,.jet-woo-products__item .wlfmc-top-of-image{ position:relative;top:0;right:0;left:0;}';
	return $generated_css;
}
