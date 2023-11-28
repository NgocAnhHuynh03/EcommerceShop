<?php
/**
 * WLFMC wishlist integration with YITH WooCommerce Product Bundles Premium plugin
 *
 * @plugin_name YITH WooCommerce Product Bundles Premium
 * @version 1.4.6
 * @slug yith-woocommerce-product-bundles
 * @url  https://yithemes.com/themes/plugins/yith-woocommerce-product-bundles
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'wlfmc_yith_woocommerce_product_bundles_integrate' );
/**
 * Integration with YITH WooCommerce Product Bundles plugin
 *
 * @return void
 */
function wlfmc_yith_woocommerce_product_bundles_integrate() {
	if ( defined( 'YITH_WCPB_PREMIUM' ) ) {
		add_filter( 'wlfmc_wishlist_item_price', 'wlfmc_yith_woocommerce_product_bundles_wishlist_item_price', 9, 4 );
	}

}

/**
 *  Modify wishlist item price for woocommerce product bundles
 *
 * @param string              $price item price.
 * @param null|array          $product_meta product meta.
 * @param WC_Product          $product  $product Woocommerce Product.
 * @param WLFMC_Wishlist_Item $item Wishlist item object.
 *
 * @throws Exception Exception.
 * @return float|int|mixed
 */
function wlfmc_yith_woocommerce_product_bundles_wishlist_item_price( $price, $product_meta, $product, $item ) {
	if ( is_object( $product ) && $product->is_type( 'yith_bundle' ) ) {
		$posted_data = $item->get_posted_data( 'view' );
		$posted_data = is_array( $posted_data ) && ! empty( $posted_data ) ? $posted_data['post'] : array();
		if ( $product->per_items_pricing ) {
			$bundled_items  = $product->get_bundled_items();
			$array_quantity = array();
			$array_opt      = array();
			$array_var      = array();
			if ( $bundled_items ) {
				$loop = 0;
				foreach ( $bundled_items as $item ) {
					/**
					 * Yith Bundle item
					 *
					 * @var YITH_WC_Bundled_Item $item
					 */
					$id = $item->item_id;

					if ( $item->is_optional() && isset( $posted_data[ 'yith_bundle_optional_' . $id ] ) ) {
						$array_opt[ $loop ] = 1;
					}

					if ( isset( $posted_data[ 'yith_bundle_quantity_' . $id ] ) ) {
						$array_quantity[ $loop ] = $posted_data[ 'yith_bundle_quantity_' . $id ];
					} else {
						$array_quantity[ $loop ] = 0;
					}

					if ( isset( $posted_data[ 'yith_bundle_variation_id_' . $id ] ) ) {
						$array_var[ $loop ] = $posted_data[ 'yith_bundle_variation_id_' . $id ];
					} else {
						$array_var[ $loop ] = '';
					}
					$loop++;
				}
				$price = $product->get_per_item_price_tot_with_params( $array_quantity, $array_opt, $array_var, false );
			}
		}
	}
	return $price;
}
