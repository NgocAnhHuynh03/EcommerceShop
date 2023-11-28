<?php
/**
 * WLFMC wishlist integration with Advanced Product Fields Pro for WooCommerce plugin
 *
 * @plugin_name Advanced Product Fields Pro for WooCommerce
 * @version  2.1.1
 * @slug advanced-product-fields-for-woocommerce-pro
 * @url https://www.studiowombat.com/plugin/advanced-product-fields-for-woocommerce/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

use SW_WAPF_PRO\Includes\Classes\Cache;
use SW_WAPF_PRO\Includes\Classes\Cart;
use SW_WAPF_PRO\Includes\Classes\Enumerable;
use SW_WAPF_PRO\Includes\Classes\Field_Groups;
use SW_WAPF_PRO\Includes\Classes\Fields;
use SW_WAPF_PRO\Includes\Classes\File_Upload;
use SW_WAPF_PRO\Includes\Classes\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_wapf_pro_integrate' );

/**
 * Integration with Advanced Product Fields Pro for WooCommerce ( free and Premium ) plugin
 *
 * @return void
 */
function wlfmc_wapf_pro_integrate() {

	if ( class_exists( 'SW_WAPF_PRO\WAPF' ) ) {
		add_action( 'wlfmc_before_wishlist_table', 'wlfmc_wapf_pro_show_in_table' );
		add_action( 'wlfmc_after_wishlist_table', 'wlfmc_wapf_pro_hide_in_table' );
		add_action( 'wlfmc_adding_to_wishlist', 'wlfmc_fix_wapf_pro_adding_to_wishlist' );
		add_action( 'wlfmc_added_to_wishlist', 'wlfmc_fix_wapf_pro_added_to_wishlist' );
		add_action( 'wlfmc_before_add_to_cart_validation', 'wlfmc_fix_wapf_pro_post_data' );
		add_filter( 'wlfmc_add_to_cart_validation', 'wlfmc_wapf_pro_add_to_cart_validation', 10, 7 );
		add_filter( 'wlfmc_wishlist_item_price', 'wlfmc_wapf_pro_wishlist_item_price', 9, 4 );
	}
}

/**
 * Skip validation before add to cart from wishlist
 *
 * @return void
 */
function wlfmc_fix_wapf_pro_post_data() {
	add_filter( 'wapf/skip_cart_validation', '__return_true' );
}

/**
 * Skip field validation before add to wishlist
 *
 * @return void
 */
function wlfmc_fix_wapf_pro_adding_to_wishlist() {
	add_filter( 'wapf/skip_fieldgroup_validation', 'wlfmc_wapf_pro_skip_validation' );
}

/**
 * Remove skipped hook.
 *
 * @return void
 */
function wlfmc_fix_wapf_pro_added_to_wishlist() {
	remove_filter( 'wapf/skip_fieldgroup_validation', 'wlfmc_wapf_pro_skip_validation' );
}

/**
 * Skip function.
 *
 * @return false
 */
function wlfmc_wapf_pro_skip_validation() {
	return false;
}
/**
 * Add hook before table to Show products in wishlist table.
 *
 * @return void
 */
function wlfmc_wapf_pro_show_in_table() {
	add_filter( 'woocommerce_get_item_data', 'wlfmc_wapf_pro_get_item_data', 10, 2 );
}

/**
 * Remove added hook after wishlist table.
 *
 * @return void
 */
function wlfmc_wapf_pro_hide_in_table() {
	remove_filter( 'woocommerce_get_item_data', 'wlfmc_wapf_pro_get_item_data', 10 );
}

/**
 * Filtering cart item data
 *
 * @param array $item_data item data.
 * @param array $cart_item cart item data.
 *
 * @return array
 */
