<?php
/**
 * WLFMC wishlist integration with YITH WooCommerce Product Add-ons & Extra Options plugin ( free and premium)
 *
 * @plugin_name YITH WooCommerce Product Add-ons & Extra Options
 * @version 2.16.0 free 3.2.1 premium
 * @slug yith-woocommerce-product-add-ons
 * @url  https://wordpress.org/plugins/yith-woocommerce-product-add-ons/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'wlfmc_yith_woocommerce_product_addons_integrate' );
/**
 * Integration with YITH WooCommerce Product Add-ons & Extra Options plugin
 *
 * @return void
 */
function wlfmc_yith_woocommerce_product_addons_integrate() {

	if ( defined( 'YITH_WAPO' ) ) {
		add_action( 'wlfmc_before_add_to_cart_validation', 'wlfmc_yith_woocommerce_product_addons_disable_validation' );
	}

}

/**
 * Disable validation before add to wishlist.
 *
 * @return void
 */
function wlfmc_yith_woocommerce_product_addons_disable_validation() {
	$_REQUEST['yith_wapo_is_single'] = 1;
}

add_filter( 'wlfmc_add_to_cart_validation', 'wlfmc_yith_woocommerce_product_addons_validation', 10, 7 );

/**
 * Woocommerce add to cart validation
 *
 * @param bool                $passed validation status.
 * @param int                 $product_id product id.
 * @param int                 $quantity product quantity.
 * @param int                 $variation_id variation id.
 * @param array               $attributes product attributes.
 * @param array               $cart_item cart item data.
 * @param WLFMC_Wishlist_Item $item Wishlist item object.
 *
 * @return bool
 */
function wlfmc_yith_woocommerce_product_addons_validation( $passed, $product_id, $quantity, $variation_id, $attributes, $cart_item, $item ) {
	if ( defined( 'YITH_WAPO' ) && isset( $cart_item['yith_wapo_options'] ) ) {
		foreach ( $cart_item['yith_wapo_options'] as $index => $option ) {
			foreach ( $option as $key => $value ) {
				if ( $key ) {
					$_value  = stripslashes( $value );
					$explode = explode( '-', $key );
					if ( isset( $explode[1] ) ) {
						$addon_id  = $explode[0];
						$option_id = $explode[1];
					} else {
						$addon_id  = $key;
						$option_id = $_value;
					}
					$addon = new YITH_WAPO_Addon( $addon_id );
					if ( wlfmc_is_true( $addon->get_option( 'required', $option_id ) ) && '' === $value ) {
						wc_add_notice( __( 'Please fill required fields.', 'wc-wlfmc-wishlist' ), 'error' );
						return false;
					}
				}
			}
		}
		return true;
	}
	return $passed;
}
