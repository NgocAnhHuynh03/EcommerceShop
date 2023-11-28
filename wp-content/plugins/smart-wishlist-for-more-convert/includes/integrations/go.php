<?php
/**
 * WLFMC wishlist integration with go theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.6.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'after_setup_theme', 'wlfmc_after_setup_go' );

/**
 * Go theme integrate
 *
 * @return void
 */
function wlfmc_after_setup_go() {

	if ( defined( 'GO_VERSION' ) ) {
		add_action( 'go_header_social_icons', 'wlfmc_go_header_counters' );
		add_action( 'customize_register', 'wlfmc_go_customizer', 20 );
	}

}

/**
 * Add settings to customizer.
 *
 * @param WP_Customize_Manager $wp_customize wp customize.
 * @return void
 */
function wlfmc_go_customizer( $wp_customize ) {
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
			'label'    => __( 'Mc Wishlist Counter', 'wc-wlfmc-wishlist' ),
			'section'  => 'go_header_settings',
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
				'label'    => __( 'Mc Waitlist Counter', 'wc-wlfmc-wishlist' ),
				'section'  => 'go_header_settings',
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
				'label'    => __( 'Mc Multi-List Counter', 'wc-wlfmc-wishlist' ),
				'section'  => 'go_header_settings',
				'settings' => 'header_mc_lists_counter',
				'type'     => 'checkbox',
			)
		);
	}
}

/**
 * Add Counters to header
 *
 * @return void
 */
function wlfmc_go_header_counters() {
	$enable_wishlist   = get_theme_mod( 'header_mc_wishlist_counter', false );
	$enable_waitlist   = get_theme_mod( 'header_mc_waitlist_counter', false );
	$enable_multi_list = get_theme_mod( 'header_mc_lists_counter', false );
	if ( $enable_wishlist || ( defined( 'MC_WLFMC_PREMIUM' ) && ( $enable_waitlist || $enable_multi_list ) ) ) {
		$position   = is_rtl() ? " products_number_position='top-left' " : " products_number_position='top-right' ";
		$attributes = wp_is_mobile() ? 'show_text="false" show_products="false" show_lists="false" ' : ' show_text="false" show_products="true" show_lists="true"  ';
		if ( $enable_wishlist ) {
			echo do_shortcode( "[wlfmc_wishlist_counter $attributes $position]" );
		}
		if ( defined( 'MC_WLFMC_PREMIUM' ) ) {
			if ( $enable_waitlist ) {
				echo do_shortcode( "[wlfmc_waitlist_counter $attributes $position ]" );
			}
			if ( $enable_multi_list ) {
				echo do_shortcode( "[wlfmc_multi_list_counter $attributes $position]" );
			}
		}
	}
}
