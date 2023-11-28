<?php
/**
 * WLFMC wishlist integration with shoptimizer theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.3
 * @version 1.6.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_shoptimizer_integrate' );
add_filter( 'wlfmc_wishlist_button_settings', 'wlfmc_shoptimizer_single_button_positions' );
add_action( 'after_setup_theme', 'wlfmc_after_setup_shoptimizer' );

/**
 * Integration with Shoptimizer theme
 *
 * @return void
 */
function wlfmc_shoptimizer_integrate() {

	if ( class_exists( 'CommerceGurus_Commercekit' ) ) {

		add_filter( 'shoptimizer_get_option_defaults', 'wlfmc_shoptimizer_get_option_defaults' );

		add_action(
			'wlfmc_table_product_before_add_to_cart',
			function() {
				remove_filter( 'woocommerce_loop_add_to_cart_link', 'commercegurus_as_loop_add_to_cart_link', 99 );
			}
		);

		add_action(
			'wlfmc_table_product_after_add_to_cart',
			function() {
				add_filter( 'woocommerce_loop_add_to_cart_link', 'commercegurus_as_loop_add_to_cart_link', 99, 2 );
			}
		);

		$shoptimizer_layout_woocommerce_card_display = function_exists( 'shoptimizer_get_option' ) ? shoptimizer_get_option( 'shoptimizer_layout_woocommerce_card_display' ) : 'slide';
		if ( 'slide' !== $shoptimizer_layout_woocommerce_card_display ) {
			add_filter( 'wlfmc_loop_positions', 'wlfmc_shoptimizer_fix_loop_position' );

		}

		add_filter( 'wlfmc_button_positions', 'wlfmc_shoptimizer_fix_single_position' );
		remove_action( 'woocommerce_single_product_summary', 'commercekit_single_product_wishlist', 38 );
		remove_action( 'woocommerce_before_shop_loop_item_title', 'commercekit_after_shop_loop_item_wishlist', 15 );
	}

}

/**
 * Set default options for shoptimizer settings.
 *
 * @param array $defaults default options.
 * @return array
 */
function wlfmc_shoptimizer_get_option_defaults( $defaults ) {
	$defaults['shoptimizer_enable_wishlist_counter']   = 'disable';
	$defaults['shoptimizer_mc_counters_text_color']    = '#333';
	$defaults['shoptimizer_mc_counters_icon_color']    = '#333';
	$defaults['shoptimizer_enable_waitlist_counter']   = 'disable';
	$defaults['shoptimizer_enable_multi_list_counter'] = 'disable';
	return $defaults;
}

/**
 * Fix single position
 *
 * @param array $positions all single positions.
 *
 * @return array
 */
function wlfmc_shoptimizer_fix_single_position( array $positions ): array {

	$positions['image_top_left']  = array(
		array(
			'hook'     => 'commercekit_before_gallery',
			'priority' => '10',
		),
	);
	$positions['image_top_right'] = array(
		array(
			'hook'     => 'commercekit_before_gallery',
			'priority' => '10',
		),
	);
	return $positions;
}

/**
 * Fix loop position
 *
 * @param array $positions all loop positions.
 *
 * @return array
 */
function wlfmc_shoptimizer_fix_loop_position( array $positions ): array {
	$positions['after_add_to_cart'] = array(
		'hook'     => 'woocommerce_after_shop_loop_item',
		'priority' => 7,
	);
	return $positions;
}

/**
 * Remove single position not compatible with shoptimizer
 *
 * @param array $settings wishlist single position settings.
 *
 * @return array
 */
function wlfmc_shoptimizer_single_button_positions( array $settings ): array {

	if ( class_exists( 'CommerceGurus_Commercekit' ) ) {
		if ( isset( $settings['wishlist_button_position']['options']['before_add_to_cart_button'] ) ) {
			unset( $settings['wishlist_button_position']['options']['before_add_to_cart_button'] );
		}
		if ( isset( $settings['wishlist_button_position']['options']['after_add_to_cart_button'] ) ) {
			unset( $settings['wishlist_button_position']['options']['after_add_to_cart_button'] );
		}
		if ( isset( $settings['wishlist_button_position']['options']['image_bottom_left'] ) ) {
			unset( $settings['wishlist_button_position']['options']['image_bottom_left'] );
		}
		if ( isset( $settings['wishlist_button_position']['options']['image_bottom_right'] ) ) {
			unset( $settings['wishlist_button_position']['options']['image_bottom_right'] );
		}
		if ( isset( $settings['wishlist_button_position']['options']['flex_image_bottom_left'] ) ) {
			unset( $settings['wishlist_button_position']['options']['flex_image_bottom_left'] );
		}
		if ( isset( $settings['wishlist_button_position']['options']['flex_image_bottom_right'] ) ) {
			unset( $settings['wishlist_button_position']['options']['flex_image_bottom_right'] );
		}
	}

	return $settings;
}


