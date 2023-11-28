<?php
/**
 * WLFMC wishlist integration with flatsome theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
add_action( 'after_setup_theme', 'wlfmc_flatsome' );

/**
 * Flatsome integrate
 *
 * @return void
 */
function wlfmc_flatsome() {
	if ( ! class_exists( 'Flatsome_Default' ) ) {
		return;
	}
	add_action( 'flatsome_header_elements', 'wlfmc_flatsome_header_print_elements' );
	add_filter( 'flatsome_header_element', 'wlfmc_flatsome_header_element' );
	add_action( 'customize_controls_print_styles', 'enqueue_customizer_scripts' );

	/**
	 * Enqueue scripts
	 *
	 * @return void
	 */
	function enqueue_customizer_scripts() {
		wp_add_inline_script(
			'flatsome-customizer-admin-js',
			'(function ($) {
                    jQuery(document).ready(function (e) {
                        var wishlist = $(\'.header-builder span[data-id="header-wishlist-counter"]\'),
                            waitlist = $(\'.header-builder span[data-id="header-waitlist-counter"]\'),
                            multiList = $(\'.header-builder span[data-id="header-multi-list-counter"]\');
                            if (waitlist.length) {
                                waitlist.attr("data-section", "header_waitlist_counter");
                                waitlist.on("click", function (t) {
                                    var a = e(this).data("section");
                                    wp.customize.section(a) && wp.customize.section(a).focus(), t.preventDefault();
                                });
                            }
                            
                            if (multiList.length) {
                                multiList.attr("data-section", "header_multi_list_counter");
                                multiList.on("click", function (t) {
                                    var a = e(this).data("section");
                                    wp.customize.section(a) && wp.customize.section(a).focus(), t.preventDefault();
                                });
                            }
                            
                             if (wishlist.length) {
                                wishlist.attr("data-section", "header_wishlist_counter");
                                wishlist.on("click", function (t) {
                                    var a = e(this).data("section");
                                    wp.customize.section(a) && wp.customize.section(a).focus(), t.preventDefault();
                                });
                            }
                        });
                    })(jQuery);'
		);
	}

	/**
	 * Print element
	 *
	 * @param string $element flatsome element slug.
	 * @return void
	 */
	function wlfmc_flatsome_header_print_elements( $element ) {
		if ( in_array( $element, array( 'header-wishlist-counter', 'header-waitlist-counter', 'header-multi-list-counter' ), true ) ) {
			echo wlfmc_flatsome_header_elements( $element );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Output element
	 *
	 * @param string $element flatsome element slug.
	 * @return void|string
	 */
	function wlfmc_flatsome_header_elements( $element ) {
		if ( 'header-wishlist-counter' === $element ) {
			$enable_title    = wlfmc_is_true( get_theme_mod( 'mc_wishlist_title', true ) );
			$title           = get_theme_mod( 'header_mc_wishlist_label', '' );
			$title_shortcode = $enable_title && '' !== $title ? 'show_text="true" counter_text="' . esc_attr( $title ) . '"' : '';
			$enable_dropdown = wlfmc_is_true( get_theme_mod( 'mc_dropdown_products', true ) );
			ob_start();
			?>
			<li class="wlfmc-header-counter-item header-wishlist-counter wishlist-counter-item <?php echo $enable_dropdown ? 'has-dropdown' : ''; ?>">
				<?php echo do_shortcode( '[wlfmc_wishlist_counter dropdown_products="false" ' . $title_shortcode . ' add_link_title="true" show_products="false"]' ); ?>
				<?php if ( $enable_dropdown ) : ?>
					<div class="nav-dropdown <?php flatsome_dropdown_classes(); ?>">
						<?php echo do_shortcode( '[wlfmc_wishlist_counter position_mode="relative" dropdown_products="false" show_icon="false" show_text="false"]' ); ?>
					</div>
				<?php endif; ?>
			</li>
			<?php
			return ob_get_clean();
		}
		if ( defined( 'MC_WLFMC_PREMIUM' ) && 'header-waitlist-counter' === $element ) {
			$enable_title    = wlfmc_is_true( get_theme_mod( 'mc_waitlist_title', true ) );
			$title           = get_theme_mod( 'header_mc_waitlist_label', '' );
			$title_shortcode = $enable_title && '' !== $title ? 'show_text="true" counter_text="' . esc_attr( $title ) . '"' : '';
			$enable_dropdown = wlfmc_is_true( get_theme_mod( 'mc_waitlist_dropdown_products', true ) );
			ob_start();
			?>
			<li class="wlfmc-header-counter-item header-waitlist-counter waitlist-counter-item <?php echo $enable_dropdown ? 'has-dropdown' : ''; ?>">
				<?php echo do_shortcode( '[wlfmc_waitlist_counter dropdown_products="false" ' . $title_shortcode . ' add_link_title="true" show_products="false"]' ); ?>
				<?php if ( $enable_dropdown ) : ?>
					<div class="nav-dropdown <?php flatsome_dropdown_classes(); ?>">
						<?php echo do_shortcode( '[wlfmc_waitlist_counter position_mode="relative" dropdown_products="false" show_icon="false" show_text="false"]' ); ?>
					</div>
				<?php endif; ?>
			</li>
			<?php
			return ob_get_clean();
		}
		if ( defined( 'MC_WLFMC_PREMIUM' ) && 'header-multi-list-counter' === $element ) {
			$enable_title    = wlfmc_is_true( get_theme_mod( 'mc_multi_list_title', true ) );
			$title           = get_theme_mod( 'header_mc_multi_list_label', '' );
			$title_shortcode = $enable_title && '' !== $title ? 'show_text="true" counter_text="' . esc_attr( $title ) . '"' : '';
			$enable_dropdown = wlfmc_is_true( get_theme_mod( 'mc_multi_list_dropdown_lists', true ) );
			ob_start();
			?>
			<li class="wlfmc-header-counter-item header-multi-list-counter multi-list-counter-item <?php echo $enable_dropdown ? 'has-dropdown' : ''; ?>">
				<?php echo do_shortcode( '[wlfmc_multi_list_counter dropdown_lists="false" ' . $title_shortcode . ' add_link_title="true" show_lists="false"]' ); ?>
				<?php if ( $enable_dropdown ) : ?>
					<div class="nav-dropdown <?php flatsome_dropdown_classes(); ?>">
						<?php echo do_shortcode( '[wlfmc_multi_list_counter position_mode="relative" dropdown_lists="false" show_icon="false" show_text="false"]' ); ?>
					</div>
				<?php endif; ?>
			</li>
			<?php
			return ob_get_clean();
		}
	}

	/**
	 * Add flatsome header element
	 *
	 * @param array $elements flatsome header elements.
	 * @return array
	 */
	function wlfmc_flatsome_header_element( $elements ) {
		$elements['header-wishlist-counter'] = __( 'Wishlist Counter', 'wc-wlfmc-wishlist' );
		if ( defined( 'MC_WLFMC_PREMIUM' ) ) {
			$elements['header-waitlist-counter']   = __( 'Waitlist Counter', 'wc-wlfmc-wishlist' );
			$elements['header-multi-list-counter'] = __( 'Multi-List Counter', 'wc-wlfmc-wishlist' );
		}
		return $elements;
	}

	/**
	 * Add sections to header
	 *
	 * @param WP_Customize_Manager $wp_customize wp customize.
	 * @return void
	 */
	function wlfmc_flatsome_refresh_counters_partials( WP_Customize_Manager $wp_customize ) {
		if ( ! isset( $wp_customize->selective_refresh ) ) {
			return;
		}
		// wishlist counter.
		$wp_customize->selective_refresh->add_partial(
			'header-wishlist-counter',
			array(
				'selector'            => '.header .wishlist-counter-item',
				'container_inclusive' => true,
				'settings'            => array(
					'header_wishlist_help',
					'mc_wishlist_title',
					'header_mc_wishlist_label',
					'mc_dropdown_products',
				),
				'render_callback'     => wlfmc_flatsome_header_elements( 'header-wishlist-counter' ),
			)
		);
		if ( defined( 'MC_WLFMC_PREMIUM' ) ) {
			// waitlist counter.
			$wp_customize->selective_refresh->add_partial(
				'header-waitlist-counter',
				array(
					'selector'            => '.header .waitlist-counter-item',
					'container_inclusive' => true,
					'settings'            => array(
						'header_waitlist_help',
						'mc_waitlist_title',
						'header_mc_waitlist_label',
						'mc_waitlist_dropdown_products',
					),
					'render_callback'     => wlfmc_flatsome_header_elements( 'header-wishlist-counter' ),
				)
			);
			// multi-list counter.
			$wp_customize->selective_refresh->add_partial(
				'header-multi-list-counter',
				array(
					'selector'            => '.header .multi-list-counter-item',
					'container_inclusive' => true,
					'settings'            => array(
						'header_multi_list_help',
						'mc_multi_list_title',
						'header_mc_multi_list_label',
						'mc_multi_list_dropdown_lists',
					),
					'render_callback'     => wlfmc_flatsome_header_elements( 'header-wishlist-counter' ),
				)
			);
		}
	}

	add_action( 'customize_register', 'wlfmc_flatsome_refresh_counters_partials' );

	$transport = 'postMessage';
	if ( ! isset( $wp_customize->selective_refresh ) ) {
		$transport = 'refresh';
	}
	$section      = 'header_wishlist_counter';
	$counter_link = add_query_arg(
		array(
			'page' => 'mc-wishlist-settings',
			'tab'  => 'counter',
		),
		admin_url( 'admin.php' )
	);
	Flatsome_Option::add_section(
		$section,
		array(
			'title'    => __( 'Wishlist Counter', 'wc-wlfmc-wishlist' ),
			'panel'    => 'header',
			'priority' => 110,
		)
	);
	Flatsome_Option::add_field(
		'option',
		array(
			'type'      => 'checkbox',
			'settings'  => 'mc_wishlist_title',
			'label'     => __( 'Show Wishlist Title', 'wc-wlfmc-wishlist' ),
			'section'   => $section,
			'transport' => $transport,
			'default'   => 1,
		)
	);

	Flatsome_Option::add_field(
		'option',
		array(
			'type'            => 'text',
			'settings'        => 'header_mc_wishlist_label',
			'label'           => __( 'Custom Title', 'wc-wlfmc-wishlist' ),
			'section'         => $section,
			'transport'       => $transport,
			'active_callback' => array(
				array(
					'setting'  => 'mc_wishlist_title',
					'operator' => '==',
					'value'    => '1',
				),
			),
			'default'         => '',
		)
	);

	Flatsome_Option::add_field(
		'option',
		array(
			'type'      => 'checkbox',
			'settings'  => 'mc_dropdown_products',
			'label'     => __( 'Show Dropdown Products', 'wc-wlfmc-wishlist' ),
			'section'   => $section,
			'transport' => $transport,
			'default'   => 1,
		)
	);

	Flatsome_Option::add_field(
		'',
		array(
			'type'      => 'custom',
			'settings'  => 'header_wishlist_help',
			'label'     => '',
			'section'   => $section,
			'transport' => $transport,
			/* translators: %s: wishlist counter setting page */
			'default'   => sprintf( __( 'To change the settings of this section, please refer to the %s.', 'wc-wlfmc-wishlist' ), '<a href="' . esc_url( $counter_link ) . '" target="_blank">' . __( 'wishlist counter page', 'wc-wlfmc-wishlist' ) . '</a>' ),
		)
	);
	if ( defined( 'MC_WLFMC_PREMIUM' ) ) {
		$section      = 'header_waitlist_counter';
		$counter_link = add_query_arg(
			array(
				'page' => 'mc-waitlist',
				'tab'  => 'counter',
			),
			admin_url( 'admin.php' )
		);
		Flatsome_Option::add_section(
			$section,
			array(
				'title'    => __( 'Waitlist Counter', 'wc-wlfmc-wishlist' ),
				'panel'    => 'header',
				'priority' => 110,
			)
		);

		Flatsome_Option::add_field(
			'option',
			array(
				'type'      => 'checkbox',
				'settings'  => 'mc_waitlist_title',
				'label'     => __( 'Show Waitlist Title', 'wc-wlfmc-wishlist' ),
				'section'   => $section,
				'transport' => $transport,
				'default'   => 1,
			)
		);

		Flatsome_Option::add_field(
			'option',
			array(
				'type'            => 'text',
				'settings'        => 'header_mc_waitlist_label',
				'label'           => __( 'Custom Title', 'wc-wlfmc-wishlist' ),
				'section'         => $section,
				'transport'       => $transport,
				'active_callback' => array(
					array(
						'setting'  => 'mc_waitlist_title',
						'operator' => '==',
						'value'    => '1',
					),
				),
				'default'         => '',
			)
		);

		Flatsome_Option::add_field(
			'option',
			array(
				'type'      => 'checkbox',
				'settings'  => 'mc_waitlist_dropdown_products',
				'label'     => __( 'Show Dropdown Products', 'wc-wlfmc-wishlist' ),
				'section'   => $section,
				'transport' => $transport,
				'default'   => 1,
			)
		);

		Flatsome_Option::add_field(
			'',
			array(
				'type'      => 'custom',
				'settings'  => 'header_waitlist_help',
				'label'     => '',
				'section'   => $section,
				'transport' => $transport,
				/* translators: %s: waitlist counter setting page */
				'default'   => sprintf( __( 'To change the settings of this section, please refer to the %s.', 'wc-wlfmc-wishlist' ), '<a href="' . esc_url( $counter_link ) . '" target="_blank">' . __( 'waitlist counter page', 'wc-wlfmc-wishlist' ) . '</a>' ),
			)
		);

		$section      = 'header_multi_list_counter';
		$counter_link = add_query_arg(
			array(
				'page' => 'mc-multi-list',
				'tab'  => 'counter',
			),
			admin_url( 'admin.php' )
		);
		Flatsome_Option::add_section(
			$section,
			array(
				'title'    => __( 'Multi-List Counter', 'wc-wlfmc-wishlist' ),
				'panel'    => 'header',
				'priority' => 110,
			)
		);

		Flatsome_Option::add_field(
			'option',
			array(
				'type'      => 'checkbox',
				'settings'  => 'mc_multi_list_title',
				'label'     => __( 'Show Multi-list Title', 'wc-wlfmc-wishlist' ),
				'section'   => $section,
				'transport' => $transport,
				'default'   => 1,
			)
		);

		Flatsome_Option::add_field(
			'option',
			array(
				'type'            => 'text',
				'settings'        => 'header_mc_multi_list_label',
				'label'           => __( 'Custom Title', 'wc-wlfmc-wishlist' ),
				'section'         => $section,
				'transport'       => $transport,
				'active_callback' => array(
					array(
						'setting'  => 'mc_multi_list_title',
						'operator' => '==',
						'value'    => '1',
					),
				),
				'default'         => '',
			)
		);

		Flatsome_Option::add_field(
			'option',
			array(
				'type'      => 'checkbox',
				'settings'  => 'mc_multi_list_dropdown_lists',
				'label'     => __( 'Show Dropdown Lists', 'wc-wlfmc-wishlist' ),
				'section'   => $section,
				'transport' => $transport,
				'default'   => 1,
			)
		);

		Flatsome_Option::add_field(
			'',
			array(
				'type'      => 'custom',
				'settings'  => 'header_multi_list_help',
				'label'     => '',
				'section'   => $section,
				'transport' => $transport,
				/* translators: %s: multi-list counter setting page */
				'default'   => sprintf( __( 'To change the settings of this section, please refer to the %s.', 'wc-wlfmc-wishlist' ), '<a href="' . esc_url( $counter_link ) . '" target="_blank">' . __( 'multi-list counter page', 'wc-wlfmc-wishlist' ) . '</a>' ),
			)
		);
	}
}
add_action( 'init', 'wlfmc_flatsome_integrate' );



/**
 * Integration with flatsome theme
 *
 * @return void
 */
function wlfmc_flatsome_integrate() {

	if ( function_exists( 'flatsome_setup' ) ) {
		remove_action( 'wp_enqueue_scripts', 'flatsome_wishlist_integrations_scripts' );
		remove_action( 'flatsome_account_links', 'flatsome_wishlist_account_item' );
		remove_action( 'flatsome_product_image_tools_top', 'flatsome_product_wishlist_button', 2 );
		remove_action( 'flatsome_product_box_tools_top', 'flatsome_product_wishlist_button', 2 );
		remove_action( 'flatsome_header_element', 'flatsome_header_wishlist' );
		remove_action( 'wp_ajax_flatsome_update_wishlist_count', 'flatsome_update_wishlist_count' );
		remove_action( 'wp_ajax_nopriv_flatsome_update_wishlist_count', 'flatsome_update_wishlist_count' );
		add_filter( 'wlfmc_button_positions', 'wlfmc_flatsome_fix_single_position' );
		add_filter( 'wlfmc_loop_positions', 'wlfmc_flatsome_fix_loop_position' );

		// fix default pagination class.
		add_filter( 'wlfmc_default_pagination_class', 'wlfmc_flatsome_default_pagination_class' );
		// fix quick_view positions.
		add_action( 'wc_quick_view_before_single_product', 'wlfmc_flatsome_fix_quick_view' );

		// fix inline loop buttons.
		$options       = new MCT_Options( 'wlfmc_options' );
		$loop_position = $options->get_option( 'loop_position', 'after_add_to_cart' );

		if ( in_array( $loop_position, array( 'before_add_to_cart', 'after_add_to_cart' ), true ) ) {

			add_action( 'flatsome_product_box_after', 'wlfmc_flatsome_inline_btn_loop_start', 98 );
			add_action( 'flatsome_product_box_after', 'wlfmc_flatsome_inline_btn_loop_end', 102 );

		} elseif ( in_array( $loop_position, array( 'image_top_left', 'image_top_right' ), true ) ) {
			add_filter( 'flatsome_product_labels', 'wlfmc_flatsome_fix_loop_on_image_position', 99999, 3 );
		}
		add_filter( 'wlfmc_custom_css_output', 'wlfmc_flatsome_fix_css' );
	}

}

/**
 * Fix default pagination class
 *
 * @return string
 */
function wlfmc_flatsome_default_pagination_class() {
	return ' wishlist-pagination';
}

/**
 * Start add wrapper for button position in loop
 *
 * @return void
 */
function wlfmc_flatsome_inline_btn_loop_start() {
	echo '<div class="wlfmc-inline-buttons"><div>';
}

/**
 * End add wrapper for button position in loop
 *
 * @return void
 */
function wlfmc_flatsome_inline_btn_loop_end() {
	echo '</div></div>';
}

/**
 * Fix quick view
 */
function wlfmc_flatsome_fix_quick_view() {
	add_filter( 'wlfmc_is_single', '__return_true' );
}

/**
 * Fix single position
 *
 * @param array $positions all single positions.
 *
 * @return array
 */
function wlfmc_flatsome_fix_single_position( array $positions ): array {

	$positions['image_top_left']     = array(
		array(
			'hook'     => 'flatsome_sale_flash',
			'priority' => '21',
		),
		array(
			'hook'     => 'woocommerce_before_single_product_lightbox_summary',
			'priority' => '50',
		),
	);
	$positions['image_top_right']    = array(
		array(
			'hook'     => 'flatsome_sale_flash',
			'priority' => '21',
		),
		array(
			'hook'     => 'woocommerce_before_single_product_lightbox_summary',
			'priority' => '50',
		),
	);
	$positions['image_bottom_left']  = array(
		array(
			'hook'     => 'flatsome_sale_flash',
			'priority' => '21',
		),
		array(
			'hook'     => 'woocommerce_before_single_product_lightbox_summary',
			'priority' => '50',
		),
	);
	$positions['image_bottom_right'] = array(
		array(
			'hook'     => 'flatsome_sale_flash',
			'priority' => '21',
		),
		array(
			'hook'     => 'woocommerce_before_single_product_lightbox_summary',
			'priority' => '50',
		),
	);
	$positions['summary']            = array(
		array(
			'hook'     => 'woocommerce_single_product_summary',
			'priority' => '100',
		),
		array(
			'hook'     => 'woocommerce_single_product_lightbox_summary',
			'priority' => '50',
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
function wlfmc_flatsome_fix_loop_position( array $positions ): array {

	$positions['before_add_to_cart']['hook']     = 'flatsome_product_box_after';
	$positions['before_add_to_cart']['priority'] = '99';
	$positions['after_add_to_cart']['hook']      = 'flatsome_product_box_after';
	$positions['after_add_to_cart']['priority']  = '101';
	$positions['image_top_left']['hook']         = 'wlfmc_shown_with_shortcode';
	$positions['image_top_right']['hook']        = 'wlfmc_shown_with_shortcode';
	$positions['image_top_left']['priority']     = '10';
	$positions['image_top_right']['priority']    = '10';
	return $positions;
}

/**
 * Fix loop position on ux products.
 *
 * @param string     $labels custom labels.
 * @param WP_Post    $post post.
 * @param WC_Product $product product.
 *
 * @return string
 */
function wlfmc_flatsome_fix_loop_on_image_position( $labels, $post, $product ) {
	if ( is_page() || ! wlfmc_is_single() ) {
		$labels .= '</div>' . do_shortcode( '[wlfmc_add_to_wishlist product_id="' . $product->get_id() . '" is_single=""]' ) . '<div>';
	}
	return $labels;
}


/**
 * Generate css plugin
 *
 * @param string $generated_css generated css codes.
 *
 * @return string
 */
function wlfmc_flatsome_fix_css( string $generated_css ) {
	$generated_css .= '#masthead .wlfmc-header-counter-item  .nav-dropdown {padding:0 !important} #masthead .wlfmc-header-counter-item  .nav-dropdown .wlfmc-counter-items {margin:0 !important} #masthead .wlfmc-header-counter-item .wlfmc-counter-items {background-color: transparent !important}';
	return $generated_css;
}
