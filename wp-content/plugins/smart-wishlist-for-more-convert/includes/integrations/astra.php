<?php
/**
 * WLFMC wishlist integration with astra theme
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_astra_integrate' );

/**
 * Integration with Astra theme
 *
 * @return void
 */
function wlfmc_astra_integrate() {
	add_filter( 'wlfmc_loop_positions', 'wlfmc_astra_fix_loop_position' );
}

add_action( 'astra_render_header_components', 'wlfmc_add_astra_header_component', 10, 2 );
add_filter( 'astra_customizer_configurations', 'wlfmc_register_configuration', 30, 2 );
add_filter( 'astra_header_mobile_items', 'wlfmc_astra_header_items' );
add_filter( 'astra_header_desktop_items', 'wlfmc_astra_header_items' );

/**
 * Fix loop position
 *
 * @param array $positions all loop positions.
 *
 * @return array
 */
function wlfmc_astra_fix_loop_position( array $positions ): array {

	$positions['image_top_left']  = array(
		'hook'     => 'woocommerce_before_shop_loop_item',
		'priority' => 7,
	);
	$positions['image_top_right'] = array(
		'hook'     => 'woocommerce_before_shop_loop_item',
		'priority' => 7,
	);

	return $positions;
}

/**
 * Add astra header items
 *
 * @param array $items header items.
 * @return array
 */
function wlfmc_astra_header_items( $items ) {
	$items['wishlist-counter'] = array(
		'name'    => __( 'Wishlist Counter', 'wc-wlfmc-wishlist' ),
		'icon'    => 'heart',
		'section' => 'section-header-wishlist-counter',
		'delete'  => false,
	);
	if ( defined( 'MC_WLFMC_PREMIUM' ) ) {
		$items['multi-list-counter'] = array(
			'name'    => __( 'Multi-list Counter', 'wc-wlfmc-wishlist' ),
			'icon'    => 'editor-ul',
			'section' => 'section-header-multi-list-counter',
			'delete'  => false,
		);
		$items['waitlist-counter']   = array(
			'name'    => __( 'Waitlist Counter', 'wc-wlfmc-wishlist' ),
			'icon'    => 'bell',
			'section' => 'section-header-waitlist-counter',
			'delete'  => false,
		);
	}
	return $items;
}

/**
 * Render header components
 *
 * @param string $astra_header_component_slug component slug.
 * @param string $astra_active_device active device.
 * @return void
 */
function wlfmc_add_astra_header_component( $astra_header_component_slug, $astra_active_device ) {
	if ( 'wishlist-counter' === $astra_header_component_slug ) {
		?>
		<div class="ast-builder-layout-element site-header-focus-item ast-header-wishlist-counter" data-section="section-header-wishlist-counter">
			<div class="ast-header-wishlist-counter-wrap">
				<?php echo do_shortcode( '[wlfmc_wishlist_counter]' ); ?>
			</div>

		</div>
		<?php
	}
	if ( defined( 'MC_WLFMC_PREMIUM' ) && 'waitlist-counter' === $astra_header_component_slug ) {
		?>
		<div class="ast-builder-layout-element site-header-focus-item ast-header-waitlist-counter" data-section="section-header-waitlist-counter">
			<div class="ast-header-waitlist-counter-wrap">
				<?php echo do_shortcode( '[wlfmc_waitlist_counter]' ); ?>
			</div>

		</div>
		<?php
	}
	if ( defined( 'MC_WLFMC_PREMIUM' ) && 'multi-list-counter' === $astra_header_component_slug ) {
		?>
		<div class="ast-builder-layout-element site-header-focus-item ast-header-multi-list-counter" data-section="section-header-multi-list-counter">
			<div class="ast-header-multi-list-counter-wrap">
				<?php echo do_shortcode( '[wlfmc_multi_list_counter]' ); ?>
			</div>

		</div>
		<?php
	}
}

/**
 * Register header components
 *
 * @param array                $configurations configurations.
 * @param WP_Customize_Manager $wp_customize wp customize.
 * @return array
 */
