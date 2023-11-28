<?php
/**
 * WLFMC wishlist integration with woocommerce product bundles plugin
 *
 * @plugin_name WooCommerce Product Bundles
 * @version 6.17.1
 * @slug woocommerce-product-bundles
 * @url https://woocommerce.com/products/product-bundles/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_woocommerce_product_bundles_integrate' );

/**
 * Integration with woocommerce product bundles plugin
 *
 * @return void
 */
function wlfmc_woocommerce_product_bundles_integrate() {

	if ( class_exists( 'WC_Bundles' ) ) {
		add_filter( 'wlfmc_wishlist_item_price', 'wlfmc_woocommerce_product_bundles_wishlist_item_price', 9, 4 );
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
function wlfmc_woocommerce_product_bundles_wishlist_item_price( $price, $product_meta, $product, $item ) {
	if ( is_object( $product ) && $product->is_type( 'bundle' ) && isset( $product_meta['stamp'] ) ) {
		$bundle_price  = $product->get_price();
		$bundled_items = $product->get_bundled_items();
		$meta          = $product_meta['stamp'];
		if ( ! empty( $bundled_items ) ) {
			$bundled_items_price = 0.0;

			foreach ( $bundled_items as $bundled_item_id => $bundled_item ) {

				$bundled_variation_id = absint( $meta[ $bundled_item_id ]['variation_id'] ?? 0 );
				if ( ! empty( $bundled_variation_id ) ) {
					$_bundled_product = wc_get_product( $bundled_variation_id );
				} else {
					$_bundled_product = $bundled_item->product;
				}

				$is_optional = $bundled_item->is_optional();

				$bundled_product_qty = isset( $meta[ $bundled_item_id ]['quantity'] ) ? absint( $meta[ $bundled_item_id ]['quantity'] ) : $bundled_item->get_quantity();

				if ( $is_optional ) {
					if ( isset( $meta[ $bundled_item_id ]['optional_selected'] ) && 'no' === $meta[ $bundled_item_id ]['optional_selected'] ) {
						$bundled_product_qty = 0;
					}
				}

				if ( $bundled_item->is_priced_individually() ) {
					try {
						$variations = array();
						$p_meta     = $product_meta['stamp'][ $bundled_item_id ];
						if ( empty( $p_meta ) ) {
							throw new Exception();
						}
						if ( isset( $p_meta['attributes'] ) ) {
							$variations['attributes'] = $p_meta['attributes'];
						}
						$woocommerce_add_cart_item = apply_filters(
							'woocommerce_add_cart_item',
							array_merge(
								$p_meta,
								array(
									'key'          => '',
									'product_id'   => $bundled_variation_id ? $bundled_item->product->get_id() : $_bundled_product->get_id(),
									'variation_id' => $bundled_variation_id,
									'variation'    => $variations,
									'quantity'     => $bundled_product_qty,
									'data'         => $_bundled_product,
									'data_hash'    => '',
								)
							),
							''
						);
						$product_price             = $woocommerce_add_cart_item['data']->get_price();
					} catch ( Exception $e ) {
						$product_price = $_bundled_product->get_price();
					}
					$discount             = $bundled_item->get_discount();
					$product_price        = empty( $discount ) ? $product_price : WC_PB_Product_Prices::get_discounted_price( $product_price, $discount );
					$bundled_item_price   = (float) $product_price * (int) $bundled_product_qty;
					$bundled_items_price += $bundled_item_price;
				}

				$bundled_items_price = apply_filters( 'wlfmc_wishlist_item_price', $bundled_items_price, $product_meta['stamp'][ $bundled_item_id ], $_bundled_product, $item );
			}
			$price = (float) $bundle_price + $bundled_items_price;
		}
	}

	return $price;
}
