<?php
/**
 * WLFMC wishlist integration with GeneratePress theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'after_setup_theme', 'wlfmc_after_setup_generatepress' );

/**
 * GeneratePress theme integrate
 *
 * @return void
 */
function wlfmc_after_setup_generatepress() {

	if ( defined( 'GENERATE_VERSION' ) ) {
		add_action( 'generate_menu_bar_items', 'wlfmc_generatepress_header_counters' );
		add_action( 'customize_register', 'wlfmc_generatepress_customizer', 20 );
		add_filter( 'wlfmc_custom_css_output', 'wlfmc_generatepress_custom_css' );
	}

}

/**
 * Add settings to customizer.
 *
 * @param WP_Customize_Manager $wp_customize wp customize.
 * @return void
 */
function wlfmc_generatepress_customizer( $wp_customize ) {
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
			'section'  => 'generate_layout_header',
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
				'section'  => 'generate_layout_header',
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
				'section'  => 'generate_layout_header',
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
function wlfmc_generatepress_header_counters() {
	$enable_wishlist   = get_theme_mod( 'header_mc_wishlist_counter', false );
	$enable_waitlist   = get_theme_mod( 'header_mc_waitlist_counter', false );
	$enable_multi_list = get_theme_mod( 'header_mc_lists_counter', false );
	if ( $enable_wishlist || ( defined( 'MC_WLFMC_PREMIUM' ) && ( $enable_waitlist || $enable_multi_list ) ) ) {
		$position   = is_rtl() ? " products_number_position='top-left' " : " products_number_position='top-right' ";
		$attributes = wp_is_mobile() ? 'show_text="false" show_products="false" show_lists="false" ' : ' show_text="false" show_products="true" show_lists="true"  ';
		echo '<div class="wlfmc-generatepress-counters d-flex gap-10 space-between f-center-item">';
		if ( $enable_wishlist ) {
			echo '<div>' . do_shortcode( "[wlfmc_wishlist_counter $attributes $position]" ) . '</div>';
		}
		if ( defined( 'MC_WLFMC_PREMIUM' ) ) {
			if ( $enable_waitlist ) {
				echo '<div>' . do_shortcode( "[wlfmc_waitlist_counter $attributes $position ]" ) . '</div>';
			}
			if ( $enable_multi_list ) {
				echo '<div>' . do_shortcode( "[wlfmc_multi_list_counter $attributes $position]" ) . '</div>';
			}
		}
		echo '</div>';
	}
}

/**
 * Add inline custom css.
 *
 * @param string $generated_code generated css codes.
 *
 * @return string
 */
function wlfmc_generatepress_custom_css( $generated_code ) {
	$generated_code .= '.wlfmc-generatepress-counters {gap:15px;}';
	return $generated_code;
}
