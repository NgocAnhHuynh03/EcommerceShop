<?php
/**
 * WLFMC wishlist integration with woostify theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.5.6
 * @version 1.6.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_woostify_integrate' );
add_filter( 'wlfmc_wishlist_button_settings', 'wlfmc_woostify_wishlist_settings' );
add_action( 'after_setup_theme', 'wlfmc_after_setup_woostify' );

/**
 * Integration with woostify theme
 *
 * @return void
 */
function wlfmc_woostify_integrate() {

	if ( defined( 'WOOSTIFY_VERSION' ) ) {

		add_filter( 'wlfmc_loop_positions', 'wlfmc_woostify_fix_loop_position' );
		add_filter( 'wlfmc_button_positions', 'wlfmc_woostify_fix_single_position' );
		add_action( 'woocommerce_before_shop_loop_item_title', 'wlfmc_woostify_fix_wishlist_icon_bottom', 80 );
		add_filter( 'wlfmc_custom_css_output', 'wlfmc_woostify_custom_css' );
		add_filter( 'woostify_single_product_group_buttons', 'wlfmc_woostify_fix_single_bottom_on_image_position' );

	}

}

/**
 * Woostify integrate
 *
 * @return void
 *
 * @since 1.6.6
 */
function wlfmc_after_setup_woostify() {

	if ( defined( 'WOOSTIFY_VERSION' ) ) {
		add_action( 'woostify_site_tool_before_second_item', 'wlfmc_woostify_header_counters' );
		add_action( 'customize_register', 'wlfmc_woostify_customizer', 20 );
	}

}

/**
 * Add settings to customizer.
 *
 * @param WP_Customize_Manager $wp_customize wp customize.
 * @return void
 *
 * @since 1.6.6
 */
function wlfmc_woostify_customizer( $wp_customize ) {
	$wp_customize->add_setting(
		'woostify_setting[header_mc_wishlist_counter]',
		array(
			'type'              => 'option',
			'default'           => false,
			'sanitize_callback' => 'woostify_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		new Woostify_Switch_Control(
			$wp_customize,
			'woostify_setting[header_mc_wishlist_counter]',
			array(
				'priority' => 131,
				'label'    => __( 'Mc Wishlist Counter', 'wc-wlfmc-wishlist' ),
				'section'  => 'woostify_header',
				'settings' => 'woostify_setting[header_mc_wishlist_counter]',
				'tab'      => 'general',
			)
		)
	);
	if ( defined( 'MC_WLFMC_PREMIUM' ) ) {
		$wp_customize->add_setting(
			'woostify_setting[header_mc_waitlist_counter]',
			array(
				'type'              => 'option',
				'default'           => false,
				'sanitize_callback' => 'woostify_sanitize_checkbox',
			)
		);
		$wp_customize->add_control(
			new Woostify_Switch_Control(
				$wp_customize,
				'woostify_setting[header_mc_waitlist_counter]',
				array(
					'priority' => 132,
					'label'    => __( 'Mc Waitlist Counter', 'wc-wlfmc-wishlist' ),
					'section'  => 'woostify_header',
					'settings' => 'woostify_setting[header_mc_waitlist_counter]',
					'tab'      => 'general',
				)
			)
		);

		$wp_customize->add_setting(
			'woostify_setting[header_mc_lists_counter]',
			array(
				'type'              => 'option',
				'default'           => false,
				'sanitize_callback' => 'woostify_sanitize_checkbox',
			)
		);
		$wp_customize->add_control(
			new Woostify_Switch_Control(
				$wp_customize,
				'woostify_setting[header_mc_lists_counter]',
				array(
					'priority' => 133,
					'label'    => __( 'Mc Lists Counter', 'wc-wlfmc-wishlist' ),
					'section'  => 'woostify_header',
					'settings' => 'woostify_setting[header_mc_lists_counter]',
					'tab'      => 'general',
				)
			)
		);
	}
}

/**
 * Add Counters to header
 *
 * @return void
 *
 * @since 1.6.6
 */
