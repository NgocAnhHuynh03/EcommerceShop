<?php
/**
 * WLFMC wishlist integration with woocommerce custom product addons plugin
 *
 * @plugin_name WooCommerce Custom Product Addons (Free && Pro)
 * @version 2.6.9 free and  4.2.0 pro
 * @slug woo-custom-product-addons && woo-custom-product-addons-pro
 * @url https://wordpress.org/plugins/woo-custom-product-addons/ && https://acowebs.com/woo-custom-product-addons/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_woo_custom_product_addons_integrate' );

/**
 * Integration with woocommerce custom product addons plugin
 *
 * @return void
 */
function wlfmc_woo_custom_product_addons_integrate() {

	if ( function_exists( 'WCPA' ) ) {
		add_filter( 'wlfmc_add_cart_item_data', 'wlfmc_remove_empty_wcpa_meta', 10, 2 );
		add_filter( 'wlfmc_wishlist_item_price', 'wlfmc_wcpa_wishlist_item_price', 11, 4 );
	}
}

/**
 *  Modify wishlist item price for woocommerce custom product addons
 *
 * @param string              $price item price.
 * @param null|array          $product_meta product meta.
 * @param WC_Product          $product  $product Woocommerce Product.
 * @param WLFMC_Wishlist_Item $item Wishlist item object.
 *
 * @return float
 */
function wlfmc_wcpa_wishlist_item_price( $price, $product_meta, $product, $item ) {
	if ( defined( 'WCPA_ITEM_ID' ) && class_exists( 'WCPA_Form' ) && class_exists( 'WCPA_MC' ) ) {

		if ( ! empty( $product_meta['wcpa_data'] ) ) {
			$calculated_price = 0;
			foreach ( $product_meta['wcpa_data'] as $v ) {

				if ( wlfmc_is_true( $v['cur_swit'] ) && isset( $v['price'] ) && false !== $v['price'] ) {
					if ( is_array( $v['price'] ) ) {
						$v['price'] = array_sum( $v['price'] );
					}
					$calculated_price += $v['price'];
				}
			}
			$mc    = new WCPA_MC();
			$price = $mc->mayBeConvert( floatval( $calculated_price ) ) + (float) $price;
		}
	}

	return $price;
}

/**
 * Remove empty 'wcpa_data' key from product meta.
 *
 * @param null|array $product_meta product meta.
 * @param int        $product_id product id.
 *
 * @return array
 */
function wlfmc_remove_empty_wcpa_meta( $product_meta, $product_id ) {
	if ( isset( $product_meta['wcpa_data'] ) && empty( $product_meta['wcpa_data'] ) ) {
		if ( ! wcpa_is_wcpa_product( $product_id ) ) {
			unset( $product_meta['wcpa_data'] );
			if ( isset( $product_meta['wcpa_combined_products'] ) ) {
				unset( $product_meta['wcpa_combined_products'] );
			}
			if ( isset( $product_meta['wcpa_checkout_fields_data'] ) ) {
				unset( $product_meta['wcpa_checkout_fields_data'] );
			}
		}
	}
	return $product_meta;
}
