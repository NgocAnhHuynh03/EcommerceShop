<?php
/**
 * WLFMC wishlist integration with divi theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.6.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_divi_integrate' );
add_action( 'after_setup_theme', 'wlfmc_divi' );

/**
 * Integration with Divi theme
 *
 * @return void
 *
 * @since 1.6.6
 */
function wlfmc_divi() {
	if ( function_exists( 'et_setup_theme' ) ) {
		add_action( 'customize_preview_init', 'wlfmc_divi_customizer_scripts' );
		add_filter( 'body_class', 'wlfmc_divi_customize_preview_class' );
		add_filter( 'wlfmc_custom_css_output', 'wlfmc_divi_fix_css' );
		add_action( 'et_header_top', 'wlfmc_add_wishlist_counter_to_divi_header' );
		add_action( 'customize_register', 'wlfmc_divi_et_setup_theme', 0 );
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
function wlfmc_divi_fix_css( string $generated_css ) {
	if ( et_get_option( 'show_wishlist_counter', true ) || is_customize_preview() ) {
		$generated_css .= '
			.et-wlfmc-counters .wlfmc-counter-icon i { font-size:17px !important;}
			.et_pb_menu_hidden .et-wlfmc-counters {
			    opacity: 0;
			    -webkit-animation: fadeOutBottom 1s 1 cubic-bezier(0.77, 0, 0.175, 1);
			    animation: fadeOutBottom 1s 1 cubic-bezier(0.77, 0, 0.175, 1);
			}
			.et_pb_menu_visible .et-wlfmc-counters {
			    z-index: 99;
			    opacity: 1;
			    -webkit-animation: fadeInBottom 1s 1 cubic-bezier(0.77, 0, 0.175, 1);
			    animation: fadeInBottom 1s 1 cubic-bezier(0.77, 0, 0.175, 1);
			}
			/** centered */
			.et_header_style_centered .et-wlfmc-counters{
				float:none;display:inline-block !important;
				margin: 0px 0 0 32px;
			}
			@media all and (max-width: 980px) {
				.et_header_style_centered .et-wlfmc-counters{
					display:none !important}
			}	
			/** header */
			.et-wlfmc-counters {
				float: right;
				margin: 0 0 0 14px;
				position: relative;
				display: block;
			}
			@media all and (max-width: 980px) {
				.et-wlfmc-counters {
					margin: 8px 0 0 30px;  float: left;
				}
			}
			/** slide in, fullscreen */
			.et_header_style_fullscreen .et-wlfmc-counters,.et_header_style_slide .et-wlfmc-counters { 
				display: inline-block !important;
				float: none;
				margin: 0;
				position: absolute;
				right: 40px;
				transform: translateY( -50% );
				top: 50%;
			}
			/** split header */
			.et_header_style_split .et-wlfmc-counters {
				display: none !important;
			}
			/** vertical */
			.et_header_style_split.et_vertical_nav .et-wlfmc-counters {
				display: block;
			}
			.et_vertical_nav .et-wlfmc-counters {
				margin-left: 0;
			}
			.et_vertical_nav.et_header_style_centered #main-header .et-wlfmc-counters {
					display: none !important;
			}
			.et_vertical_nav #main-header .et-wlfmc-counters {
					display: none !important;
			}
			@media all and (min-width: 981px) {
				.et_vertical_nav div.et-wlfmc-counters {
					width: 100%;
				}
				.et_vertical_nav .et_pb_menu_hidden .et-wlfmc-counters {
					opacity: 0;
			
				}
			}
		
			/** rtl */
			.rtl .et-wlfmc-counters {
				float: left;
			}
			.rtl.et_header_style_left .et-wlfmc-counters {
				float: right;
			}
			.rtl.et_header_style_left .et-wlfmc-counters {
				margin: 0 14px 0 0;
			}
			.rtl.et_header_style_fullscreen .et-wlfmc-counters,.rtl.et_header_style_slide .et-wlfmc-counters {
				right:auto;
				float: none !important;
				margin:0 !important;
			}
			.rtl.et_header_style_fullscreen .et-wlfmc-counters {
				left:40px;
			}
			@media all and (max-width: 980px) {
				.rtl.et_header_style_left .et-wlfmc-counters{
					float: left;
				}
				.rtl.et_header_style_left .et-wlfmc-counters {
					margin: 8px 30px 0 0;
					float: right;
				}
			}
			.wlfmc-elementor.wlfmc-wishlist-counter {
				z-index:100000 !important
			}
			';
	}
	return $generated_css;
}

/**
 * Enqueue scripts
 *
 * @return void
 *
 * @since 1.6.6
 */
function wlfmc_divi_customizer_scripts() {
	wp_add_inline_script(
		'divi-customizer',
		'(function ($) {
                jQuery(document).ready(function (e) {
                  console.log(1);
                    wp.customize("et_divi[show_wishlist_counter]", function (e) {
						e.bind(function (e) {
							var o = jQuery("body");
							e ? o.removeClass("et_hide_wishlist_counter") : o.addClass("et_hide_wishlist_counter");
						});
					});
			
                    });
                })(jQuery);'
	);
}

/**
 * Add body class
 *
 * @param  array $classes body classes.
 * @return array
 *
 * @since 1.6.6
 */
function wlfmc_divi_customize_preview_class( $classes ) {
	if ( is_customize_preview() ) {
		// wishlist counter icon state.
		if ( ! et_get_option( 'show_wishlist_counter', true ) ) {
			$classes[] = 'et_hide_wishlist_counter';
		}
	}

	return $classes;
}
/**
 * Add settings to customizer.
 *
 * @param WP_Customize_Manager $wp_customize wp customize.
 * @return void
 *
 * @since 1.6.6
 */
function wlfmc_divi_et_setup_theme( $wp_customize ) {
	$wp_customize->add_setting(
		'et_divi[show_wishlist_counter]',
		array(
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'transport'         => 'postMessage',
			'sanitize_callback' => 'wp_validate_boolean',
		)
	);

	$wp_customize->add_control(
		'et_divi[show_wishlist_counter]',
		array(
			'label'   => esc_html__( 'Show Wishlist Counter', 'wc-wlfmc-wishlist' ),
			'section' => 'et_divi_header_primary',
			'type'    => 'checkbox',
		)
	);
}

/**
 * Print wishlist counter in the header position.
 *
 * @return void
 *
 * @since 1.6.6
 */
function wlfmc_add_wishlist_counter_to_divi_header() {
	$show_wishlist_counter = et_get_option( 'show_wishlist_counter', 'false' );
	if ( wlfmc_is_true( $show_wishlist_counter ) || is_customize_preview() ) {
		echo '<style>.et_hide_wishlist_counter #et_wishlist_counter {display:none }</style>';
		echo '<div class="et-wlfmc-counters" id="et_wishlist_counter">' . do_shortcode( '[wlfmc_wishlist_counter position_mode="fixed" show_text="false"]' ) . '</div>';
	}
}
/**
 * Integration with Divi theme
 *
 * @return void
 */
function wlfmc_divi_integrate() {

	if ( function_exists( 'et_setup_theme' ) ) {

		add_filter( 'wlfmc_loop_positions', 'wlfmc_divi_fix_loop_position' );

	}

}

/**
 * Fix loop position
 *
 * @param array $positions all loop positions.
 *
 * @return array
 */
function wlfmc_divi_fix_loop_position( array $positions ): array {

	$positions['image_top_left']['hook']  = 'woocommerce_after_shop_loop_item';
	$positions['image_top_right']['hook'] = 'woocommerce_after_shop_loop_item';

	return $positions;
}

