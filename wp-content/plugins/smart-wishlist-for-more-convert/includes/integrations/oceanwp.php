<?php
/**
 * WLFMC wishlist integration with oceanwp theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_oceanwp_integrate' );
add_action( 'after_setup_theme', 'wlfmc_after_setup_oceanwp' );

/**
 * Oceanwp customizer integrate
 *
 * @return void
 */
function wlfmc_after_setup_oceanwp() {

	if ( class_exists( 'OCEANWP_Theme_Class' ) ) {
		add_action( 'customize_register', 'wlfmc_oceanwp_customizer', 20 );
		add_filter( 'wlfmc_custom_css_output', 'wlfmc_oceanwp_custom_css' );
		add_filter( 'wp_nav_menu_items', 'wlfmc_oceanwp_menu_counter_icons', 10, 2 );
	}

}


/**
 * Add settings to customizer.
 *
 * @param WP_Customize_Manager $wp_customize wp customize.
 * @return void
 */
function wlfmc_oceanwp_customizer( $wp_customize ) {
	/**
	 * Heading Wishlist
	 */
	$wp_customize->add_setting(
		'ocean_mc_wishlist_heading',
		array(
			'sanitize_callback' => 'wp_kses',
		)
	);

	$wp_customize->add_control(
		new OceanWP_Customizer_Heading_Control(
			$wp_customize,
			'ocean_mc_wishlist_heading',
			array(
				'label'    => esc_html__( 'MC Wishlist Counters', 'wc-wlfmc-wishlist' ),
				'section'  => 'ocean_woocommerce_general',
				'priority' => 10,
			)
		)
	);

	$wp_customize->add_setting(
		'header_mc_wishlist_counter',
		array(
			'transport'         => 'refresh',
			'default'           => false,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'header_mc_wishlist_counter_checkbox',
		array(
			'label'    => __( 'Add Mc Wishlist Counter In Header', 'wc-wlfmc-wishlist' ),
			'section'  => 'ocean_woocommerce_general',
			'settings' => 'header_mc_wishlist_counter',
			'type'     => 'checkbox',
		)
	);
	if ( defined( 'MC_WLFMC_PREMIUM' ) ) {
		$wp_customize->add_setting(
			'header_mc_waitlist_counter',
			array(
				'transport'         => 'refresh',
				'default'           => false,
				'sanitize_callback' => 'absint',
			)
		);
		$wp_customize->add_control(
			'header_mc_waitlist_counter_checkbox',
			array(
				'label'    => __( 'Add Mc Waitlist Counter In Header', 'wc-wlfmc-wishlist' ),
				'section'  => 'ocean_woocommerce_general',
				'settings' => 'header_mc_waitlist_counter',
				'type'     => 'checkbox',
			)
		);

		$wp_customize->add_setting(
			'header_mc_lists_counter',
			array(
				'transport'         => 'refresh',
				'default'           => false,
				'sanitize_callback' => 'absint',
			)
		);
		$wp_customize->add_control(
			'header_mc_lists_counter_checkbox',
			array(
				'label'    => __( 'Add Mc Multi-List Counter In Header', 'wc-wlfmc-wishlist' ),
				'section'  => 'ocean_woocommerce_general',
				'settings' => 'header_mc_lists_counter',
				'type'     => 'checkbox',
			)
		);
	}
	$wp_customize->add_setting(
		'header_mc_enable_dropdown',
		array(
			'transport'         => 'refresh',
			'default'           => false,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'header_mc_enable_dropdown_checkbox',
		array(
			'label'    => __( 'Enable Dropdown for all counters', 'wc-wlfmc-wishlist' ),
			'section'  => 'ocean_woocommerce_general',
			'settings' => 'header_mc_enable_dropdown',
			'type'     => 'checkbox',
		)
	);
}

/**
 * Adds wishlist counters to menu
 *
 * @param string $items Existing menu items.
 * @param object $args Nav args.
 */
function wlfmc_oceanwp_menu_counter_icons( $items, $args ) {

	// Return items if is in the Elementor edit mode, to avoid error.
	if ( OCEANWP_ELEMENTOR_ACTIVE && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
		return $items;
	}

	// Return.
	if ( 'main_menu' !== $args->theme_location ) {
		return $items;
	}
	$enable_dropdown = wlfmc_is_true( get_theme_mod( 'header_mc_enable_dropdown', false ) );
	if ( wlfmc_is_true( get_theme_mod( 'header_mc_lists_counter', false ) ) ) {
		$items .= '<li class="woo-wishlist-link menu-item ' . ( $enable_dropdown ? 'menu-item-has-children dropdown' : '' ) . '">';
		$items .= do_shortcode( '[wlfmc_multi_list_counter dropdown_lists="false" show_lists="false"]' );
		if ( $enable_dropdown ) {
			$items .= '<ul class="sub-menu" style="opacity: 0; visibility: hidden;">' . do_shortcode( '[wlfmc_multi_list_counter position_mode="relative" dropdown_lists="false" show_icon="false" show_text="false"]' ) . '</ul>';
		}
		$items .= '</li>';
	}

	if ( wlfmc_is_true( get_theme_mod( 'header_mc_waitlist_counter', false ) ) ) {
		$items .= '<li class="woo-wishlist-link menu-item ' . ( $enable_dropdown ? 'menu-item-has-children dropdown' : '' ) . '">';
		$items .= do_shortcode( '[wlfmc_waitlist_counter add_link_title="true"  dropdown_products="false" show_products="false"]' );
		if ( $enable_dropdown ) {
			$items .= '<ul class="sub-menu" style="opacity: 0; visibility: hidden;">' . do_shortcode( '[wlfmc_waitlist_counter position_mode="relative" dropdown_products="false" show_icon="false" show_text="false"]' ) . '</ul>';
		}
		$items .= '</li>';
	}

	if ( wlfmc_is_true( get_theme_mod( 'header_mc_wishlist_counter', false ) ) ) {
		$items .= '<li class="woo-wishlist-link menu-item ' . ( $enable_dropdown ? 'menu-item-has-children dropdown' : '' ) . '">';
		$items .= do_shortcode( '[wlfmc_wishlist_counter add_link_title="true" dropdown_products="false" show_products="false"]' );
		if ( $enable_dropdown ) {
			$items .= '<ul class="sub-menu" style="opacity: 0; visibility: hidden;">' . do_shortcode( '[wlfmc_wishlist_counter position_mode="relative" dropdown_products="false" show_icon="false" show_text="false"]' ) . '</ul>';
		}
		$items .= '</li>';
	}
	// Return menu items.
	return $items;
}

/**
 * Add inline custom css.
 *
 * @param string $css generated css codes.
 *
 * @return string
 */
function wlfmc_oceanwp_custom_css( $css ) {
	$css .= '
	#site-header.top-header #site-navigation-wrap .wlfmc-counter-wrapper:not(.is-elementor) a.wlfmc-counter {  height: 40px;}
	#site-header.medium-header #site-navigation-wrap .wlfmc-counter-wrapper:not(.is-elementor) a.wlfmc-counter {height: 60px;padding: 0 22px;}
	#site-header.vertical-header .custom-header-nav #site-navigation-wrap #site-navigation .wlfmc-counter-wrapper:not(.is-elementor) a.wlfmc-counter {padding-left: 0;padding-right: 0;}
	#site-navigation-wrap .wlfmc-counter-wrapper:not(.is-elementor) a.wlfmc-counter { height: 74px; padding: 0 15px;}
	#site-navigation-wrap .wlfmc-counter-wrapper:not(.is-elementor) a.wlfmc-counter{padding: 0 15px }
	#site-navigation-wrap .wlfmc-counter-wrapper:not(.is-elementor) .wlfmc-counter-items { border-radius: 0 !important; margin: 0 !important}';
	// Get header & mobile styles.
	$header_style                                  = oceanwp_header_style();
	$header_height                                 = get_theme_mod( 'ocean_header_height', '74' );
	$top_height                                    = get_theme_mod( 'ocean_top_header_height', '40' );
	$medium_header_menu_height                     = get_theme_mod( 'ocean_medium_header_menu_height', '60' );
	$medium_header_menu_items_right_padding        = get_theme_mod( 'ocean_medium_header_menu_items_right_padding', '22' );
	$medium_header_menu_items_left_padding         = get_theme_mod( 'ocean_medium_header_menu_items_left_padding', '22' );
	$medium_header_menu_items_tablet_right_padding = get_theme_mod( 'ocean_medium_header_menu_items_tablet_right_padding' );
	$medium_header_menu_items_tablet_left_padding  = get_theme_mod( 'ocean_medium_header_menu_items_tablet_left_padding' );
	$medium_header_menu_items_mobile_right_padding = get_theme_mod( 'ocean_medium_header_menu_items_mobile_right_padding' );
	$medium_header_menu_items_mobile_left_padding  = get_theme_mod( 'ocean_medium_header_menu_items_mobile_left_padding' );
	$vertical_header_inner_right_padding           = get_theme_mod( 'ocean_vertical_header_inner_right_padding', '30' );
	$vertical_header_inner_left_padding            = get_theme_mod( 'ocean_vertical_header_inner_left_padding', '30' );
	$vertical_header_menu_items_padding            = get_theme_mod( 'ocean_vertical_header_menu_items_padding', '17' );
	$menu_items_padding                            = get_theme_mod( 'ocean_menu_items_padding', '15' );

	// Add header height.
	if ( ( 'top' !== $header_style && 'medium' !== $header_style ) && ! empty( $header_height ) && '74' !== $header_height ) {
		$css .= '#site-navigation-wrap .wlfmc-counter-wrapper:not(.is-elementor) a.wlfmc-counter{height:' . $header_height . 'px;}';
	}

	// Add header height for top header style.
	if ( 'top' === $header_style && ! empty( $top_height ) && '40' !== $top_height ) {
		$css .= '#site-header.top-header #site-navigation-wrap .wlfmc-counter-wrapper:not(.is-elementor) a.wlfmc-counter{height:' . $top_height . 'px;}';
	}
	// Medium header style.
	if ( 'medium' === $header_style ) {
		// Add height menu for medium header style.
		if ( ! empty( $medium_header_menu_height ) && '60' !== $medium_header_menu_height ) {
			$css .= '#site-header.medium-header #site-navigation-wrap .wlfmc-counter-wrapper:not(.is-elementor) a.wlfmc-counter{height:' . $medium_header_menu_height . 'px;}';
		}
		// Header padding.
		if ( isset( $medium_header_menu_items_right_padding ) && '22' !== $medium_header_menu_items_right_padding && '' !== $medium_header_menu_items_right_padding || isset( $medium_header_menu_items_left_padding ) && '22' !== $medium_header_menu_items_left_padding && '' !== $medium_header_menu_items_left_padding ) {
			$css .= '#site-header.medium-header #site-navigation-wrap .wlfmc-counter-wrapper:not(.is-elementor) a.wlfmc-counter{padding:' . oceanwp_spacing_css( '', $medium_header_menu_items_right_padding, '', $medium_header_menu_items_left_padding ) . '}';
		}

		// Tablet header padding.
		if ( isset( $medium_header_menu_items_tablet_right_padding ) && '' !== $medium_header_menu_items_tablet_right_padding || isset( $medium_header_menu_items_tablet_left_padding ) && '' !== $medium_header_menu_items_tablet_left_padding ) {
			$css .= '@media (max-width: 768px){#site-header.medium-header #site-navigation-wrap .wlfmc-counter-wrapper:not(.is-elementor) a.wlfmc-counter{padding:' . oceanwp_spacing_css( '', $medium_header_menu_items_tablet_right_padding, '', $medium_header_menu_items_tablet_left_padding ) . '}}';
		}

		// Mobile header padding.
		if ( isset( $medium_header_menu_items_mobile_right_padding ) && '' !== $medium_header_menu_items_mobile_right_padding || isset( $medium_header_menu_items_mobile_left_padding ) && '' !== $medium_header_menu_items_mobile_left_padding ) {
			$css .= '@media (max-width: 480px){#site-header.medium-header #site-navigation-wrap .wlfmc-counter-wrapper:not(.is-elementor) a.wlfmc-counter{padding:' . oceanwp_spacing_css( '', $medium_header_menu_items_mobile_right_padding, '', $medium_header_menu_items_mobile_left_padding ) . '}}';
		}
	}
	// Vertical header padding.
	if ( 'vertical' === $header_style ) {

		// Vertical header left/right padding.
		if ( isset( $vertical_header_inner_right_padding ) && '30' !== $vertical_header_inner_right_padding && '' !== $vertical_header_inner_right_padding || isset( $vertical_header_inner_left_padding ) && '30' !== $vertical_header_inner_left_padding && '' !== $vertical_header_inner_left_padding ) {
			$css .= '#site-header.vertical-header #site-navigation-wrap .wlfmc-counter-wrapper:not(.is-elementor) a.wlfmc-counter{padding:' . oceanwp_spacing_css( '', $vertical_header_inner_right_padding, '', $vertical_header_inner_left_padding ) . '}';
		}
		// Menu items padding.
		if ( ! empty( $vertical_header_menu_items_padding ) && '17' !== $vertical_header_menu_items_padding ) {
			$css .= '#site-header.vertical-header #site-navigation-wrap .wlfmc-counter-wrapper:not(.is-elementor) a.wlfmc-counter{padding-top:' . $vertical_header_menu_items_padding . 'px; padding-bottom:' . $vertical_header_menu_items_padding . 'px;}';
		}
	}

	// Menu items padding.
	if ( ! empty( $menu_items_padding ) && '15' !== $menu_items_padding ) {
		$css .= '#site-navigation-wrap .wlfmc-counter-wrapper:not(.is-elementor) a.wlfmc-counter{padding: 0 ' . $menu_items_padding . 'px;}';
	}
	return $css;
}


/**
 * Integration with Oceanwp theme
 *
 * @return void
 */
function wlfmc_oceanwp_integrate() {

	if ( class_exists( 'OCEANWP_Theme_Class' ) ) {

		add_filter( 'wlfmc_button_positions', 'wlfmc_oceanwp_fix_single_position' );
		add_filter( 'wlfmc_loop_positions', 'wlfmc_oceanwp_fix_loop_position' );

		// fix inline loop buttons.
		$options       = new MCT_Options( 'wlfmc_options' );
		$loop_position = $options->get_option( 'loop_position', 'after_add_to_cart' );

		if ( in_array( $loop_position, array( 'before_add_to_cart', 'after_add_to_cart' ), true ) ) {

			add_action( 'ocean_before_archive_product_add_to_cart_inner', 'wlfmc_oceanwp_inline_btn_loop_start', 9 );
			add_action( 'ocean_after_archive_product_add_to_cart_inner', 'wlfmc_oceanwp_inline_btn_loop_end', 11 );

		}
	}

}

/**
 * Start add wrapper for button position in loop
 *
 * @return void
 */
function wlfmc_oceanwp_inline_btn_loop_start() {
	echo '<div class="wlfmc-flex-buttons">';
}

/**
 * End add wrapper for button position in loop
 *
 * @return void
 */
function wlfmc_oceanwp_inline_btn_loop_end() {
	echo '</div>';
}

/**
 * Fix single position
 *
 * @param array $positions all single positions.
 *
 * @return array
 */
function wlfmc_oceanwp_fix_single_position( array $positions ): array {

	$positions['image_top_left']     = array(
		array(
			'hook'     => 'woocommerce_product_thumbnails',
			'priority' => '21',
		),
		array(
			'hook'     => 'ocean_woo_quick_view_product_image',
			'priority' => '50',
		),
	);
	$positions['image_top_right']    = array(
		array(
			'hook'     => 'woocommerce_product_thumbnails',
			'priority' => '21',
		),
		array(
			'hook'     => 'ocean_woo_quick_view_product_image',
			'priority' => '50',
		),
	);
	$positions['image_bottom_left']  = array(
		array(
			'hook'     => 'woocommerce_product_thumbnails',
			'priority' => '21',
		),
		array(
			'hook'     => 'ocean_woo_quick_view_product_image',
			'priority' => '50',
		),
	);
	$positions['image_bottom_right'] = array(
		array(
			'hook'     => 'woocommerce_product_thumbnails',
			'priority' => '21',
		),
		array(
			'hook'     => 'ocean_woo_quick_view_product_image',
			'priority' => '50',
		),
	);
	$positions['summary']            = array(
		array(
			'hook'     => 'ocean_woo_quick_view_product_content',
			'priority' => '100',
		),
		array(
			'hook'     => 'woocommerce_single_product_summary',
			'priority' => '100',
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
function wlfmc_oceanwp_fix_loop_position( array $positions ): array {

	$positions['before_add_to_cart']['hook']     = 'ocean_before_archive_product_add_to_cart_inner';
	$positions['before_add_to_cart']['priority'] = '10';
	$positions['after_add_to_cart']['hook']      = 'ocean_after_archive_product_add_to_cart_inner';
	$positions['after_add_to_cart']['priority']  = '10';
	$positions['image_top_left']['hook']         = 'ocean_before_archive_product_image';
	$positions['image_top_right']['hook']        = 'ocean_before_archive_product_image';

	return $positions;
}
