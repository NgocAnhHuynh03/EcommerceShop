<?php
/**
 * WLFMC wishlist integration with WooCommerce Mix and Match plugin
 *
 * @plugin_name WooCommerce Mix and Match
 * @version 2.2.2
 * @slug woocommerce-mix-and-match-products
 * @url https://woocommerce.com/products/woocommerce-mix-and-match-products/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_woocommerce_mix_and_match_products_integrate' );

/** Integration with WooCommerce Mix and Match plugin
 *
 * @return void
 */
function wlfmc_woocommerce_mix_and_match_products_integrate() {

	if ( class_exists( 'WC_Mix_and_Match' ) ) {
		add_filter( 'wlfmc_wishlist_item_price', 'wlfmc_woocommerce_mix_and_match_products_wishlist_item_price', 8, 4 );
	}
}

/**
 *  Modify wishlist item price for WooCommerce Mix and Match
 *
 * @param string              $price item price.
 * @param null|array          $product_meta product meta.
 * @param WC_Product          $product  $product Woocommerce Product.
 * @param WLFMC_Wishlist_Item $item Wishlist item object.
 *
 * @throws Exception Exception.
 * @return float|int
 */
function wlfmc_woocommerce_mix_and_match_products_wishlist_item_price( $price, $product_meta, $product, $item ) {
	if ( is_object( $product ) && $product->is_type( 'mix-and-match' ) && $product->is_priced_per_product( 'edit' ) ) {
		$mnm_items = $product->get_child_items();
		if ( ! empty( $mnm_items ) ) {
			$_price = 0;
			foreach ( $mnm_items as $mnm_item ) {
				$p_id          = $mnm_item->get_variation_id() ? $mnm_item->get_variation_id() : $mnm_item->get_product_id();
				$item_quantity = 0;
				if ( array_key_exists( $p_id, $product_meta['mnm_config'] ) ) {
					$item_quantity = absint( $product_meta['mnm_config'][ $p_id ]['quantity'] );
				}
				if ( 0 >= $item_quantity ) {
					continue;
				}
				$_price += wc_get_price_to_display( $mnm_item, array( 'qty' => $item_quantity ) );
			}

			if ( 0 < $_price ) {
				$price = $_price + wc_get_price_to_display( $product );
			}
		}
	}
	return $price;
}


