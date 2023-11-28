<?php
/**
 * Static class that will handle all form submission from customer
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.3.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WLFMC_Form_Handler' ) ) {
	/**
	 * WooCommerce Smart Wishlist Form Handler
	 */
	class WLFMC_Form_Handler {
		/**
		 * Performs all required add_actions to handle forms
		 *
		 * @return void
		 */
		public static function init() {
			/**
			 * This check was added to prevent bots from accidentally executing wishlist code
			 */
			if ( ! self::process_form_handling() ) {
				return;
			}

			// add to wishlist when js is disabled.
			add_action( 'init', array( 'WLFMC_Form_Handler', 'add_to_wishlist' ) );

			// remove from wishlist when js is disabled.
			add_action( 'init', array( 'WLFMC_Form_Handler', 'remove_from_wishlist' ) );

			// remove from wishlist after add to cart.
			add_action( 'woocommerce_add_to_cart', array( 'WLFMC_Form_Handler', 'wlfmc_after_add_to_cart' ), 10, 6 );

			// these actions manage cart, and needs to hooked to wp_loaded.
			add_action( 'wp_loaded', array( 'WLFMC_Form_Handler', 'apply_bulk_actions' ), 15 );
			add_action( 'wp_loaded', array( 'WLFMC_Form_Handler', 'add_all_to_cart' ), 15 );
			add_action( 'wp_loaded', array( 'WLFMC_Form_Handler', 'add_to_cart' ), 15 );

			add_action( 'init', array( 'WLFMC_Form_Handler', 'unsubscribe' ) );
			add_action( 'wp_loaded', array( 'WLFMC_Form_Handler', 'download_pdf_file' ), 1000 );

		}

		/**
		 * Return true if system can process request; false otherwise
		 *
		 * @return bool
		 */
		public static function process_form_handling(): bool {
			$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : false;

			if ( $user_agent && apply_filters( 'wlfmc_block_user_agent', preg_match( '/bot|crawl|slurp|spider|wordpress/i', $user_agent ), $user_agent ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Apply bulk actions to wishlist items
		 *
		 * @return void
		 * @version 1.3.2
		 */
		public static function apply_bulk_actions() {
			if ( ! isset( $_POST['wlfmc_edit_wishlist'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wlfmc_edit_wishlist'] ) ), 'wlfmc_edit_wishlist_action' ) || ( ! isset( $_POST['add_selected_to_cart'] ) && ! isset( $_POST['apply_bulk_actions'] ) ) || empty( $_POST['items'] ) ) {
				return;
			}

			$wishlist_id = isset( $_POST['wishlist_id'] ) ? sanitize_text_field( wp_unslash( $_POST['wishlist_id'] ) ) : false;
			$action      = isset( $_POST['bulk_actions'] ) ? sanitize_text_field( wp_unslash( $_POST['bulk_actions'] ) ) : false;
			$items       = wp_unslash( $_POST['items'] ) ; // phpcs:ignore

			if ( isset( $_POST['add_selected_to_cart'] ) ) {
				$action = 'add_to_cart';
			}

			if ( ! $wishlist_id || ! $action ) {
				return;
			}

			if ( empty( $items ) ) {
				wc_add_notice( esc_html__( 'You have to select at least one product', 'wc-wlfmc-wishlist' ), 'error' );
			}

			$wishlist = wlfmc_get_wishlist( $wishlist_id );

			if ( ! $wishlist ) {
				return;
			}

			$options                  = new MCT_Options( 'wlfmc_options' );
			$remove_after_add_to_cart = 'added-to-cart' === $options->get_option( 'remove_from_wishlist', 'none' );
			$redirect_to_cart         = $options->get_option( 'redirect_after_add_to_cart', true );

			$processed            = array();
			$errors               = array();
			$destination_wishlist = false;
			$message              = false;

			foreach ( $items as $item_id => $prop ) {
				if ( empty( $prop['cb'] ) ) {
					continue;
				}
				$result = false;
				$item   = $wishlist->get_product( $item_id );

				if ( ! $item ) {
					continue;
				}

				switch ( $action ) {
					case 'add_to_cart':
						$product_id = apply_filters( 'wlfmc_woocommerce_add_to_cart_product_id', $item->get_product_id(), $item->get_id() );
						try {
							$product = wc_get_product( $product_id ); // TODO: check worked Properly. old_value =  $item->get_product().
							if ( $product && $product->is_type( 'variable' ) ) {
								wc_add_notice( apply_filters( 'wlfmc_add_to_cart_error_message_for_variable', __( 'you don\'t select a variation for it', 'wc-wlfmc-wishlist' ), $product ), 'error' );
								$errors[ $product_id ] = wlfmc_merge_notices( wp_strip_all_tags( $item->get_formatted_product_name() ) );
								continue 2;
							}
							$meta       = apply_filters( 'wlfmc_woocommerce_add_to_cart_product_meta', $item->get_product_meta( 'view' ), $item->get_id() );
							$cart_item  = apply_filters( 'wlfmc_woocommerce_add_to_cart_product_cart_item', $item->get_cart_item(), $item->get_id() );
							$attributes = array();
							if ( isset( $meta['attributes'] ) && ! empty( $meta['attributes'] ) ) {
								foreach ( $meta['attributes'] as $key => $value ) {
									if ( '' !== $value ) {
										$attributes[ $key ] = $value;
									}
								}
							}
							$variation_id = 0;
							if ( $product && 'variation' === $product->get_type() ) {
								$variation_id = $product_id;
								$product_id   = $product->get_parent_id();
							}
							$attributes        = apply_filters( 'wlfmc_woocommerce_add_to_cart_attributes', $attributes, $item->get_id() );
							$variation_id      = apply_filters( 'wlfmc_woocommerce_add_to_cart_variation_id', $variation_id, $item->get_id() );
							$quantity          = apply_filters( 'wlfmc_woocommerce_add_to_cart_quantity', $item->get_quantity(), $product, $product_id, $variation_id );
							$passed_validation = apply_filters( 'wlfmc_woocommerce_add_to_cart_validation', true, $product, $meta, $item, $cart_item );

							if ( $passed_validation ) {
								$result = WLFMC_Frontend()->add_to_cart( $product_id, $quantity, $variation_id, $attributes, $cart_item, $item );
							}
							if ( false !== $result ) {
								if ( $wishlist->is_current_user_owner() ) {
									$variation_id = 0 === $variation_id ? $product_id : $variation_id;
									do_action( 'wlfmc_product_added_to_cart', $wishlist->get_customer_id(), $wishlist->get_id(), $result, $variation_id, $quantity, $wishlist->get_type() );
								}
							}
							if ( false !== $result && $remove_after_add_to_cart ) {
								$item->delete();
							}
							if ( ! $result ) {
								wc_add_notice( apply_filters( 'wlfmc_add_to_cart_error_message_for_not_validation', __( 'Does not meet the necessary conditions to be added to the cart.', 'wc-wlfmc-wishlist' ), $product ), 'error' );
								$errors[ $product_id ] = wlfmc_merge_notices( wp_strip_all_tags( $item->get_formatted_product_name() ) );
							}
						} catch ( Exception $e ) {
							wc_add_notice( $e->getMessage(), 'error' );
							$errors[ $product_id ] = wlfmc_merge_notices( wp_strip_all_tags( $item->get_formatted_product_name() ) );
							continue 2;
						}
						break;

					case 'delete':
						$result = $item->delete();
						break;
					default:
						// maybe customer wants to move items to another list.
						$destination_wishlist = wlfmc_get_wishlist( $action );

						if ( ! $destination_wishlist ) {
							continue 2;
						}

						$item->set_wishlist_id( $destination_wishlist->get_id() );
						$item->set_date_added( current_time( 'mysql' ) );
						$result = $item->save();
				}

				if ( $result ) {
					$processed[] = $item;
				}
			}

			if ( ! empty( $processed ) ) {
				$count = count( $processed );
				switch ( $action ) {
					case 'add_to_cart':
						/* translators: %s: Number of successful operations . */
						$message = esc_html( sprintf( _n( '%s item have been correctly added to the cart', '%s items have been correctly added to the cart', $count, 'wc-wlfmc-wishlist' ), $count ) );
						break;
					case 'delete':
						/* translators: %s: Number of successful operations . */
						$message = esc_html( sprintf( _n( '%s item have been correctly removed', '%s items have been correctly removed', $count, 'wc-wlfmc-wishlist' ), $count ) );

						break;
					default:
						if ( $destination_wishlist ) {
							// translators: 1. Destination wishlist name.
							$message = sprintf( __( 'The items have been correctly moved to %s', 'wc-wlfmc-wishlist' ), $destination_wishlist->get_formatted_name() );
						}
				}
				if ( $message ) {
					wc_add_notice( $message );
				}
			} elseif ( 'add_to_cart' !== $action ) {
				wc_add_notice( esc_html__( 'An error occurred while processing this action', 'wc-wlfmc-wishlist' ), 'error' );
			}
			if ( ! empty( $errors ) && 'add_to_cart' === $action ) {
				wc_add_notice( implode( '<br>', $errors ), 'error' );
			}

			$cart_url     = wc_get_cart_url();
			$redirect_url = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ) : remove_query_arg( '' );
			$redirect_url = ( 'add_to_cart' === $action && $redirect_to_cart && ! empty( $processed ) ) ? $cart_url : $redirect_url;

			wp_safe_redirect( $redirect_url );
			die();
		}

		/**
		 * Add all items of a wishlist to cart
		 *
		 * @return void
		 * @version 1.5.3
		 */
		public static function add_all_to_cart() {

			if ( ! isset( $_POST['wlfmc_edit_wishlist'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wlfmc_edit_wishlist'] ) ), 'wlfmc_edit_wishlist_action' ) || ! isset( $_REQUEST['add_all_to_cart'] ) ) {
				return;
			}

			$wishlist_id = isset( $_REQUEST['wishlist_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wishlist_id'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification
			$wishlist    = $wishlist_id ? wlfmc_get_wishlist( $wishlist_id ) : false;

			if ( ! $wishlist ) {
				return;
			}

			$options                  = new MCT_Options( 'wlfmc_options' );
			$remove_after_add_to_cart = 'added-to-cart' === $options->get_option( 'remove_from_wishlist', 'none' );
			$redirect_to_cart         = $options->get_option( 'redirect_after_add_to_cart', true );

			$processed = array();
			$errors    = array();
			remove_action(
				'woocommerce_add_to_cart',
				array(
					'WLFMC_Form_Handler',
					'wlfmc_after_add_to_cart',
				)
			);

			do_action( 'wlfmc_before_add_all_to_cart_from_wishlist', $wishlist );

			if ( apply_filters( 'wlfmc_add_all_to_cart_from_wishlist', $wishlist instanceof WLFMC_Wishlist ) ) {

				if ( $wishlist->has_items() ) {
					foreach ( $wishlist->get_items() as $item ) {
						$product_id = apply_filters( 'wlfmc_woocommerce_add_to_cart_product_id', $item->get_product_id(), $item->get_id() );
						$product    = wc_get_product( $product_id ); // TODO: check worked Properly. old_value =  $item->get_product().
						if ( $product && $product->is_type( 'variable' ) ) {
							wc_add_notice( apply_filters( 'wlfmc_add_to_cart_error_message_for_variable', __( 'you don\'t select a variation for it', 'wc-wlfmc-wishlist' ), $product ), 'error' );
							$errors[ $product_id ] = wlfmc_merge_notices( wp_strip_all_tags( $item->get_formatted_product_name() ) );
							continue;
						}

						try {
							$meta       = apply_filters( 'wlfmc_woocommerce_add_to_cart_product_meta', $item->get_product_meta( 'view' ), $item->get_id() );
							$cart_item  = apply_filters( 'wlfmc_woocommerce_add_to_cart_product_cart_item', $item->get_cart_item(), $item->get_id() );
							$attributes = array();
							if ( isset( $meta['attributes'] ) && ! empty( $meta['attributes'] ) ) {
								foreach ( $meta['attributes'] as $key => $value ) {
									if ( '' !== $value ) {
										$attributes[ $key ] = $value;
									}
								}
							}
							$variation_id = 0;
							if ( $product && 'variation' === $product->get_type() ) {
								$variation_id = $product_id;
								$product_id   = $product->get_parent_id();
							}
							$attributes        = apply_filters( 'wlfmc_woocommerce_add_to_cart_attributes', $attributes, $item->get_id() );
							$variation_id      = apply_filters( 'wlfmc_woocommerce_add_to_cart_variation_id', $variation_id, $item->get_id() );
							$quantity          = apply_filters( 'wlfmc_woocommerce_add_to_cart_quantity', $item->get_quantity(), $product, $product_id, $variation_id );
							$passed_validation = apply_filters( 'wlfmc_woocommerce_add_to_cart_validation', true, $product, $meta, $item, $cart_item );
							$cart_item_key     = false;
							if ( $passed_validation ) {
								$cart_item_key = WLFMC_Frontend()->add_to_cart( $product_id, $quantity, $variation_id, $attributes, $cart_item, $item );
							}
							if ( false !== $cart_item_key ) {
								$processed[] = $item;

								if ( $wishlist->is_current_user_owner() ) {
									$variation_id = 0 === $variation_id ? $product_id : $variation_id;
									do_action( 'wlfmc_product_added_to_cart', $wishlist->get_customer_id(), $wishlist->get_id(), $cart_item_key, $variation_id, $quantity, $wishlist->get_type() );
								}

								if ( $remove_after_add_to_cart ) {
									$item->delete();
								}
							} else {
								wc_add_notice( apply_filters( 'wlfmc_add_to_cart_error_message_for_not_validation', __( 'Does not meet the necessary conditions to be added to the cart.', 'wc-wlfmc-wishlist' ), $product ), 'error' );
								$errors[ $product_id ] = wlfmc_merge_notices( wp_strip_all_tags( $item->get_formatted_product_name() ) );
							}
						} catch ( Exception $e ) {
							wc_add_notice( $e->getMessage(), 'error' );
							$errors[ $product_id ] = wlfmc_merge_notices( wp_strip_all_tags( $item->get_formatted_product_name() ) );
							continue;
						}
					}
				}
			}

			if ( ! empty( $processed ) ) {
				/* translators: %s: Number of successful operations . */
				$message = esc_html( sprintf( _n( '%s item have been correctly added to the cart', '%s items have been correctly added to the cart', count( $processed ), 'wc-wlfmc-wishlist' ), count( $processed ) ) );

				wc_add_notice( $message );
			}
			if ( ! empty( $errors ) ) {
				wc_add_notice( implode( '<br>', $errors ), 'error' );
			}

			$cart_url     = wc_get_cart_url();
			$redirect_url = $wishlist_id ? remove_query_arg( '' ) : remove_query_arg( array( 'add_all_to_cart' ) );
			$redirect_url = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ) : $redirect_url; // phpcs:ignore WordPress.Security.NonceVerification
			$redirect_url = $redirect_to_cart && ! empty( $processed ) ? $cart_url : $redirect_url;

			wp_safe_redirect( $redirect_url );
			die;
		}

		/**
		 * Add to cart item
		 *
		 * @return void
		 * @throws Exception If add to cart fails.
		 * @since 1.4.2
		 */
		public static function add_to_cart() {
			if ( ! isset( $_REQUEST['action'] ) || ! isset( $_REQUEST['_wpnonce'] ) || 'wlfmc_add_to_cart' !== $_REQUEST['action'] || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'wlfmc_add_to_cart' ) ) {
				return;
			}

			$item_id     = isset( $_REQUEST['lid'] ) ? absint( $_REQUEST['lid'] ) : '';
			$wishlist_id = isset( $_REQUEST['wid'] ) ? absint( $_REQUEST['wid'] ) : '';

			if ( ! $item_id || ! $wishlist_id ) {
				return;
			}

			$wishlist = wlfmc_get_wishlist( $wishlist_id );

			if ( ! $wishlist ) {
				return;
			}

			$options                  = new MCT_Options( 'wlfmc_options' );
			$remove_after_add_to_cart = 'added-to-cart' === $options->get_option( 'remove_from_wishlist', 'none' );
			$redirect_to_cart         = $options->get_option( 'redirect_after_add_to_cart', true );

			$item = $wishlist->get_item( $item_id );

			if ( ! $item ) {
				return;
			}

			$product_id = apply_filters( 'wlfmc_woocommerce_add_to_cart_product_id', $item->get_product_id(), $item_id );
			$meta       = apply_filters( 'wlfmc_woocommerce_add_to_cart_product_meta', $item->get_product_meta( 'view' ), $item_id );
			$cart_item  = apply_filters( 'wlfmc_woocommerce_add_to_cart_product_cart_item', $item->get_cart_item(), $item_id );
			$product    = wc_get_product( $product_id ); // TODO: check worked Properly. old_value =  $item->get_product().
			$attributes = array();
			if ( isset( $meta['attributes'] ) && ! empty( $meta['attributes'] ) ) {
				foreach ( $meta['attributes'] as $key => $value ) {
					if ( '' !== $value ) {
						$attributes[ $key ] = $value;
					}
				}
			}
			$variation_id = 0;
			if ( $product && 'variation' === $product->get_type() ) {
				$variation_id = $product_id;
				$product_id   = $product->get_parent_id();
			}
			$attributes        = apply_filters( 'wlfmc_woocommerce_add_to_cart_attributes', $attributes, $item_id );
			$variation_id      = apply_filters( 'wlfmc_woocommerce_add_to_cart_variation_id', $variation_id, $item_id );
			$quantity          = apply_filters( 'wlfmc_woocommerce_add_to_cart_quantity', $item->get_quantity(), $product, $product_id, $variation_id );
			$passed_validation = apply_filters( 'wlfmc_woocommerce_add_to_cart_validation', true, $product, $meta, $item, $cart_item );
			if ( $passed_validation ) {
				$cart_item_key = WLFMC_Frontend()->add_to_cart( $product_id, $quantity, $variation_id, $attributes, $cart_item, $item );

				if ( false !== $cart_item_key ) {
					wc_add_to_cart_message( array( $product_id => $quantity ), true );
					if ( $wishlist->is_current_user_owner() ) {
						$variation_id = 0 === $variation_id ? $product_id : $variation_id;
						do_action( 'wlfmc_product_added_to_cart', $wishlist->get_customer_id(), $wishlist->get_id(), $cart_item_key, $variation_id, $quantity, $wishlist->get_type() );
					}
					if ( $remove_after_add_to_cart ) {
						$item->delete();
					}
				}
				$cart_url     = wc_get_cart_url();
				$redirect_url = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ) : remove_query_arg( array( 'lid', 'wid', 'action', '_wpnonce' ) );
				$redirect_url = ( true === $cart_item_key && $redirect_to_cart ) ? $cart_url : $redirect_url;
			} else {
				$product      = $item->get_product();
				$permalink    = apply_filters( 'woocommerce_cart_item_permalink', $product->get_permalink( $cart_item ), $cart_item, '' );
				$redirect_url = apply_filters( 'woocommerce_cart_redirect_after_error', $permalink, $product_id );
			}
			wp_safe_redirect( $redirect_url );
			die();
		}

		/**
		 * Adds a product to wishlist when js is disabled
		 *
		 * @return void
		 *
		 * @version 1.3.1
		 */
		public static function add_to_wishlist() {
			// add item to wishlist when javascript is not enabled.
			if ( isset( $_GET['add_to_wishlist'] ) ) {// phpcs:ignore WordPress.Security.NonceVerification
				try {
					$result = WLFMC()->add();

					$options                        = new MCT_Options( 'wlfmc_options' );
					$click_wishlist_button_behavior = $options->get_option( 'click_wishlist_button_behavior', 'just-add' );

					do_action( 'wlfmc_load_automations', intval( $_GET['add_to_wishlist'] ), $result['wishlist_id'], $result['customer_id'], 'wishlist' );// phpcs:ignore WordPress.Security.NonceVerification

					if ( '' !== $options->get_option( 'product_added_text' ) ) {
						wc_add_notice( $options->get_option( 'product_added_text' ) );

					}

					if ( 'add-redirect' === $click_wishlist_button_behavior ) {
						$wishlist_url = WLFMC()->get_wc_wishlist_url( 'wishlist', 'last_operation' );
						wp_safe_redirect( $wishlist_url );
						die();
					}
				} catch ( Exception $e ) {
					wc_add_notice( apply_filters( 'wlfmc_error_adding_to_wishlist_message', $e->getMessage() ), 'error' );
				}
			}
		}

		/**
		 * Removes from wishlist when js is disabled
		 *
		 * @return void
		 */
		public static function remove_from_wishlist() {
			// remove item from wishlist when javascript is not enabled.
			if ( isset( $_GET['remove_from_wishlist'] ) ) {// phpcs:ignore WordPress.Security.NonceVerification
				try {
					WLFMC()->remove();
					$options = new MCT_Options( 'wlfmc_options' );
					if ( '' !== $options->get_option( 'product_removed_text' ) ) {
						wc_add_notice( $options->get_option( 'product_removed_text' ) );
					}
				} catch ( Exception $e ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}
			}
		}

		/**
		 * Remove from wishlist after adding to cart
		 *
		 * @param string     $cart_item_key cart product unique key.
		 * @param int        $product_id Product id.
		 * @param int        $quantity Quantity.
		 * @param int        $variation_id Variation id.
		 * @param null|array $variation attribute values.
		 * @param array      $cart_item_data extra cart item data we want to pass into the item.
		 *
		 * @return void
		 */
		public static function wlfmc_after_add_to_cart( string $cart_item_key, int $product_id, int $quantity, int $variation_id, $variation, array $cart_item_data ) {
			// phpcs:disable WordPress.Security.NonceVerification
			$wishlist_id   = isset( $_REQUEST['wishlist_id'] ) ? absint( wp_unslash( $_REQUEST['wishlist_id'] ) ) : 0;
			$wishlist_type = isset( $_REQUEST['wishlist_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wishlist_type'] ) ) : '';

			$args = array(
				'wishlist_id' => $wishlist_id,
			);

			if ( isset( $_REQUEST['remove_from_wishlist_after_add_to_cart'] ) ) {
				if ( 'save-for-later' === $wishlist_type ) {
					$args['remove_from_save_for_later'] = $cart_item_key;
				} else {
					$args['remove_from_wishlist'] = intval( $_REQUEST['remove_from_wishlist_after_add_to_cart'] );
				}
			} elseif ( wlfmc_is_wishlist() && isset( $_REQUEST['add-to-cart'] ) ) {
				if ( 'save-for-later' === $wishlist_type ) {
					$args['remove_from_save_for_later'] = $cart_item_key;
				} else {
					$args['remove_from_wishlist'] = intval( $_REQUEST['add-to-cart'] );
				}
			}

			if ( 0 < $args['wishlist_id'] && isset( $args['remove_from_save_for_later'] ) ) {

				try {

					WLFMC()->remove_from_save_for_later( $args );

				} catch ( Exception $e ) {
					// we were unable to remove item from the wishlist; no follow up is provided.
					wc_add_notice( $e->getMessage(), 'error' );
				}
			}

			// phpcs:enable WordPress.Security.NonceVerification
			if ( 0 < $args['wishlist_id'] && isset( $args['remove_from_wishlist'] ) ) {
				$options = new MCT_Options( 'wlfmc_options' );
				if ( 'added-to-cart' !== $options->get_option( 'remove_from_wishlist', 'none' ) ) {
					return;
				}

				try {

					WLFMC()->remove( $args );

				} catch ( Exception $e ) {
					// we were unable to remove item from the wishlist; no follow up is provided.
					wc_add_notice( $e->getMessage(), 'error' );
				}
			}
		}

		/**
		 * Unsubscribe from mailing lists
		 *
		 * @return void
		 * @since 1.4.0
		 */
		public static function unsubscribe() {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( ! isset( $_GET['wlfmc_unsubscribe'] ) && ! isset( $_GET['cid'] ) ) {
				return;
			}

			// retrieve unsubscription_process.
			$unsubscribe_token = sanitize_text_field( wp_unslash( $_GET['wlfmc_unsubscribe'] ) );
			$customer_id       = absint( wp_unslash( $_GET['cid'] ) );

			if ( ! $unsubscribe_token || ! $customer_id > 0 ) {
				return;
			}

			$customer = wlfmc_get_customer( $customer_id );

			if ( ! $customer ) {
				return;
			}

			// phpcs:enable WordPress.Security.NonceVerification

			// redirect uri.
			$redirect = apply_filters( 'wlfmc_after_unsubscribe_redirect', get_permalink( wc_get_page_id( 'shop' ) ) );

			// get current user token.

			$user_unsubscribe_token       = $customer->get_unsubscribe_token();
			$unsubscribe_token_expiration = $customer->get_unsubscribe_expiration();

			// check for match with provided token.
			if ( $unsubscribe_token !== $user_unsubscribe_token ) {
				wc_add_notice( esc_html__( 'The token provided does not match the current user', 'wc-wlfmc-wishlist' ), 'notice' );
				wp_safe_redirect( $redirect );
				die;
			}

			if ( $unsubscribe_token_expiration < time() ) {
				wc_add_notice( esc_html__( 'The token provided is expired; contact us to so we can manually unsubscribe your from the list', 'wc-wlfmc-wishlist' ), 'notice' );
				wp_safe_redirect( $redirect );
				die;
			}

			WLFMC_Wishlist_Factory::unsubscribe_customer( $customer );

			wc_add_notice( defined( 'MC_WLFMC_PREMIUM' ) ? esc_html__( 'You have unsubscribed and you will not receive any more emails and offers unless you enable the settings from your user panel.', 'wc-wlfmc-wishlist' ) : esc_html__( 'You have unsubscribed and you will not receive any more emails and offers.', 'wc-wlfmc-wishlist' ) );
			wp_safe_redirect( $redirect );
			die;
		}

		/**
		 * Download wishlist as pdf file
		 *
		 * @return void
		 * @version 1.5.0
		 * @since 1.4.4
		 */
		public static function download_pdf_file() {
			if ( ! isset( $_GET['download_pdf_wishlist'] ) || ! isset( $_GET['download_pdf_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['download_pdf_nonce'] ) ), 'download_pdf_wishlist' ) ) {
				return;
			}

			$wishlist_id = intval( $_GET['download_pdf_wishlist'] );
			$wishlist    = wlfmc_get_wishlist( $wishlist_id );

			if ( ! $wishlist || ! $wishlist->current_user_can( 'view' ) ) {
				return;
			}

			$options  = new MCT_Options( 'wlfmc_options' );
			$template = wlfmc_get_template(
				'mc-wishlist-pdf.php',
				apply_filters(
					'wlfmc_pdf_parameters',
					array(
						'wishlist'               => $wishlist,
						'wishlist_items'         => $wishlist->get_items(),
						'page_title'             => $wishlist->get_formatted_name(),
						'items_show'             => $options->get_option(
							'wishlist_items_show',
							array(
								'product-stock-status',
								'product-date-added',
								'product-quantity',
								'product-price',
							)
						),
						'empty_wishlist_content' => $options->get_option( 'empty_wishlist_content', esc_html__( 'You have not added any products to your wishlist.', 'wc-wlfmc-wishlist' ) ),
					)
				),
				true
			);

			// send nocache headers.
			nocache_headers();

			if ( ! class_exists( 'TCPDF' ) ) {
				require_once MC_WLFMC_DIR . 'lib/tcpdf/tcpdf.php';
			}

			$font            = 'dejavusans';
			$monospaced_font = 'dejavusansmono';

			// create new PDF document.
			$pdf = new TCPDF( 'L', 'mm', 'A4', true, 'UTF-8', false );
			$pdf->setRTL( is_rtl() );

			// set document information.
			$pdf->SetCreator( get_option( 'blogname' ) );
			$pdf->setTitle( get_option( 'blogname' ) );
			// set default header data.
			$pdf->setHeaderData( '', 0, get_option( 'blogname' ), get_option( 'blogdescription' ), array( 0, 0, 0 ), array( 235, 235, 235 ) );
			$pdf->setFooterData( array( 0, 0, 0 ), array( 235, 235, 235 ) );

			$pdf->setHeaderMargin( PDF_MARGIN_HEADER );
			$pdf->setFooterMargin( PDF_MARGIN_FOOTER );
			$pdf->setHeaderFont( array( $font, '', PDF_FONT_SIZE_MAIN ) );
			// set default monospaced font.
			$pdf->SetDefaultMonospacedFont( $monospaced_font );

			// set margins.
			$pdf->SetMargins( PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT, false );

			// set auto page breaks.
			$pdf->SetAutoPageBreak( true, 10 );

			// set font.
			$pdf->SetFont( $font, '', 10 );

			// add a page.
			$pdf->AddPage();

			// output the HTML content.
			$pdf->writeHTML( $template, true, false, false, true, '' );

			// reset pointer to the last page.
			$pdf->lastPage();

			// Close and output PDF document.
			$pdf->Output( $wishlist->get_formatted_name() . '.pdf' );

			die();
		}

	}
}

WLFMC_Form_Handler::init();