/**
 * Integrate with customizer
 *
 * @return void
 *
 * @since 1.6.6
 */
function wlfmc_after_setup_shoptimizer() {
	if ( class_exists( 'CommerceGurus_Commercekit' ) ) {
		// Color fields.
		shoptimizer_Kirki::add_field(
			'shoptimizer_config',
			array(
				'type'     => 'select',
				'settings' => 'shoptimizer_enable_wishlist_counter',
				'label'    => esc_html__( 'Enable Wishlist Counter', 'wc-wlfmc-wishlist' ),
				'section'  => 'shoptimizer_header_section_layout',
				'default'  => 'disable',
				'choices'  => array(
					'enable'  => esc_attr__( 'Enable', 'wc-wlfmc-wishlist' ),
					'disable' => esc_attr__( 'Disable', 'wc-wlfmc-wishlist' ),

				),
				'priority' => 10,
			)
		);
		// Mobile Header - Text Color.
		shoptimizer_Kirki::add_field(
			'shoptimizer_config',
			array(
				'type'      => 'color',
				'settings'  => 'shoptimizer_mc_counters_text_color',
				'label'     => esc_html__( 'Mobile Mc Counters text color', 'wc-wlfmc-wishlist' ),
				'section'   => 'shoptimizer_color_section_header',
				'default'   => '#333',
				'priority'  => 10,
				'output'    => array(
					array(
						'element'     => '.shoptimizer-primary-navigation .wlfmc-counter-wrapper:not(.is-elementor) .wlfmc-counter-text ,
						 .shoptimizer-primary-navigation .wlfmc-counter-wrapper:not(.is-elementor) .products-number-position-left .wlfmc-counter-number ,
						 .shoptimizer-primary-navigation .wlfmc-counter-wrapper:not(.is-elementor) .products-number-position-right .wlfmc-counter-number',
						'property'    => 'color',
						'media_query' => '@media (max-width: 992px)',
					),
				),
				'transport' => 'postMessage',
				'js_vars'   => array(
					array(
						'element'     => '.shoptimizer-primary-navigation .wlfmc-counter-wrapper:not(.is-elementor) .wlfmc-counter-text ,
						 .shoptimizer-primary-navigation .wlfmc-counter-wrapper:not(.is-elementor) .products-number-position-left .wlfmc-counter-number ,
						 .shoptimizer-primary-navigation .wlfmc-counter-wrapper:not(.is-elementor) .products-number-position-right .wlfmc-counter-number',
						'function'    => 'css',
						'property'    => 'color',
						'media_query' => '@media (max-width: 992px)',
					),
				),
			)
		);
		shoptimizer_Kirki::add_field(
			'shoptimizer_config',
			array(
				'type'      => 'color',
				'settings'  => 'shoptimizer_mc_counters_icon_color',
				'label'     => esc_html__( 'Mobile Mc Counters icon color', 'wc-wlfmc-wishlist' ),
				'section'   => 'shoptimizer_color_section_header',
				'default'   => '#333',
				'priority'  => 10,
				'output'    => array(
					array(
						'element'     => '.shoptimizer-primary-navigation .wlfmc-counter-wrapper:not(.is-elementor) .wlfmc-counter-icon i',
						'property'    => 'color',
						'media_query' => '@media (max-width: 992px)',
					),
				),
				'transport' => 'postMessage',
				'js_vars'   => array(
					array(
						'element'     => '.shoptimizer-primary-navigation .wlfmc-counter-wrapper:not(.is-elementor) .wlfmc-counter-icon i',
						'function'    => 'css',
						'property'    => 'color',
						'media_query' => '@media (max-width: 992px)',
					),
				),
			)
		);
		add_action( 'shoptimizer_navigation', 'wlfmc_shoptimizer_counters_position', 60 );

		if ( defined( 'MC_WLFMC_PREMIUM' ) ) {
			// Display Search on Mobile.
			shoptimizer_Kirki::add_field(
				'shoptimizer_config',
				array(
					'type'     => 'select',
					'settings' => 'shoptimizer_enable_waitlist_counter',
					'label'    => esc_html__( 'Enable Waitlist Counter', 'wc-wlfmc-wishlist' ),
					'section'  => 'shoptimizer_header_section_layout',
					'default'  => 'disable',
					'choices'  => array(
						'enable'  => esc_attr__( 'Enable', 'wc-wlfmc-wishlist' ),
						'disable' => esc_attr__( 'Disable', 'wc-wlfmc-wishlist' ),

					),
					'priority' => 10,
				)
			);
			// Display Search on Mobile.
			shoptimizer_Kirki::add_field(
				'shoptimizer_config',
				array(
					'type'     => 'select',
					'settings' => 'shoptimizer_enable_multi_list_counter',
					'label'    => esc_html__( 'Enable Multi-List Counter', 'wc-wlfmc-wishlist' ),
					'section'  => 'shoptimizer_header_section_layout',
					'default'  => 'disable',
					'choices'  => array(
						'enable'  => esc_attr__( 'Enable', 'wc-wlfmc-wishlist' ),
						'disable' => esc_attr__( 'Disable', 'wc-wlfmc-wishlist' ),

					),
					'priority' => 10,
				)
			);
		}

		add_filter( 'wlfmc_custom_css_output', 'wlfmc_shoptimizer_fix_css' );
	}
}

