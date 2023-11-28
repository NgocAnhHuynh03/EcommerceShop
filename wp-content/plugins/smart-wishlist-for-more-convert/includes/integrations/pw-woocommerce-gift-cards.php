<?php
/**
 * WLFMC wishlist integration with PW WooCommerce Gift Cards  plugin
 *
 * @plugin_name PW WooCommerce Gift Cards
 * @version 1.220
 * @slug pw-woocommerce-gift-cards
 * @url https://wordpress.org/plugins/pw-woocommerce-gift-cards/
 *
 * AND Pro version:
 *
 * @plugin_name PW WooCommerce Gift Cards Pro
 * @version 1.407
 * @slug pw-gift-cards
 * @url https://www.pimwick.com
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action( 'init', 'wlfmc_pw_woocommerce_gift_cards_integrate' );

/**
 * Integration with PW WooCommerce Gift Cards plugin
 *
 * @return void
 */
function wlfmc_pw_woocommerce_gift_cards_integrate() {

	if ( defined( 'PWGC_VERSION' ) ) {
		add_filter( 'wlfmc_product_with_meta_add_to_cart_enable_ajax', 'wlfmc_pwgc_add_to_cart_ajax_enable', 10, 2 );
		add_filter( 'wlfmc_product_with_meta_add_to_cart_text', 'wlfmc_pwgc_add_to_cart_text', 10, 5 );
		add_filter( 'wlfmc_product_with_meta_add_to_cart_url', 'wlfmc_pwgc_add_to_cart_url', 10, 3 );
		add_filter( 'wlfmc_woocommerce_add_to_cart_validation', 'wlfmc_pwgc_add_to_cart_validation', 10, 3 );
	}
}

/**
 * Enable ajax add to cart
 *
 * @param bool       $can_add_with_ajax ajax add to card status.
 * @param WC_Product $product Product.
 *
 * @return bool
 */
function wlfmc_pwgc_add_to_cart_ajax_enable( $can_add_with_ajax, $product ) {
	if ( PWGC_PRODUCT_TYPE_SLUG === $product->get_type() ) {
		return false;
	}
	return $can_add_with_ajax;
}

/**
 * Add to cart Text
 *
 * @param string              $add_to_cart_text Add to cart button text.
 * @param WC_Product          $product product.
 * @param bool                $passed_validation validation status.
 * @param WLFMC_Wishlist_Item $item Item wishlist.
 * @param array               $meta product meta.
 *
 * @return string
 */
function wlfmc_pwgc_add_to_cart_text( $add_to_cart_text, $product, $passed_validation, $item, $meta ) {

	if ( PWGC_PRODUCT_TYPE_SLUG === $product->get_type() ) {
		return $product->add_to_cart_text();
	}

	if ( ! $passed_validation && ( ( isset( $meta[ PWGC_TO_META_KEY ] ) && empty( $meta[ PWGC_TO_META_KEY ] ) ) || ( isset( $meta[ PWGC_FROM_META_KEY ] ) && empty( $meta[ PWGC_FROM_META_KEY ] ) ) ) ) {
		return __( 'Define options', 'wc-wlfmc-wishlist' );
	}
	if ( isset( $meta[ PWGC_DENOMINATION_ATTRIBUTE_SLUG ] ) && empty( $meta[ PWGC_DENOMINATION_ATTRIBUTE_SLUG ] ) ) {
		return __( 'Select amount', 'wc-wlfmc-wishlist' );
	}

	if ( ! $passed_validation && defined( PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ) && isset( $meta[ PWGC_GIFT_CARD_CUSTOM_AMOUNT_META_KEY ] ) ) {
		return __( 'Select amount', 'wc-wlfmc-wishlist' );
	}
	return $add_to_cart_text;
}

/**
 * Add to cart Url
 *
 * @param string     $add_to_cart_url add to cart url.
 * @param string     $permalink product permalink.
 * @param WC_Product $product product.
 *
 * @return string
 */
function wlfmc_pwgc_add_to_cart_url( $add_to_cart_url, $permalink, $product ) {

	if ( PWGC_PRODUCT_TYPE_SLUG === $product->get_type() ) {
		return $permalink;
	}
	return $add_to_cart_url;
}

/**
 * Woocommerce add to cart validation
 *
 * @param bool       $passed validation status.
 * @param WC_Product $product product.
 * @param array      $meta product meta.
 *
 * @return bool
 */
function wlfmc_pwgc_add_to_cart_validation( $passed, $product, $meta ) {
	try {
		if ( is_a( $product, 'WC_Product_PW_Gift_Card' ) ) {
			return false;
		}
		if ( isset( $meta[ PWGC_TO_META_KEY ] ) && empty( $meta[ PWGC_TO_META_KEY ] ) ) {
			wc_add_notice( __( '"to recipient" is a required field.', 'wc-wlfmc-wishlist' ), 'error' );
			return false;
		}
		if ( isset( $meta[ PWGC_FROM_META_KEY ] ) && empty( $meta[ PWGC_FROM_META_KEY ] ) ) {
			wc_add_notice( __( '"from" is a required field.', 'wc-wlfmc-wishlist' ), 'error' );
			return false;
		}
		if ( isset( $meta[ PWGC_DENOMINATION_ATTRIBUTE_SLUG ] ) && empty( $meta[ PWGC_DENOMINATION_ATTRIBUTE_SLUG ] ) ) {
			wc_add_notice( __( '"amount" is a required field.', 'wc-wlfmc-wishlist' ), 'error' );
			return false;
		}
	} catch ( Exception $e ) {
		return $passed;
	}
	return $passed;
}