function wlfmc_wapf_pro_get_item_data( $item_data, $cart_item ) {
	if ( empty( $cart_item['wapf'] ) || ! is_array( $cart_item['wapf'] ) ) {
		return $item_data;
	}
	if ( ! is_array( $item_data ) ) {
		$item_data = array();
	}

	foreach ( $cart_item['wapf'] as $field ) {
		if ( ! isset( $field['values'] ) ) {
			continue;
		}

		if ( Enumerable::from( $field['values'] )->any(
			function( $x ) use ( $cart_item ) {
				return isset( $x['label'] ) && strlen( $x['label'] ) > 0;
			}
		) ) {

			$data = array(
				'key'     => $field['label'],
				'value'   => Helper::values_to_string( $field, true ),
				'display' => Helper::values_to_string( $field, false, isset( $cart_item['wapf_item_price'] ) ? $cart_item['wapf_item_price']['options'] : array() ),
			);

			$item_data[] = $data;
		}
	}

	return $item_data;
}

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
function wlfmc_wapf_pro_add_to_cart_validation( $passed, $product_id, $quantity, $variation_id, $attributes, $cart_item, $item ) {
	$skip_validation = apply_filters( 'wlfmc_wapf_pro_skip_cart_validation', false );
	if ( $skip_validation ) {
		return true;
	}

	if ( empty( $product_id ) ) {
		return true;
	}
	$field_groups = Field_Groups::get_field_groups_of_product( $product_id );

	if ( empty( $field_groups ) ) {
		return $passed;
	}
	$posted_data = $item->get_posted_data( 'view' );
	$files_data  = is_array( $posted_data ) && ! empty( $posted_data ) ? $posted_data['files'] : array();
	$posted_data = is_array( $posted_data ) && ! empty( $posted_data ) ? $posted_data['post'] : array();

	$field_group_ids            = isset( $posted_data['wapf_field_groups'] ) ? sanitize_text_field( $posted_data['wapf_field_groups'] ) : false;
	$skip_fieldgroup_validation = apply_filters( 'wapf/skip_fieldgroup_validation', false );//phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

	if ( ! $skip_fieldgroup_validation && $field_group_ids ) {
		if ( ! isset( $posted_data['wapf_field_groups'] ) ) {
			wc_add_notice( esc_html( __( 'Error adding product to cart.', 'sw-wapf' ) ), 'error' );
			return false;
		}

		$field_group_ids = explode( ',', $field_group_ids );
		foreach ( $field_groups as $fg ) {
			if ( ! in_array( $fg->id, $field_group_ids ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				wc_add_notice( esc_html( __( 'Error adding product to cart.', 'sw-wapf' ) ), 'error' );
				return false;
			}
		}
	}
	$files = wlfmc_create_uploaded_file_array( $files_data );
	Cache::set_files( $files );
	$_REQUEST   = $posted_data;
	$validation = Cart::validate_cart_data( $field_groups, true, $product_id, $item->get_quantity(), $variation_id, false, $item->get_cart_item() );
	if ( is_string( $validation ) ) {
		wc_add_notice( esc_html( $validation ), 'error' );
		return false;
	}

	return true;
}

/**
 * Create uploaded file array
 *
 * @param array $files files.
 *
 * @return array
 */
function wlfmc_create_uploaded_file_array( $files ) {

	if ( ! isset( $files['wapf'] ) ) {
		return array();
	}

	$result = array();

	foreach ( $files['wapf']['name'] as $key => $content ) {
		if ( empty( $content[0] ) ) {
			continue;
		}

		$result[ $key ] = array();
		$count          = count( $content );
		for ( $i = 0; $i < $count; $i++ ) {
			$result[ $key ][] = array(
				'name'     => $content[ $i ],
				'tmp_name' => $files['wapf']['tmp_name'][ $key ][ $i ],
				'size'     => $files['wapf']['size'][ $key ][ $i ],
				'error'    => isset( $files['wapf']['error'][ $key ][ $i ] ) ?? 0,
				'type'     => $files['wapf']['type'][ $key ][ $i ],
			);
		}
	}

	return $result;

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
function wlfmc_wapf_pro_wishlist_item_price( $price, $product_meta, $product, $item ) {
	if ( ! empty( $product_meta['wapf'] ) ) {
		$base          = Cart::get_cart_item_base_price( $product, $item->get_quantity(), $item->get_cart_item() );
		$options_total = 0;
		foreach ( $product_meta['wapf'] as $field ) {
			if ( ! empty( $field['values'] ) ) {
				foreach ( $field['values'] as $value ) {
					if ( 0 === $value['price'] || 'none' === $value['price_type'] ) {
						continue;
					}

					$v         = isset( $value['slug'] ) ? $value['label'] : $field['raw'];
					$qty_based = ( isset( $field['clone_type'] ) && 'qty' === $field['clone_type'] ) || ! empty( $field['qty_based'] );

					$_price        = Fields::do_pricing( $qty_based, $value['price_type'], $value['price'], $base, $item->get_quantity(), $v, $product->get_id(), $product_meta['wapf'], $product_meta['wapf_field_groups'], $product_meta['wapf_clone'] ?? 0 );
					$options_total = $options_total + $_price;
				}
			}
		}
		if ( $options_total > 0 ) {
			$price = $base + $options_total;
		}
	}
	return $price;
}
