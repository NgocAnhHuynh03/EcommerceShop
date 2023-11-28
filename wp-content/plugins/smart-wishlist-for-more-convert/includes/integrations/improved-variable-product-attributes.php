<?php
/**
 * WLFMC wishlist integration with Improved Product Options for WooCommerce  plugin
 *
 * @plugin_name Improved Product Options for WooCommerce
 * @version 5.3.2
 * @slug improved-variable-product-attributes
 * @url https://xforwoocommerce.com
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'wlfmc_improved_variable_product_attributes_integrate' );

/**
 * Integration with Improved Product Options for WooCommerce plugin
 *
 * @return void
 */
function wlfmc_improved_variable_product_attributes_integrate() {

	if ( class_exists( 'XforWC_Improved_Options' ) ) {
		add_filter( 'wlfmc_remove_args_from_posted_data', 'wlfmc_fix_improved_variable_product_attribute_posted_data' );
		add_action( 'wp_enqueue_scripts', 'wlfmc_add_to_wishlist_improved_variable_product_attributes', 100, 1 );
	}
}

/**
 * Add inline script to fix Improved Product Options for WooCommerce
 *
 * @return void
 */
function wlfmc_add_to_wishlist_improved_variable_product_attributes() {

	wp_add_inline_script(
		'wlfmc-main',
		"
	jQuery( document ).ready(
		function($){
			var target = document.querySelectorAll( '.ivpa-content' );
			for (var i = 0; i < target.length; i++) {
				create( target[i] );
			}

			function create(t) {
				// create an observer instance.
				var observer = new MutationObserver(
					function(mutations) {
						mutations.forEach(
							function(mutation) {
								var v_id = t.getAttribute( 'data-selected' )
								var p_id = t.getAttribute( 'data-id' )

								var variation_data = {
									product_id : p_id,
									variation_id : v_id,
								};
								$( document ).trigger( 'wlfmc_show_variation', variation_data );
							}
						);
					}
				);
				// configuration of the observer.
				var config = {
					attributes: true,
					attributeFilter: ['data-selected']
				};

				// pass in the target node, as well as the observer options.
				observer.observe( t, config );
			}

			$( document ).on(
				'wlfmc_add_to_wishlist_data',
				function (e, el, data) {

					if (typeof ivpa === 'undefined' || ! ivpa) {
						return false;
					}

					var button    = el;
					var container = button.closest( ivpa.settings.archive_selector );
					var find      = button.closest('.summary').length > 0 ? '#ivpa-content' : '.ivpa-content';

					if ( container.find( find ).length > 0 ) {

						var var_id = container.find( find ).attr( 'data-selected' );

						if ( typeof var_id == 'undefined' || var_id == '' ) {
							var_id = container.find( '[name=\"variation_id\"]' ).val();
						}

						if ( typeof var_id == 'undefined' || var_id == '' ) {
							var_id = container.find( find ).attr( 'data-id' );
						}

						var item = {};

						container.find( find + ' .ivpa_attribute' ).each(
							function() {
								var attribute       = $( this ).attr( 'data-attribute' );
								var attribute_value = $( this ).find( '.ivpa_term.ivpa_clicked' ).attr( 'data-term' );
								if ( typeof attribute_value !== 'undefined' ) {
									data['attribute_' + attribute] = attribute_value;
								}

							}
						);
						var ivpac = container.find( find + ' .ivpa_custom_option' ).length > 0 ? container.find( find + ' .ivpa_custom_option [name^=\"ivpac_\"]' ).serialize() : '';

						var ivpac_fields = container.find( find + ' .ivpa_custom_option' ).length > 0 ? container.find( find + ' .ivpa_custom_option [name^=\"ivpac_\"]' ) : '';

						if (ivpac_fields) {

							ivpac_fields.each(
								function () {

									var name = $( this ).attr( 'name' ).replace( /\[.*\]/g, '' );

									if ($( this ).is( ':checkbox' )) {

										if ( ! $( this ).is( ':checked' )) {
											return true;
										}

										if (data.hasOwnProperty( name ) && data[name].length) {
											data[name] = (data[name] + ', ' + $( this ).val()).replace( /^, /, '' );
										} else {
											data[name] = $( this ).val();
										}
									} else {
										data[name] = $( this ).val();
									}
								}
							);
						}
						if( var_id ) {
							data.add_to_wishlist = var_id;
						}
						data.variation_id    = var_id;
						data.ivpac           = ivpac;
					}
				}
			);

		}
	);
    "
	);
}


/**
 * Remove unused posted key
 *
 * @param array $args arguments.
 *
 * @return array
 */
function wlfmc_fix_improved_variable_product_attribute_posted_data( $args ) {
	if ( isset( $_POST['ivpac'] ) && '' === $_POST['ivpac'] ) { // phpcs:ignore WordPress.Security.NonceVerification
		$args[] = 'ivpac';
	}
	return $args;
}
