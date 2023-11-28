<?php
/**
 * WLFMC wishlist integration with  Hide Price and Add to Cart Button  plugin
 *
 * @plugin_name  Hide Price and Add to Cart Button
 * @version 1.2.6
 * @slug hide-price-add-to-cart-button
 * @url https://woocommerce.com/products/hide-price-add-to-cart-button/
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'wlfmc_hide_price_add_to_cart_button_integrate' );


/**
 * Integration with Hide Price and Add to Cart Button plugin
 *
 * @return void
 */
function wlfmc_hide_price_add_to_cart_button_integrate() {

	if ( class_exists( 'Addify_Woo_Hide_Price' ) ) {
		new WLFMC_Addify_Woo_Hide_Price();
	}
}

if ( ! class_exists( 'WLFMC_Addify_Woo_Hide_Price' ) ) {
	/**
	 * WLFMC_Addify_Woo_Hide_Price Class
	 */
	class WLFMC_Addify_Woo_Hide_Price {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_filter( 'wlfmc_hide_on_button_disabled_in_single', array( $this, 'show_button_in_single' ), 10, 2 );
			add_filter( 'wlfmc_product_with_meta_button_add_to_cart', array( $this, 'show_meta_button_in_table' ), 10, 3 );
			add_action( 'wlfmc_table_product_before_add_to_cart', array( $this, 'alter_loop_add_to_cart_link' ) );
			add_action( 'wlfmc_table_product_after_add_to_cart', array( $this, 'restore_loop_add_to_cart_link' ) );

		}
		/**
		 * Filter Add to Cart button url on wishlist page
		 */
		public function alter_loop_add_to_cart_link() {
			add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'show_default_button_in_table' ), 99, 2 );
		}
		/**
		 * Restore default Add to Cart button, after wishlist handling
		 *
		 * @return void
		 */
		public function restore_loop_add_to_cart_link() {
			remove_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'show_default_button_in_table' ), 99 );
		}
		/**
		 * Show wishlist button in single product page
		 *
		 * @param bool       $show Showing state.
		 * @param WC_Product $product Product.
		 *
		 * @return bool
		 */
		public function show_button_in_single( $show, $product ): bool {
			if ( $this->button_is_hidden( $product ) ) {
				return false;
			}
			return $show;
		}

		/**
		 * Show Custom Button add to cart in wishlist table
		 *
		 * @param bool                $state Showing state.
		 * @param WLFMC_Wishlist_Item $item WLFMC Wishlist Item.
		 * @param WC_Product          $product Product.
		 *
		 * @return bool
		 */
		public function show_meta_button_in_table( $state, $item, $product ): bool {
			if ( $this->button_is_hidden( $product ) ) {
				return false;
			}
			return $state;
		}

		/**
		 * Show default button in wishlist table
		 *
		 * @param string     $html button html.
		 * @param  WC_Product $product Product.
		 *
		 * @return string
		 */
		public function show_default_button_in_table( $html, $product ) {
			$cart_txt = $html;

			$args = array(
				'post_type'   => 'addify_whp',
				'post_status' => 'publish',
				'numberposts' => -1,
				'orderby'     => 'menu_order',
				'order'       => 'ASC',

			);
			$rules = get_posts( $args );
			foreach ( $rules as $rule ) {

				$afwhp_rule_type          = get_post_meta( $rule->ID, 'afwhp_rule_type', true );
				$afwhp_hide_products      = unserialize( get_post_meta( $rule->ID, 'afwhp_hide_products', true ) );// @codingStandardsIgnoreLine.
				$afwhp_hide_categories    = unserialize( get_post_meta( $rule->ID, 'afwhp_hide_categories', true ) );// @codingStandardsIgnoreLine.
				$afwhp_hide_user_role     = unserialize( get_post_meta( $rule->ID, 'afwhp_hide_user_role', true ) );// @codingStandardsIgnoreLine.
				$afwhp_is_hide_addtocart  = get_post_meta( $rule->ID, 'afwhp_is_hide_addtocart', true );
				$afwhp_custom_button_text = get_post_meta( $rule->ID, 'afwhp_custom_button_text', true );
				$afwhp_custom_button_link = get_post_meta( $rule->ID, 'afwhp_custom_button_link', true );
				$afwhp_contact7_form      = get_post_meta( $rule->ID, 'afwhp_contact7_form', true );
				$afwhp_hide_for_countries = unserialize( get_post_meta( $rule->ID, 'afwhp_hide_for_countries', true ) );// @codingStandardsIgnoreLine.

				if ( ! empty( $afwhp_hide_for_countries ) ) {
					// country.
					$curr_country = $this->geolocate();

				} else {

					$curr_country = '';

				}

				$istrue = false;

				if ( ! empty( $afwhp_hide_for_countries ) && in_array( $curr_country, $afwhp_hide_for_countries, true ) ) {

					$iscountry = true;

				} elseif ( empty( $afwhp_hide_for_countries ) ) {

					$iscountry = true;

				} else {

					$iscountry = false;
				}

				$applied_on_all_products = get_post_meta( $rule->ID, 'afwhp_apply_on_all_products', true );

				if ( 'variable' !== $product->get_type() ) {
					if ( 'afwhp_for_registered_users' === $afwhp_rule_type ) {
						// Registered Users.

						if ( is_user_logged_in() ) {

							// get Current User Role.
							$curr_user      = wp_get_current_user();
							$curr_user_role = $curr_user->roles[0];

							if ( 'yes' === $applied_on_all_products && empty( $afwhp_hide_user_role ) ) {
								$istrue = true;
							} elseif ( ( is_array( $afwhp_hide_user_role ) && in_array( $curr_user_role, $afwhp_hide_user_role, true ) ) && 'yes' === $applied_on_all_products ) {
								$istrue = true;
							} elseif ( ( is_array( $afwhp_hide_user_role ) && in_array( $curr_user_role, $afwhp_hide_user_role, true ) ) && ( is_array( $afwhp_hide_products ) && in_array( $product->get_id(), $afwhp_hide_products, true ) ) ) {
								$istrue = true;
							}

							// Products.
							if ( $istrue && $iscountry ) {

								if ( $this->check_required_addons( $product->get_id() ) ) {
									// WooCommerce Product Addons compatibility.

									return $html;

								} else {

									if ( 'yes' === $afwhp_is_hide_addtocart ) {

										if ( '' === $afwhp_custom_button_text ) {

											$cart_txt = '';
										} else {

											if ( ! empty( $afwhp_custom_button_link ) ) {

												$cart_txt = '<a href="' . esc_url( $afwhp_custom_button_link ) . '" rel="nofollow" class="button add_to_cart_button product_type_' . $product->get_type() . '">' . esc_attr( $afwhp_custom_button_text ) . '</a>';
											} elseif ( ! empty( $afwhp_contact7_form ) ) {

												$contact7_cart_txt = '<a href="#form_popup' . $afwhp_contact7_form . $product->get_id() . '" class="form_popup' . $afwhp_contact7_form . $product->get_id() . '_open button product_type_simple add_to_cart_button">' . esc_attr( $afwhp_custom_button_text ) . '</a>';

												$contact7 = get_post( $afwhp_contact7_form );

												$form_title = $contact7->post_title;

												$popup  = '<div id="form_popup' . $afwhp_contact7_form . $product->get_id() . '" class="form_popup">';
												$popup .= '<button class="form_popup' . $afwhp_contact7_form . $product->get_id() . '_close form_close_btn btn btn-default">X</button>';

												$popup .= do_shortcode( '[contact-form-7 id="' . $afwhp_contact7_form . '" title="' . $form_title . '" ] ' );

												$popup .= '</div>';

												$popup .= '<script type="text/javascript">';
												$popup .= '	jQuery(document).ready(function() {';
												$popup .= '		jQuery("#form_popup' . $afwhp_contact7_form . $product->get_id() . '").popup();';
												$popup .= '	});';
												$popup .= '</script>';

												$cart_txt = $contact7_cart_txt . $popup;

											} else {

												$cart_txt = '<a href="javascript:void(0)" rel="nofollow" class="button add_to_cart_button product_type_' . $product->get_type() . '">' . esc_attr( $afwhp_custom_button_text ) . '</a>';
											}
										}
									}
								}
							}

							// Categories.
							if ( ! empty( $afwhp_hide_categories ) && ! $istrue && $iscountry ) {
								if ( 'variation' === $product->get_type() ) {
									$product = wc_get_product( $product->get_parent_id() );
								}
								foreach ( $afwhp_hide_categories as $cat ) {

									if ( has_term( $cat, 'product_cat', $product->get_id() ) ) {

										if ( in_array( $curr_user_role, $afwhp_hide_user_role, true ) ) {

											if ( $this->check_required_addons( $product->get_id() ) ) {
												// WooCommerce Product Addons compatibility.

												return $html;

											} else {

												if ( 'yes' === $afwhp_is_hide_addtocart ) {
													if ( '' === $afwhp_custom_button_text ) {
														return '';
													} else {
														if ( ! empty( $afwhp_custom_button_link ) ) {

															return '<a href="' . esc_url( $afwhp_custom_button_link ) . '" rel="nofollow" class="button add_to_cart_button product_type_' . $product->get_type() . '">' . esc_attr( $afwhp_custom_button_text ) . '</a>';
														} elseif ( ! empty( $afwhp_contact7_form ) ) {

															$contact7_cart_txt = '<a href="#form_popup' . $afwhp_contact7_form . $product->get_id() . '" class="form_popup' . $afwhp_contact7_form . $product->get_id() . '_open button product_type_simple add_to_cart_button">' . esc_attr( $afwhp_custom_button_text ) . '</a>';

															$contact7 = get_post( $afwhp_contact7_form );

															$form_title = $contact7->post_title;

															$popup  = '<div id="form_popup' . $afwhp_contact7_form . $product->get_id() . '" class="form_popup">';
															$popup .= '<button class="form_popup' . $afwhp_contact7_form . $product->get_id() . '_close form_close_btn btn btn-default">X</button>';

															$popup .= do_shortcode( '[contact-form-7 id="' . $afwhp_contact7_form . '" title="' . $form_title . '" ] ' );

															$popup .= '</div>';

															$popup .= '<script type="text/javascript">';
															$popup .= '	jQuery(document).ready(function() {';
															$popup .= '		jQuery("#form_popup' . $afwhp_contact7_form . $product->get_id() . '").popup();';
															$popup .= '	});';
															$popup .= '</script>';

															return $contact7_cart_txt . $popup;
														} else {

															return '<a href="javascript:void(0)" rel="nofollow" class="button add_to_cart_button product_type_' . $product->get_type() . '">' . esc_attr( $afwhp_custom_button_text ) . '</a>';

														}
													}
												}
											}
										}
									}
								}
							}
						}
					} else {
						// Guest Users.

						if ( ! is_user_logged_in() ) {

							// Products.
							if ( 'yes' === $applied_on_all_products ) {
								$istrue = true;
							} elseif ( is_array( $afwhp_hide_products ) && in_array( $product->get_id(), $afwhp_hide_products, true ) ) {
								$istrue = true;
							}

							if ( $istrue && $iscountry ) {

								if ( $this->check_required_addons( $product->get_id() ) ) {
									// WooCommerce Product Addons compatibility.

									return $html;

								} else {

									if ( 'yes' === $afwhp_is_hide_addtocart ) {

										if ( '' === $afwhp_custom_button_text ) {

											$cart_txt = '';
										} else {

											if ( ! empty( $afwhp_custom_button_link ) ) {

												$cart_txt = '<a href="' . esc_url( $afwhp_custom_button_link ) . '" rel="nofollow" class="button add_to_cart_button product_type_' . $product->get_type() . '">' . esc_attr( $afwhp_custom_button_text ) . '</a>';
											} elseif ( ! empty( $afwhp_contact7_form ) ) {

												$contact7_cart_txt = '<a href="#form_popup' . $afwhp_contact7_form . $product->get_id() . '" class="form_popup' . $afwhp_contact7_form . $product->get_id() . '_open button product_type_simple add_to_cart_button">' . esc_attr( $afwhp_custom_button_text ) . '</a>';

												$contact7 = get_post( $afwhp_contact7_form );

												$form_title = $contact7->post_title;

												$popup  = '<div id="form_popup' . $afwhp_contact7_form . $product->get_id() . '" class="form_popup">';
												$popup .= '<button class="form_popup' . $afwhp_contact7_form . $product->get_id() . '_close form_close_btn btn btn-default">X</button>';

												$popup .= do_shortcode( '[contact-form-7 id="' . $afwhp_contact7_form . '" title="' . $form_title . '" ] ' );

												$popup .= '</div>';

												$popup .= '<script type="text/javascript">';
												$popup .= '	jQuery(document).ready(function() {';
												$popup .= '		jQuery("#form_popup' . $afwhp_contact7_form . $product->get_id() . '").popup();';
												$popup .= '	});';
												$popup .= '</script>';

												$cart_txt = $contact7_cart_txt . $popup;

											} else {

												$cart_txt = '<a href="javascript:void(0)" rel="nofollow" class="button add_to_cart_button product_type_' . $product->get_type() . '">' . esc_attr( $afwhp_custom_button_text ) . '</a>';
											}
										}
									}
								}
							}

							// Categories.
							if ( ! empty( $afwhp_hide_categories ) && ! $istrue && $iscountry ) {
								if ( 'variation' === $product->get_type() ) {
									$product = wc_get_product( $product->get_parent_id() );
								}
								foreach ( $afwhp_hide_categories as $cat ) {

									if ( has_term( $cat, 'product_cat', $product->get_id() ) ) {

										if ( $this->check_required_addons( $product->get_id() ) ) {
											// WooCommerce Product Addons compatibility.

											return $html;

										} else {

											if ( 'yes' === $afwhp_is_hide_addtocart ) {
												if ( '' === $afwhp_custom_button_text ) {
													$cart_txt = '';
												} else {
													if ( ! empty( $afwhp_custom_button_link ) ) {

														$cart_txt = '<a href="' . esc_url( $afwhp_custom_button_link ) . '" rel="nofollow" class="button add_to_cart_button product_type_' . $product->get_type() . '">' . esc_attr( $afwhp_custom_button_text ) . '</a>';
													} elseif ( ! empty( $afwhp_contact7_form ) ) {
														$contact7_cart_txt = '<a href="#form_popup' . $afwhp_contact7_form . $product->get_id() . '" class="form_popup' . $afwhp_contact7_form . $product->get_id() . '_open button product_type_simple add_to_cart_button">' . esc_attr( $afwhp_custom_button_text ) . '</a>';

														$contact7 = get_post( $afwhp_contact7_form );

														$form_title = $contact7->post_title;

														$popup  = '<div id="form_popup' . $afwhp_contact7_form . $product->get_id() . '" class="form_popup">';
														$popup .= '<button class="form_popup' . $afwhp_contact7_form . $product->get_id() . '_close form_close_btn btn btn-default">X</button>';

														$popup .= do_shortcode( '[contact-form-7 id="' . $afwhp_contact7_form . '" title="' . $form_title . '" ] ' );

														$popup .= '</div>';

														$popup .= '<script type="text/javascript">';
														$popup .= '	jQuery(document).ready(function() {';
														$popup .= '		jQuery("#form_popup' . $afwhp_contact7_form . $product->get_id() . '").popup();';
														$popup .= '	});';
														$popup .= '</script>';

														$cart_txt = $contact7_cart_txt . $popup;
													} else {
														$cart_txt = '<a href="javascript:void(0)" rel="nofollow" class="button add_to_cart_button product_type_' . $product->get_type() . '">' . esc_attr( $afwhp_custom_button_text ) . '</a>';
													}

													return $cart_txt;
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}

			return $cart_txt;

		}

		/**
		 * Check button is hidden.
		 *
		 * @param WC_Product $product Product.
		 *
		 * @return bool
		 */
		public function button_is_hidden( $product ): bool {

			if ( $product && 'variation' === $product->get_type() ) {
				$product = wc_get_product( $product->get_parent_id() );
			}
			$args = array(
				'post_type'   => 'addify_whp',
				'post_status' => 'publish',
				'numberposts' => -1,
				'orderby'     => 'menu_order',
				'order'       => 'ASC',

			);
			$rules = get_posts( $args );
			if ( ! empty( $rules ) ) {
				foreach ( $rules as $rule ) {
					// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
					$afwhp_rule_type          = get_post_meta( $rule->ID, 'afwhp_rule_type', true );
					$afwhp_hide_products      = unserialize( get_post_meta( $rule->ID, 'afwhp_hide_products', true ) );
					$afwhp_hide_categories    = unserialize( get_post_meta( $rule->ID, 'afwhp_hide_categories', true ) );
					$afwhp_hide_user_role     = unserialize( get_post_meta( $rule->ID, 'afwhp_hide_user_role', true ) );
					$afwhp_is_hide_addtocart  = get_post_meta( $rule->ID, 'afwhp_is_hide_addtocart', true );
					$afwhp_hide_for_countries = unserialize( get_post_meta( $rule->ID, 'afwhp_hide_for_countries', true ) );
					// phpcs:enable WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
					if ( ! empty( $afwhp_hide_for_countries ) ) {
						// country.
						$curr_country = $this->geolocate();
					} else {
						$curr_country = '';
					}

					$istrue = false;

					if ( ! empty( $afwhp_hide_for_countries ) && in_array( $curr_country, $afwhp_hide_for_countries, true ) ) {

						$iscountry = true;

					} elseif ( empty( $afwhp_hide_for_countries ) ) {

						$iscountry = true;

					} else {

						$iscountry = false;
					}

					$applied_on_all_products = get_post_meta( $rule->ID, 'afwhp_apply_on_all_products', true );

					// Registered Users.
					if ( 'afwhp_for_registered_users' === $afwhp_rule_type ) {

						if ( is_user_logged_in() ) {

							// get Current User Role.
							$curr_user      = wp_get_current_user();
							$curr_user_role = $curr_user->roles[0];

							if ( 'yes' === $applied_on_all_products && empty( $afwhp_hide_user_role ) ) {
								$istrue = true;
							} elseif ( ( is_array( $afwhp_hide_user_role ) && in_array( $curr_user_role, $afwhp_hide_user_role, true ) ) && 'yes' === $applied_on_all_products ) {
								$istrue = true;
							} elseif ( ( is_array( $afwhp_hide_user_role ) && in_array( $curr_user_role, $afwhp_hide_user_role, true ) ) && ( is_array( $afwhp_hide_products ) && in_array( $product->get_id(), $afwhp_hide_products, true ) ) ) {
								$istrue = true;
							}

							// Products.
							if ( $istrue && $iscountry ) {

								if ( 'yes' === $afwhp_is_hide_addtocart ) {
									return true;
								}
							}

							// Categories.

							if ( ! empty( $afwhp_hide_categories ) && ! $istrue && $iscountry ) {

								foreach ( $afwhp_hide_categories as $cat ) {

									if ( has_term( $cat, 'product_cat', $product->get_id() ) ) {

										if ( in_array( $curr_user_role, $afwhp_hide_user_role, true ) ) {

											if ( 'yes' === $afwhp_is_hide_addtocart ) {

												return true;
											}
										}
									}
								}
							}
						}
					} else {
						// Guest Users.
						if ( ! is_user_logged_in() ) {

							// Products.
							if ( 'yes' === $applied_on_all_products ) {
								$istrue = true;
							} elseif ( is_array( $afwhp_hide_products ) && in_array( $product->get_id(), $afwhp_hide_products, true ) ) {
								$istrue = true;
							}

							if ( $istrue && $iscountry ) {

								if ( 'yes' === $afwhp_is_hide_addtocart ) {
									return true;
								}
							}

							// Categories.
							if ( ! empty( $afwhp_hide_categories ) && ! $istrue && $iscountry ) {

								foreach ( $afwhp_hide_categories as $cat ) {

									if ( has_term( $cat, 'product_cat', $product->get_id() ) ) {

										if ( 'yes' === $afwhp_is_hide_addtocart ) {
											return true;
										}
									}
								}
							}
						}
					}
				}
			}

			return false;
		}

		/**
		 * Check product has addons
		 *
		 * @param int $product_id product id.
		 *
		 * @return bool
		 */
		public function check_required_addons( $product_id ): bool {
			// No parent add-ons, but yes to global.
			if ( in_array( 'woocommerce-product-addons/woocommerce-product-addons.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
				$addons = WC_Product_Addons_Helper::get_product_addons( $product_id, false, false, true );

				if ( ! empty( $addons ) ) {
					foreach ( $addons as $addon ) {
						if ( isset( $addon['required'] ) && wlfmc_is_true( $addon['required'] ) ) {
							return true;
						}
					}
				}
			}

			return false;
		}


		/**
		 * Get user location.
		 *
		 * @return string
		 */
		public function geolocate(): string {
			if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
				$ip = sanitize_meta( '', $_SERVER['REMOTE_ADDR'], '' );// @codingStandardsIgnoreLine.
			} else {
				$ip = '';
			}
			$host     = 'https://www.geoplugin.net/json.gp?ip=';
			$response = wp_remote_get( $host . $ip );

			if ( is_wp_error( $response ) ) {
				return '';
			}

			$body = wp_remote_retrieve_body( $response );
			$data = $body ? json_decode( $body, true ) : null;
			if ( ! $body || null === $data || empty( $data['geoplugin_countryCode'] ) ) {
				return '';
			}

			return $data['geoplugin_countryCode'];
		}


	}
}