function wlfmc_register_configuration( $configurations, $wp_customize ) {

	$_section     = 'section-header-wishlist-counter';
	$counter_link = add_query_arg(
		array(
			'page' => 'mc-wishlist-settings',
			'tab'  => 'counter',
		),
		admin_url( 'admin.php' )
	);
	$_configs     = array(
		array(
			'name'     => $_section,
			'type'     => 'section',
			'priority' => 80,
			'title'    => __( 'Wishlist Counter', 'wc-wlfmc-wishlist' ),
			'panel'    => 'panel-header-builder-group',
		),
		array(
			'name'     => $_section . '-notice',
			'type'     => 'control',
			'control'  => 'ast-description',
			'section'  => $_section,
			'priority' => 34,
			'label'    => '',
			/* translators: %s: wishlist counter setting page */
			'help'     => sprintf( __( 'To change the settings of this section, please refer to the %s.', 'wc-wlfmc-wishlist' ), '<a href="' . esc_url( $counter_link ) . '" target="_blank">' . __( 'wishlist counter page', 'wc-wlfmc-wishlist' ) . '</a>' ),
		),
	);
	if ( class_exists( 'Astra_Builder_Base_Configuration' ) && method_exists( 'Astra_Builder_Base_Configuration', 'prepare_visibility_tab' ) ) {
		$_configs = array_merge( $_configs, Astra_Builder_Base_Configuration::prepare_visibility_tab( $_section ) );
	}
	$configurations = array_merge( $configurations, $_configs );
	if ( defined( 'MC_WLFMC_PREMIUM' ) ) {
		$_section     = 'section-header-waitlist-counter';
		$counter_link = add_query_arg(
			array(
				'page' => 'mc-waitlist',
				'tab'  => 'counter',
			),
			admin_url( 'admin.php' )
		);
		$_configs     = array(
			array(
				'name'     => $_section,
				'type'     => 'section',
				'priority' => 80,
				'title'    => __( 'Waitlist Counter', 'wc-wlfmc-wishlist' ),
				'panel'    => 'panel-header-builder-group',
			),
			array(
				'name'     => $_section . '-notice',
				'type'     => 'control',
				'control'  => 'ast-description',
				'section'  => $_section,
				'priority' => 34,
				'label'    => '',
				/* translators: %s: waitlist counter setting page */
				'help'     => sprintf( __( 'To change the settings of this section, please refer to the %s.', 'wc-wlfmc-wishlist' ), '<a href="' . esc_url( $counter_link ) . '" target="_blank">' . __( 'waitlist counter page', 'wc-wlfmc-wishlist' ) . '</a>' ),
			),
		);
		if ( class_exists( 'Astra_Builder_Base_Configuration' ) && method_exists( 'Astra_Builder_Base_Configuration', 'prepare_visibility_tab' ) ) {
			$_configs = array_merge( $_configs, Astra_Builder_Base_Configuration::prepare_visibility_tab( $_section ) );
		}
		$configurations = array_merge( $configurations, $_configs );
		$_section       = 'section-header-multi-list-counter';
		$counter_link   = add_query_arg(
			array(
				'page' => 'mc-multi-list',
				'tab'  => 'counter',
			),
			admin_url( 'admin.php' )
		);
		$_configs       = array(
			array(
				'name'     => $_section,
				'type'     => 'section',
				'priority' => 80,
				'title'    => __( 'Multi-List Counter', 'wc-wlfmc-wishlist' ),
				'panel'    => 'panel-header-builder-group',
			),
			array(
				'name'     => $_section . '-notice',
				'type'     => 'control',
				'control'  => 'ast-description',
				'section'  => $_section,
				'priority' => 34,
				'label'    => '',
				/* translators: %s: multi-list counter setting page */
				'help'     => sprintf( __( 'To change the settings of this section, please refer to the %s.', 'wc-wlfmc-wishlist' ), '<a href="' . esc_url( $counter_link ) . '" target="_blank">' . __( 'multi-list counter page', 'wc-wlfmc-wishlist' ) . '</a>' ),
			),
		);
		if ( class_exists( 'Astra_Builder_Base_Configuration' ) && method_exists( 'Astra_Builder_Base_Configuration', 'prepare_visibility_tab' ) ) {
			$_configs = array_merge( $_configs, Astra_Builder_Base_Configuration::prepare_visibility_tab( $_section ) );
		}
		$configurations = array_merge( $configurations, $_configs );
	}

	return $configurations;
}
