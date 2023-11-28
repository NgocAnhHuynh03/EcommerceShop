<?php
/**
 * WLFMC wishlist integration with woocommerce composite products plugin
 *
 * @plugin_name WooCommerce Composite Products
 * @version 8.5.2
 * @slug woocommerce-composite-products
 * @url https://woocommerce.com/products/composite-products/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_woocommerce_composite_products_integrate' );

/** Integration with woocommerce composite products plugin
 *
 * @return void
 */
function wlfmc_woocommerce_composite_products_integrate() {

	if ( class_exists( 'WC_Composite_Products' ) ) {
		add_filter( 'wlfmc_wishlist_item_price', 'wlfmc_wccp_wishlist_item_price', 8, 4 );
	}
}

/**
 *  Modify wishlist item price for woocommerce composite products
 *
 * @param string              $price item price.
 * @param null|array          $product_meta product meta.
 * @param WC_Product          $product  $product Woocommerce Product.
 * @param WLFMC_Wishlist_Item $item Wishlist item object.
 *
 * @throws Exception Exception.
 * @return float|int
 */
function wlfmc_wccp_wishlist_item_price( $price, $product_meta, $product, $item ) {
	if ( is_object( $product ) && 'composite' === $product->get_type() && isset( $product_meta['composite_data'] ) ) {
		$meta       = WC_CP()->cart->rebuild_posted_composite_form_data( $product_meta['composite_data'] );
		$components = $product->get_components();
		/**
		 * Variable
		 *
		 * @var WC_CP_Component $component
		 */
		foreach ( $components as $component_id => $component ) {
			$composited_product_id       = ! empty( $meta['wccp_component_selection'][ $component_id ] ) ? absint( $meta['wccp_component_selection'][ $component_id ] ) : '';
			$composited_product_quantity = isset( $meta['wccp_component_quantity'][ $component_id ] ) ? absint( $meta['wccp_component_quantity'][ $component_id ] ) : $component->get_quantity( 'min' );
			$composited_variation_id     = isset( $meta['wccp_variation_id'][ $component_id ] ) ? wc_clean( $meta['wccp_variation_id'][ $component_id ] ) : '';
			if ( $composited_product_id ) {
				$composited_product_wrapper = $component->get_option( $composited_variation_id ? $composited_variation_id : $composited_product_id );
				if ( ! $composited_product_wrapper ) {
					continue;
				}

				if ( $component->is_priced_individually() ) {

					$composited_product = $composited_product_wrapper->get_product();
					try {
						$variations = array();
						$p_meta     = $product_meta['composite_data'][ $component_id ];
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
									'product_id'   => $composited_product_id,
									'variation_id' => $composited_variation_id,
									'variation'    => $variations,
									'quantity'     => $composited_product_quantity,
									'data'         => $composited_product,
									'data_hash'    => '',
								)
							),
							''
						);
						$_price                    = $woocommerce_add_cart_item['data']->get_price();
					} catch ( Exception $e ) {
						$_price = $composited_product_wrapper->get_price();
					}
					$price += apply_filters( 'wlfmc_wishlist_item_price', $_price, $product_meta['composite_data'][ $component_id ], $composited_product, $item ) * $composited_product_quantity;
				}
			}
		}
	}
	return $price;
}


