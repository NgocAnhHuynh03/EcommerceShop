<?php
/**
 * WLFMC wishlist integration with WooCommerce TM Extra Product Options plugin
 *
 * @plugin_name WooCommerce TM Extra Product Options
 * @version 6.1.2
 * @slug woocommerce-tm-extra-product-options
 * @url  https://epo.themecomplete.com/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_woocommerce_tm_extra_product_options_integrate' );

/**
 * Integration with woocommerce product bundles plugin
 *
 * @return void
 */
function wlfmc_woocommerce_tm_extra_product_options_integrate() {

	if ( defined( 'THEMECOMPLETE_EPO_VERSION' ) || defined( 'TM_EPO_VERSION' ) ) {
		add_filter( 'wlfmc_wishlist_item_price', 'wlfmc_woocommerce_tm_extra_product_options_wishlist_item_price', 9, 4 );
	}
}


/**
 *  Modify wishlist item price for WooCommerce TM Extra Product Options
 *
 * @param string              $price item price.
 * @param null|array          $product_meta product meta.
 * @param WC_Product          $product  $product Woocommerce Product.
 * @param WLFMC_Wishlist_Item $item Wishlist item object.
 *
 * @throws Exception Exception.
 * @return float|int|mixed
 */
function wlfmc_woocommerce_tm_extra_product_options_wishlist_item_price( $price, $product_meta, $product, $item ) {

	if ( is_object( $product ) && isset( $product_meta['tmdata'] ) && ( defined( 'THEMECOMPLETE_EPO_VERSION' ) || defined( 'TM_EPO_VERSION' ) ) ) {
		$api     = defined( 'THEMECOMPLETE_EPO_VERSION' ) ? THEMECOMPLETE_EPO_API() : TM_EPO_API();
		$core    = defined( 'THEMECOMPLETE_EPO_VERSION' ) ? THEMECOMPLETE_EPO() : TM_EPO();
		$version = defined( 'THEMECOMPLETE_EPO_VERSION' ) ? THEMECOMPLETE_EPO_VERSION : TM_EPO_VERSION;
		if ( 'no' === $core->tm_epo_hide_options_in_cart ) {
			$product_id = $product_meta['tmdata']['product_id'];
			$has_epo    = $api->has_options( $product_id );
			if ( $api->is_valid_options( $has_epo ) ) {
				$product_meta['quantity'] = 1;
				$product_meta['data']     = $product;

				$product_price = apply_filters( 'wc_epo_add_cart_item_original_price', $product_meta['data']->get_price(), $product_meta );
				if ( ! empty( $product_meta['tmcartepo'] ) ) {
					$to_currency = version_compare( $version, '4.9.0', '<' ) ? tc_get_woocommerce_currency() : themecomplete_get_woocommerce_currency();
					foreach ( $product_meta['tmcartepo'] as $value ) {
						if ( isset( $value['price_per_currency'] ) && array_key_exists( $to_currency, $value['price_per_currency'] ) ) {
							$value          = floatval( $value['price_per_currency'][ $to_currency ] );
							$product_price += $value;
						} else {
							$product_price += floatval( $value['price'] );
						}
					}
				}
				$price = apply_filters( 'wc_tm_epo_ac_product_price', $product_price, '', $product_meta, $product, $product_id );
			}
		}
	}

	return $price;
}
