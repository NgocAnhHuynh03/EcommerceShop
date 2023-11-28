<?php
/**
 * WLFMC wishlist integration with ELEX WooCommerce Catalog Mode plugin
 *
 * @plugin_name ELEX WooCommerce Catalog Mode
 * @version 1.2.8
 * @slug elex-woocommerce-catalog-mode
 * @url https://wordpress.org/plugins/elex-woocommerce-catalog-mode/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_filter( 'wlfmc_hide_on_button_disabled_in_single', 'wlfmc_elex_woocommerce_catalog_mode_integrate', 10, 2 );

/**
 * Integration with ELEX WooCommerce Catalog Mode plugin
 *
 * @param bool       $show Show state.
 * @param WC_Product $product Product.
 *
 * @return bool
 */
function wlfmc_elex_woocommerce_catalog_mode_integrate( $show, $product ) {

	if ( class_exists( 'Elex_CM_Price_Discount_Admin' ) ) {
		if ( 'yes' === get_option( 'eh_pricing_discount_cart_catalog_mode' ) && 'yes' === get_option( 'elex_catalog_remove_addtocart_product' ) ) {
			if ( ! ( 'yes' === get_option( 'eh_pricing_discount_price_catalog_mode_exclude_admin' ) && in_array( 'administrator', wp_get_current_user()->roles, true ) ) ) {
				$options         = new MCT_Options( 'wlfmc_options' );
				$single_position = $options->get_option( 'wishlist_button_position', 'after_add_to_cart' );
				if ( 'variable' === $product->get_type() && in_array( $single_position, array( 'before_add_to_cart_button', 'after_add_to_cart_button' ), true ) ) {
					return false;
				} elseif ( 'variable' !== $product->get_type() && in_array( $single_position, array( 'before_add_to_cart', 'after_add_to_cart', 'before_add_to_cart_button', 'after_add_to_cart_button' ), true ) ) {
					return false;
				}
			}
		} elseif ( ( 'yes' === get_post_meta( $product->get_id(), 'product_adjustment_hide_addtocart_catalog', true ) ) && ( ( 'yes' === get_post_meta( $product->get_id(), 'product_adjustment_hide_addtocart_catalog_product', true ) ) || ( '' === get_post_meta( $product->get_id(), 'product_adjustment_hide_addtocart_catalog_product', true ) ) ) ) {
			if ( ! ( 'yes' === get_post_meta( $product->get_id(), 'product_adjustment_exclude_admin_catalog', true ) && in_array( 'administrator', wp_get_current_user()->roles, true ) ) ) {
				$options         = new MCT_Options( 'wlfmc_options' );
				$single_position = $options->get_option( 'wishlist_button_position', 'after_add_to_cart' );
				if ( 'variable' === $product->get_type() && in_array( $single_position, array( 'before_add_to_cart_button', 'after_add_to_cart_button' ), true ) ) {
					return false;
				} elseif ( 'variable' !== $product->get_type() && in_array( $single_position, array( 'before_add_to_cart', 'after_add_to_cart', 'before_add_to_cart_button', 'after_add_to_cart_button' ), true ) ) {
					return false;
				}
			}
		}
	}
	return $show;
}

add_filter( 'wlfmc_product_with_meta_button_add_to_cart', 'wlfmc_elex_woocommerce_catalog_mode_disable_wishlist_button', 10, 3 );

/**
 * Disable Custom Button add to cart in wishlist table
 *
 * @param bool                $state Showing state.
 * @param WLFMC_Wishlist_Item $item WLFMC Wishlist Item.
 * @param WC_Product          $product Product.
 *
 * @return bool
 */
function wlfmc_elex_woocommerce_catalog_mode_disable_wishlist_button( $state, $item, $product ): bool {
	if ( class_exists( 'Elex_CM_Price_Discount_Admin' ) ) {
		if ( 'yes' === get_option( 'eh_pricing_discount_cart_catalog_mode' ) && 'yes' === get_option( 'elex_catalog_remove_addtocart_product' ) ) {
			if ( ! ( 'yes' === get_option( 'eh_pricing_discount_price_catalog_mode_exclude_admin' ) && in_array( 'administrator', wp_get_current_user()->roles, true ) ) ) {
				return false;
			}
		} elseif ( ( 'yes' === get_post_meta( $product->get_id(), 'product_adjustment_hide_addtocart_catalog', true ) ) && ( ( 'yes' === get_post_meta( $product->get_id(), 'product_adjustment_hide_addtocart_catalog_product', true ) ) || ( '' === get_post_meta( $product->get_id(), 'product_adjustment_hide_addtocart_catalog_product', true ) ) ) ) {
			if ( ! ( 'yes' === get_post_meta( $product->get_id(), 'product_adjustment_exclude_admin_catalog', true ) && in_array( 'administrator', wp_get_current_user()->roles, true ) ) ) {
				return false;
			}
		}
	}
	return $state;
}
