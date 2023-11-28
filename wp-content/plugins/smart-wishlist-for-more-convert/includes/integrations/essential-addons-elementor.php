<?php
/**
 * WLFMC wishlist integration with Essential Addons for Elementor plugin ( Free & Pro )
 *
 * @plugin_name Essential Addons for Elementor
 * @version 5.7.1 free & 5.4.8 pro
 * @slug essential-addons-elementor
 * @url  https://wordpress.org/plugins/essential-addons-for-elementor-lite/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.5.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'wlfmc_essential_addons_elementor_integrate' );

/**
 * Integration with Essential Addons for Elementor plugin
 *
 * @return void
 */
function wlfmc_essential_addons_elementor_integrate() {

	if ( defined( 'EAEL_PLUGIN_VERSION' ) ) {
		add_filter( 'wlfmc_custom_css_output', 'wlfmc_essential_addons_elementor_fix_css' );
		add_filter( 'wlfmc_loop_positions', 'wlfmc_essential_addons_elementor_fix_loop_position' );
		add_filter( 'elementor/widget/render_content', 'wlfmc_essential_addons_after_render', 0, 2 );
		add_action(
			'woocommerce_before_shop_loop_item',
			function () {
				remove_filter( 'woocommerce_loop_add_to_cart_link', 'wlfmc_loop_add_to_cart_link', 10 );
			}
		);
		add_action( 'eael_woo_before_product_loop', 'wlfmc_add_essential_addons_elementor_loop_position' );
		if ( wp_doing_ajax() && isset( $_REQUEST['class'] ) && in_array( sanitize_text_field( wp_unslash( $_REQUEST['class'] ) ), array( 'Essential_Addons_Elementor\Elements\Product_Grid', 'Essential_Addons_Elementor\Elements\Woo_Product_Gallery' ), true ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			add_filter( 'woocommerce_loop_add_to_cart_link', 'wlfmc_loop_add_to_cart_link', 10, 3 );
		}
	}
}

/**
 * Remove hook after render element
 *
 * @param string                $content The content of the widget.
 * @param Elementor\Widget_Base $widget  The widget.
 *
 * @return string
 */
function wlfmc_essential_addons_after_render( $content, $widget ) {
	remove_filter( 'woocommerce_loop_add_to_cart_link', 'wlfmc_loop_add_to_cart_link', 10 );
	return $content;
}

/**
 * Add loop position by add to cart link.
 *
 * @return void
 */
function wlfmc_add_essential_addons_elementor_loop_position() {
	add_filter( 'woocommerce_loop_add_to_cart_link', 'wlfmc_loop_add_to_cart_link', 10, 3 );
}

/**
 * Add action hook to add to cart html
 *
 * @param string                $add_to_cart_html add to cart html.
 * @param WC_Product|null|false $product Product.
 * @param array                 $args arguments.
 *
 * @return string
 */
function wlfmc_loop_add_to_cart_link( $add_to_cart_html, $product, $args = array() ) {
	ob_start();
	do_action( 'wlfmc_before_add_to_cart_link', $product );
	$before = ob_get_clean();
	ob_start();
	do_action( 'wlfmc_after_add_to_cart_link', $product );
	$after = ob_get_clean();

	return $before . $add_to_cart_html . $after;
}

/**
 * Fix loop position
 *
 * @param array $positions all loop positions.
 *
 * @return array
 */
function wlfmc_essential_addons_elementor_fix_loop_position( array $positions ): array {

	if ( isset( $positions['before_add_to_cart']['hook'] ) ) {
		$positions['before_add_to_cart'] = array(
			array(
				'hook'     => $positions['before_add_to_cart']['hook'],
				'priority' => $positions['before_add_to_cart']['priority'],
			),
			array(
				'hook'     => 'wlfmc_before_add_to_cart_link',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['before_add_to_cart'] ) ) {
		$positions['before_add_to_cart'][] = array(
			'hook'     => 'wlfmc_before_add_to_cart_link',
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
				'hook'     => 'wlfmc_after_add_to_cart_link',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['after_add_to_cart'] ) ) {
		$positions['after_add_to_cart'][] = array(
			'hook'     => 'wlfmc_after_add_to_cart_link',
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
				'hook'     => 'wlfmc_before_add_to_cart_link',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['image_top_left'] ) ) {
		$positions['image_top_left'][] = array(
			'hook'     => 'wlfmc_before_add_to_cart_link',
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
				'hook'     => 'wlfmc_before_add_to_cart_link',
				'priority' => 10,
			),

		);
	} elseif ( ! empty( $positions['image_top_right'] ) ) {
		$positions['image_top_right'][] = array(
			'hook'     => 'wlfmc_before_add_to_cart_link',
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
function wlfmc_essential_addons_elementor_fix_css( string $generated_css ) {
	$generated_css .= '.eael-product-grid.grid .eael-product-wrap .icons-wrap.over-box-style li a {margin: 0 3px !important}';
	$generated_css .= '.eael-product-grid.grid .eael-product-wrap .icons-wrap li.add-to-cart  {display: inline-flex;}';
	$generated_css .= '.eael-product-grid.grid .eael-product-wrap .icons-wrap li.add-to-cart .wlfmc-top-of-image {position:relative;left:0;top:0;right:0;z-index: 0;}';
	$generated_css .= '.eael-product-gallery .eael-product-wrap .icons-wrap li.add-to-cart .wlfmc-top-of-image ,.eael-woo-product-slider-container.preset-1 .eael-product-slider .icons-wrap.box-style li .wlfmc-top-of-image ,.eael-woo-product-slider-container .eael-product-slider .eael-add-to-cart-button .wlfmc-top-of-image,.eael-woo-product-slider-container .icons-wrap.box-style-list li.add-to-cart .wlfmc-top-of-image,.eael-woo-product-carousel-container .icons-wrap.box-style li.add-to-cart .wlfmc-top-of-image{ position:relative;left:0;top:0;right:0;z-index: 0;}';
	$generated_css .= '.eael-product-gallery .eael-product-wrap .icons-wrap li.add-to-cart .wlfmc-add-button i,.eael-woo-product-slider-container .icons-wrap.box-style-list li.add-to-cart .wlfmc-add-button i,.eael-woo-product-slider-container.preset-1 .eael-product-slider .icons-wrap.box-style li.add-to-cart .wlfmc-add-button i,.eael-woo-product-carousel-container .icons-wrap.box-style li.add-to-cart .wlfmc-add-button i{color:inherit}';
	$generated_css .= '.eael-product-gallery .eael-product-wrap .icons-wrap li.add-to-cart .wlfmc-add-button:hover i,.eael-woo-product-slider-container .icons-wrap.box-style-list li.add-to-cart .wlfmc-add-button:hover i,.eael-woo-product-slider-container.preset-1 .eael-product-slider .icons-wrap.box-style li.add-to-cart .wlfmc-add-button:hover i,.eael-woo-product-carousel-container .icons-wrap.box-style li.add-to-cart .wlfmc-add-button:hover i {color:inherit}';
	$generated_css .= '.eael-product-gallery .eael-product-wrap .icons-wrap li.add-to-cart,.eael-woo-product-slider-container.preset-1 .eael-product-slider .icons-wrap.box-style li.add-to-cart,.eael-woo-product-carousel-container .icons-wrap.box-style li.add-to-cart {display: inline-flex;}';
	$generated_css .= '.eael-woo-product-slider-container .icons-wrap.box-style-list li.add-to-cart {display: flex;flex-direction: column;}';
	return $generated_css;
}
