<?php
/**
 * WLFMC wishlist integration with woocommerce GravityForms product addons plugin
 *
 * @plugin_name WooCommerce Gravity Forms Product Add-Ons
 * @version 3.3.26
 * @slug woocommerce-gravityforms-product-addons
 * @url http://woothemes.com/products/gravity-forms-add-ons/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

add_action( 'init', 'wlfmc_woocommerce_gravityforms_product_addons_integrate' );

/**
 * Integration with woocommerce GravityForms product addons plugin
 *
 * @return void
 */
function wlfmc_woocommerce_gravityforms_product_addons_integrate() {

	if ( function_exists( 'wc_gravityforms_product_addons_plugins_loaded' ) ) {
		add_action( 'wlfmc_adding_to_wishlist', 'wlfmc_fix_wc_gf_add_to_wishlist' );
		add_filter( 'wlfmc_remove_args_from_posted_data', 'wlfmc_fix_wc_gf_posted_data' );
		add_filter( 'wlfmc_files_posted_data', 'wlfmc_wc_gf_add_multiple_files_posted_data' );
		add_action( 'wlfmc_before_add_to_cart_validation', 'wlfmc_wc_gf_before_add_to_cart_validation' );
		add_action( 'wlfmc_after_add_to_cart_validation', 'wlfmc_wc_gf_after_add_to_cart_validation' );
	}
}

/**
 * Fix wc gravity form addons
 *
 * @return void
 */
function wlfmc_fix_wc_gf_add_to_wishlist() {
	$_POST['gform_save'] = true; // fix wc gravity form addons.
}

/**
 * Remove unused posted key
 *
 * @param array $args arguments.
 *
 * @return array
 */
function wlfmc_fix_wc_gf_posted_data( $args ) {
	$args[] = 'gform_save';
	return $args;
}

/**
 * Add gravity multiple files to posted data files.
 *
 * @param array $files Posted files.
 *
 * @return array
 */
function wlfmc_wc_gf_add_multiple_files_posted_data( $files ) {

	// Fix gravityforms multiple uploaded files.
	if ( isset( $_POST['gform_uploaded_files'] ) && ! empty( $_POST['gform_uploaded_files'] ) ) {// phpcs:ignore WordPress.Security.NonceVerification
		$gf_files = json_decode( wp_unslash( $_POST['gform_uploaded_files'] ), true );// phpcs:ignore WordPress.Security
		if ( ! empty( $gf_files ) ) {
			foreach ( $gf_files as $k => $upload_field ) {
				if ( is_array( $upload_field ) ) {
					if ( isset( $upload_field[0] ) && is_array( $upload_field[0] ) ) {
						foreach ( $upload_field as $upload ) {
							$filename = '';
							if ( isset( $upload['temp_filename'] ) ) {
								$filename = sanitize_file_name( wp_basename( $upload['temp_filename'] ) );
							}
							if ( isset( $upload['uploaded_filename'] ) ) {
								$filename = sanitize_file_name( wp_basename( $upload['uploaded_filename'] ) );
							}
							$files[ $k ][] = array(
								'name'     => $filename,
								'size'     => 1,
								'error'    => 0,
								'tmp_name' => $filename,
								'type'     => '',
							);
						}
					}
				} else {
					$files[ $k ][] = array(
						'name'     => wp_basename( $upload_field ),
						'size'     => 1,
						'error'    => 0,
						'tmp_name' => wp_basename( $upload_field ),
						'type'     => '',
					);
				}
			}
		}
	}

	return $files;
}

/**
 * Add filter for fix gravity addons form failed validation.
 *
 * @return void
 */
function wlfmc_wc_gf_before_add_to_cart_validation() {
	add_filter( 'gform_pre_process', 'wlfmc_fix_gform_pre_validation', 50 );
}

/**
 * Remove filter
 *
 * @return void
 */
function wlfmc_wc_gf_after_add_to_cart_validation() {
	remove_filter( 'gform_pre_process', 'wlfmc_fix_gform_pre_validation', 50 );
}

/**
 * Fix gravity addons form failed validation.
 *
 * @param array $form  gravity form.
 *
 * @return array
 */
function wlfmc_fix_gform_pre_validation( $form ) {

	if ( $form && ! empty( $form['fields'] ) ) {
		foreach ( $form['fields'] as $field ) {
			$field->failed_validation = false;
		}
	}

	return $form;
}