function wlfmc_woostify_header_counters() {
	$options           = woostify_options( false );
	$enable_wishlist   = wlfmc_is_true( $options['header_mc_wishlist_counter'] ?? false );
	$enable_waitlist   = wlfmc_is_true( $options['header_mc_waitlist_counter'] ?? false );
	$enable_multi_list = wlfmc_is_true( $options['header_mc_lists_counter'] ?? false );
	if ( $enable_wishlist || ( defined( 'MC_WLFMC_PREMIUM' ) && ( $enable_waitlist || $enable_multi_list ) ) ) {
		$position   = is_rtl() ? " products_number_position='top-left' " : " products_number_position='top-right' ";
		$attributes = wp_is_mobile() ? 'show_text="false" show_products="false" show_lists="false" ' : ' show_text="false" show_products="true" show_lists="true"  ';
		echo '<div class="wlfmc-woostify-counters d-flex gap-10 space-between f-center-item">';
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
 * Add hook to add wishlist button to group buttons
 *
 * @param string $output group button html.
 *
 * @return string
 */
function wlfmc_woostify_fix_single_bottom_on_image_position( $output ) {
	ob_start();
	do_action( 'wlfmc_woostify_single_product_group_buttons' );
	return ob_get_clean() . $output;
}

/**
 * Add inline custom css.
 *
 * @param string $generated_code generated css codes.
 *
 * @return string
 *
 * @version 1.6.6
 */
function wlfmc_woostify_custom_css( $generated_code ) {
	$generated_code .= '.wlfmc-woostify-counters {gap:15px;}body:not(.rtl) .wlfmc-woostify-counters { margin-left:15px} .rtl .wlfmc-woostify-counters { margin-right:15px}.product-loop-action .wlfmc_position_woostify { display:block;}.woostify-container .product-group-btns .wlfmc-top-of-image{ position:relative;top:0;right:0;left:0;}';
	return $generated_code;
}

/**
 * Fix loop position
 *
 * @param array $positions all loop positions.
 *
 * @return array
 */
function wlfmc_woostify_fix_loop_position( array $positions ): array {
	$options = woostify_options( false );
	if ( 'top-right' === $options['shop_page_wishlist_position'] ) {
		$positions['woostify']['hook']     = 'woostify_product_loop_item_action_item';
		$positions['woostify']['priority'] = 7;
	} else {
		$positions['woostify']['hook']     = 'wlfmc_woostify_loop_position_bottom';
		$positions['woostify']['priority'] = 7;
	}

	return $positions;
}

/**
 * Product loop wishlist icon on bottom right
 */
function wlfmc_woostify_fix_wishlist_icon_bottom() {
	$options = woostify_options( false );
	if ( 'bottom-right' !== $options['shop_page_wishlist_position'] ) {
		return;
	}
	?>

	<div class="loop-wrapper-wishlist">
		<?php do_action( 'wlfmc_woostify_loop_position_bottom' ); ?>
	</div>
	<?php
}

/**
 * Fix single position
 *
 * @param array $positions all single positions.
 *
 * @return array
 */
function wlfmc_woostify_fix_single_position( array $positions ): array {

	$positions['summary']            = array(
		'hook'     => 'woocommerce_single_product_summary',
		'priority' => '210',
	);
	$positions['image_top_left']     = array(
		array(
			'hook'     => 'woostify_product_images_box_end',
			'priority' => '30',
		),
	);
	$positions['image_top_right']    = array(
		array(
			'hook'     => 'woostify_product_images_box_end',
			'priority' => '30',
		),
	);
	$positions['image_bottom_left']  = array(
		array(
			'hook'     => 'woostify_product_images_box_end',
			'priority' => '30',
		),
	);
	$positions['image_bottom_right'] = array(
		array(
			'hook'     => 'wlfmc_woostify_single_product_group_buttons',
			'priority' => '30',
		),
	);

	return $positions;
}

/**
 * Fix position with woostify
 *
 * @param array $settings wishlist loop position settings.
 *
 * @return array
 */
function wlfmc_woostify_wishlist_settings( array $settings ): array {

	if ( isset( $settings['loop_position'] ) ) {

		$settings['loop_position']['options'] = array(
			'woostify'           => __( 'Default Woostify Theme', 'wc-wlfmc-wishlist' ),
			'image_top_left'     => __( 'On image - top left', 'wc-wlfmc-wishlist' ),
			'image_top_right'    => __( 'On image - top right', 'wc-wlfmc-wishlist' ),
			'before_add_to_cart' => __( 'Before "add to cart" button', 'wc-wlfmc-wishlist' ),
			'after_add_to_cart'  => __( 'After "add to cart" button', 'wc-wlfmc-wishlist' ),
			'shortcode'          => __( 'Use shortcode', 'wc-wlfmc-wishlist' ),
		);
		$settings['loop_position']['default'] = 'woostify';

	}
	return $settings;
}