/**
 * Add counter fields in customizer
 *
 * @return void
 *
 * @since 1.6.6
 */
function wlfmc_shoptimizer_counters_position() {
	if ( ! function_exists( 'shoptimizer_get_option' ) ) {
		return;
	}
	$enable_wishlist   = shoptimizer_get_option( 'shoptimizer_enable_wishlist_counter' );
	$enable_waitlist   = defined( 'MC_WLFMC_PREMIUM' ) ? shoptimizer_get_option( 'shoptimizer_enable_waitlist_counter' ) : false;
	$enable_multi_list = defined( 'MC_WLFMC_PREMIUM' ) ? shoptimizer_get_option( 'shoptimizer_enable_multi_list_counter' ) : false;
	if ( 'enable' === $enable_wishlist || ( defined( 'MC_WLFMC_PREMIUM' ) && ( 'enable' === $enable_waitlist || 'enable' === $enable_multi_list ) ) ) {
		$dropdown = wp_is_mobile() ? ' show_products="false" show_lists="false" ' : '  show_products="true" show_lists="true"  ';
		echo '<div class="wlfmc-shoptimizer-counters d-flex gap-10 space-between f-center-item">';
		if ( 'enable' === $enable_wishlist ) {
			echo '<div>' . do_shortcode( "[wlfmc_wishlist_counter $dropdown]" ) . '</div>';
		}
		if ( defined( 'MC_WLFMC_PREMIUM' ) ) {
			if ( 'enable' === $enable_waitlist ) {
				echo '<div>' . do_shortcode( "[wlfmc_waitlist_counter $dropdown ]" ) . '</div>';
			}
			if ( 'enable' === $enable_multi_list ) {
				echo '<div>' . do_shortcode( "[wlfmc_multi_list_counter $dropdown]" ) . '</div>';
			}
		}
		echo '</div>';
	}

}

/**
 * Generate css plugin
 *
 * @param string $generated_css generated css codes.
 *
 * @return string
 *
 * @since 1.6.6
 */
function wlfmc_shoptimizer_fix_css( string $generated_css ) {
	$generated_css .= '.secondary-navigation .wlfmc-counter-icon {position: absolute;left: 50%;top: 5px;transform: translate(-50%);}.shoptimizer-primary-navigation .gap-10 {gap:10px;}body:not(.rtl) .site-header-cart ~ .wlfmc-shoptimizer-counters { margin-left:10px} .rtl .site-header-cart ~ .wlfmc-shoptimizer-counters { margin-right:10px}';
	return $generated_css;
}
