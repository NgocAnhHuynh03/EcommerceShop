<?php
/**
 * WLFMC wishlist integration with Advanced Product Fields for WooCommerce plugin
 *
 * @plugin_name Advanced Product Fields for WooCommerce
 * @version 1.5.5
 * @slug advanced-product-fields-for-woocommerce
 * @url https://www.studiowombat.com/plugin/advanced-product-fields-for-woocommerce/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

use SW_WAPF\Includes\Classes\Field_Groups;
use SW_WAPF\Includes\Classes\Fields;
use SW_WAPF\Includes\Classes\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


add_action( 'init', 'wlfmc_wapf_integrate' );

/**
 * Integration with Advanced Product Fields for WooCommerce ( free and Premium ) plugin
 *
 * @return void
 */
function wlfmc_wapf_integrate() {

	if ( class_exists( 'SW_WAPF\WAPF' ) ) {
		add_action( 'wlfmc_before_wishlist_table', 'wlfmc_wapf_show_in_table' );
		add_action( 'wlfmc_after_wishlist_table', 'wlfmc_wapf_hide_in_table' );
		add_filter( 'wlfmc_woocommerce_add_to_cart_validation', 'wlfmc_wapf_add_to_cart_validation', 10, 4 );
		add_filter( 'wlfmc_wishlist_item_price', 'wlfmc_wapf_wishlist_item_price', 9, 4 );
	}
}

/**
 * Add hook before table to Show products in wishlist table.
 *
 * @return void
 */
function wlfmc_wapf_show_in_table() {
	add_filter( 'woocommerce_get_item_data', 'wlfmc_wapf_get_item_data', 10, 2 );
}

/**
 * Remove added hook after wishlist table.
 *
 * @return void
 */
function wlfmc_wapf_hide_in_table() {
	remove_filter( 'woocommerce_get_item_data', 'wlfmc_wapf_get_item_data', 10 );
}

/**
 * Filtering cart item data
 *
 * @param array $item_data item data.
 * @param array $cart_item cart item data.
 *
 * @return array
 */
function wlfmc_wapf_get_item_data( $item_data, $cart_item ) {
	if ( empty( $cart_item['wapf'] ) || ! is_array( $cart_item['wapf'] ) ) {
		return $item_data;
	}
	if ( ! is_array( $item_data ) ) {
		$item_data = array();
	}

	foreach ( $cart_item['wapf'] as $field ) {
		if ( empty( $field['value_cart'] ) ) {
			continue;
		}

		$item_data[] = array(
			'key'   => $field['label'],
			'value' => $field['value_cart'],
		);

	}

	return $item_data;
}


/**
 * Woocommerce add to cart validation
 *
 * @param bool                $passed validation status.
 * @param WC_Product          $product product.
 * @param array               $meta product meta.
 * @param WLFMC_Wishlist_Item $item wishlist item object.
 *
 * @return bool
 */
function wlfmc_wapf_add_to_cart_validation( $passed, $product, $meta, $item ) {

	$posted_data = $item->get_posted_data( 'view' );
	$posted_data = is_array( $posted_data ) && ! empty( $posted_data['post'] ) ? $posted_data['post'] : array();
	if ( ! isset( $posted_data['wapf_field_groups'] ) ) {
		return $passed;
	}

	if ( $product && 'variation' === $product->get_type() ) {
		$product_id = $product->get_parent_id();
		$product    = wc_get_product( $product_id );
	}
	$field_groups = Field_Groups::get_field_groups_of_product( $product );
	if ( empty( $field_groups ) ) {
		return $passed;
	}
	$field_group_ids = explode( ',', sanitize_text_field( $posted_data['wapf_field_groups'] ) );
	foreach ( $field_groups as $fg ) {
		if ( ! in_array( (string) $fg->id, $field_group_ids, true ) ) {
			wc_add_notice( esc_html( __( 'Error adding product to cart.', 'sw-wapf' ) ), 'error' );
			return false;
		}
	}
	$_REQUEST['wapf'] = $posted_data['wapf'];
	foreach ( $field_groups as $group ) {
		foreach ( $group->fields as $field ) {
			if ( ! Fields::should_field_be_filled_out( $group, $field ) ) {
				continue;
			}

			$value = Fields::get_raw_field_value_from_request( $field, 0, true );
			if ( empty( $value ) ) {
				/* translators: %s: field name . */
				wc_add_notice( sprintf( __( 'The field "%s" is required.', 'advanced-product-fields-for-woocommerce' ), esc_html( $field->label ) ), 'error' );

				return false;
			}
		}
	}
	$_REQUEST['wapf'] = array();
	return $passed;
}


/**
 *  Modify wishlist item price
 *
 * @param string              $price item price.
 * @param null|array          $product_meta product meta.
 * @param WC_Product          $product  $product Woocommerce Product.
 * @param WLFMC_Wishlist_Item $item Wishlist item object.
 *
 * @throws Exception Exception.
 * @return float|int|mixed
 */
function wlfmc_wapf_wishlist_item_price( $price, $product_meta, $product, $item ) {
	if ( ! empty( $product_meta['wapf'] ) ) {
		$base          = Helper::get_product_base_price( $product );
		$options_total = 0;

		foreach ( $product_meta['wapf'] as $field ) {
			if ( ! empty( $field['price'] ) ) {
				foreach ( $field['price'] as $price ) {
					if ( 0 === $price['value'] ) {
						continue;
					}
					$options_total = $options_total + Fields::do_pricing( $price['value'], 1 );
				}
			}
		}
		if ( $options_total > 0 ) {
			$price = $base + $options_total;
		}
	}

	return $price;

}
