<?php
/**
 * Smart Wishlist Analytics
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.4.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WLFMC_Analytics' ) ) {
	/**
	 * WooCommerce Wishlist Analytics
	 */
	class WLFMC_Analytics {


		/**
		 * Single instance of the class
		 *
		 * @var WLFMC_Analytics
		 */
		protected static $instance;


		/**
		 * Constructor
		 *
		 * @return void
		 */
		public function __construct() {
			$this->hooks();
		}

		/**
		 * Define Hooks
		 *
		 * @return void
		 */
		public function hooks() {

			add_action( 'wlfmc_product_added_to_cart', array( $this, 'add_item_data' ), 10, 6 );
			add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'add_order_item_meta' ), 10, 4 );
			add_action( 'woocommerce_checkout_order_created', array( $this, 'save_order_customer_id' ) );
			add_action( 'woocommerce_remove_cart_item', array( __CLASS__, 'remove_item_data' ) );
			add_action( 'woocommerce_cart_emptied', array( __CLASS__, 'remove_item_data_cart_session' ) );

			// show  item meta in admin order.
			add_action( 'woocommerce_after_order_itemmeta', array( __CLASS__, 'show_meta_in_order_item' ), 10, 3 );

			add_action( 'woocommerce_order_status_changed', array( $this, 'order_status_analytics' ), 9, 3 );

			add_action( 'wlfmc_added_to_wishlist', array( $this, 'added_to_analytics' ), 10, 4 );

		}

		/**
		 * Add product to analytics after added to list.
		 *
		 * @param int                 $product_id Product id.
		 * @param int                 $wishlist_id Wishlist id.
		 * @param WLFMC_Wishlist_Item $item Wishlist item.
		 * @param string              $list_type list type.
		 *
		 * @return false|int|void
		 */
		public function added_to_analytics( int $product_id, int $wishlist_id, $item, $list_type ) {
			global $wpdb;

			if ( ! $item->get_customer_id() > 0 ) {
				return false;
			}

			$product_id = wlfmc_object_id( $product_id, 'product', true, 'default' );
			$exists     = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->wlfmc_wishlist_analytics WHERE customer_id = %d AND prod_id = %d AND wishlist_id = %d AND list_type = %s AND DATE(dateadded) = CURDATE() LIMIT 1", $item->get_customer_id(), $product_id, $wishlist_id, $list_type ) );// phpcs:ignore WordPress.DB
			if ( ! $exists ) {
				$product = wc_get_product( $product_id );

				if ( ! $product ) {
					return 0;
				}
				try {
					$price = $item->get_product_price();
				} catch ( Exception $e ) {
					$price = $item->get_original_price( 'edit' );
				}

				$wpdb->insert( // phpcs:ignore WordPress.DB
					$wpdb->wlfmc_wishlist_analytics,
					array(
						'customer_id' => $item->get_customer_id(),
						'prod_id'     => $product_id,
						'wishlist_id' => $wishlist_id,
						'price'       => $price,
						'quantity'    => $item->get_quantity(),
						'currency'    => get_woocommerce_currency(),
						'list_type'   => $list_type,
					),
					array(
						'%d',
						'%d',
						'%d',
						'%f',
						'%d',
						'%s',
						'%s',
					)
				);

			}

		}

		/**
		 * Insert product to analytics and set sell with list if coupon used set to coupon.
		 *
		 * @param array  $data Array of data.
		 * @param string $type buy through list or coupon.
		 *
		 * @return void
		 */
		public function sell_product_with_lists( array $data, string $type = 'buy-through-list' ) {
			global $wpdb;

			$defaults = array(
				'customer_id'   => 0,
				'order_id'      => 0,
				'user_id'       => 0,
				'wishlist_id'   => 0,
				'wishlist_type' => '',
				'product_id'    => 0,
				'quantity'      => 1,
				'price'         => 0,
			);

			$args = wp_parse_args( $data, $defaults );
			// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
			extract( $args );

			$exists_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->wlfmc_wishlist_analytics WHERE prod_id = %d AND wishlist_id = %d AND customer_id = %d AND type='add-to-list' AND list_type=%s  LIMIT 1", $product_id, $wishlist_id, $customer_id, $wishlist_type ) );// phpcs:ignore WordPress.DB
			if ( $exists_id ) {
				$wpdb->update( // phpcs:ignore WordPress.DB
					$wpdb->wlfmc_wishlist_analytics,
					array(
						'order_id'      => $order_id,
						'quantity'      => $quantity,
						'price'         => $price,
						'type'          => $type,
						'datepurchased' => current_time( 'mysql' ),
						'currency'      => get_woocommerce_currency(),
					),
					array( 'ID' => $exists_id ),
					array(
						'%d',
						'%d',
						'%f',
						'%s',
						'%s',
						'%s',
					),
					array( '%d' )
				);
			} else {
				$wpdb->insert( // phpcs:ignore WordPress.DB
					$wpdb->wlfmc_wishlist_analytics,
					array(
						'customer_id'   => $customer_id,
						'order_id'      => $order_id,
						'prod_id'       => $product_id,
						'wishlist_id'   => $wishlist_id,
						'quantity'      => $quantity,
						'price'         => $price,
						'type'          => $type,
						'datepurchased' => current_time( 'mysql' ),
						'currency'      => get_woocommerce_currency(),
						'list_type'     => $wishlist_type,
					),
					array(
						'%d',
						'%d',
						'%d',
						'%d',
						'%d',
						'%f',
						'%s',
						'%s',
						'%s',
						'%s',
					)
				);
			}

		}

		/**
		 * Insert product to analytics and set sell with coupons.
		 *
		 * @param integer $customer_id Customer id.
		 * @param integer $order_id Order id.
		 * @param integer $prod_id Product id.
		 * @param integer $quantity Quantity.
		 * @param string  $price Product price.
		 *
		 * @return void
		 */
		public function sell_product_with_coupons( $customer_id, $order_id, $prod_id, $quantity, $price ) {
			global $wpdb;

			$wpdb->insert( // phpcs:ignore WordPress.DB
				$wpdb->wlfmc_wishlist_analytics,
				array(
					'customer_id'   => $customer_id,
					'order_id'      => $order_id,
					'prod_id'       => $prod_id,
					'wishlist_id'   => 0,
					'quantity'      => $quantity,
					'price'         => $price,
					'type'          => 'buy-through-coupon',
					'datepurchased' => current_time( 'mysql' ),
					'currency'      => get_woocommerce_currency(),
				),
				array(
					'%d',
					'%d',
					'%d',
					'%d',
					'%d',
					'%f',
					'%s',
					'%s',
					'%s',
				)
			);

		}

		/**
		 * Remove product added from wishlist
		 *
		 * @param string|null $cart_item_key Cart product key.
		 *
		 * @return boolean
		 */
		public static function remove_item_data( string $cart_item_key = null ): bool {
			$data = (array) WC()->session->get( 'wlfmc_wishlist_data', array() );
			if ( empty( $cart_item_key ) ) {
				WC()->session->set( 'wlfmc_wishlist_data', array() );

				return true;
			}
			if ( ! array_key_exists( $cart_item_key, $data ) ) {
				return false;
			}
			unset( $data[ $cart_item_key ] );
			WC()->session->set( 'wlfmc_wishlist_data', $data );

			return true;
		}

		/**
		 * Clear wishlist cart session.
		 *
		 * @param bool $clear_persistent_cart Should the persistent cart be cleared too. Defaults to true.
		 */
		public static function remove_item_data_cart_session( $clear_persistent_cart = true ) {
			if ( $clear_persistent_cart ) {
				WC()->session->set( 'wlfmc_wishlist_data', array() );

			}
		}

		/**
		 * Add wishlist data to item in cart
		 *
		 * @param integer $customer_id Customer id.
		 * @param integer $wishlist_id Wishlist id.
		 * @param string  $cart_item_key Cart product key.
		 * @param integer $product_id Product id.
		 * @param integer $quantity Product quantity.
		 * @param string  $wishlist_type Wishlist type.
		 *
		 * @return void
		 */
		public function add_item_data( int $customer_id, int $wishlist_id, string $cart_item_key, int $product_id, int $quantity, string $wishlist_type ) {
			$data                   = (array) WC()->session->get( 'wlfmc_wishlist_data', array() );
			$data[ $cart_item_key ] = array(
				'customer_id'   => $customer_id,
				'wishlist_id'   => $wishlist_id,
				'product_id'    => $product_id,
				'wishlist_type' => $wishlist_type,
			);
			WC()->session->set( 'wlfmc_wishlist_data', $data );
		}


		/**
		 * Add metadata for product when created order
		 *
		 * @param WC_Order_Item_Product $item order item object.
		 * @param string                $cart_item_key cart item key.
		 * @param array                 $values cart item values.
		 * @param WC_order              $order Order object.
		 */
		public function add_order_item_meta( $item, $cart_item_key, $values, $order ) {
			$data = $this->get_item_data( $cart_item_key );
			if ( ! empty( $data ) ) {
				$item->update_meta_data( '_wlfmc_wishlist_cart', $data );
				$item->save();
			}
		}

		/**
		 * Show item meta in admin order page.
		 *
		 * @param int    $item_id The id of the item being displayed.
		 * @param object $item The item being displayed.
		 * @param object $product Product.
		 *
		 * @return void
		 */
		public static function show_meta_in_order_item( $item_id, $item, $product ) {
			$wlfmc_meta = $item->get_meta( '_wlfmc_wishlist_cart' );
			if ( $wlfmc_meta && isset( $wlfmc_meta['wishlist_type'] ) ) {
				$list_type = '';
				switch ( $wlfmc_meta['wishlist_type'] ) {
					case 'lists':
						$list_type = __( 'Mc Multi-List', 'wc-wlfmc-wishlist' );
						break;
					case 'save-for-later':
						$list_type = __( 'Mc Save For Later', 'wc-wlfmc-wishlist' );
						break;
					case 'waitlist':
						$list_type = __( 'Mc WaitList', 'wc-wlfmc-wishlist' );
						break;
					case 'wishlist':
						$list_type = __( 'Mc Wishlist', 'wc-wlfmc-wishlist' );
				}
				if ( ! empty( $list_type ) ) {
					/* translators: 1: list type */
					echo '<small class="badge">' . esc_attr( sprintf( __( 'Added by %s', 'wc-wlfmc-wishlist' ), $list_type ) ) . '</small>';
				}
			}
		}

		/**
		 * Save customer id to order for guest when created order
		 *
		 * @param WC_Order $order Order object.
		 */
		public function save_order_customer_id( WC_Order $order ) {
			global $wpdb;
			if ( ! is_user_logged_in() ) {
				$customer = WLFMC_Wishlist_Factory::get_current_customer( false, 'edit' );
				if ( $customer->is_session_based() ) {
					$order->update_meta_data( 'wlfmc_customer_id', $customer->get_id() );
					$order->save();
				}
			}
		}


		/**
		 * Get product added from wishlist
		 *
		 * @param string $cart_item_key Cart product key.
		 *
		 * @return array
		 */
		public function get_item_data( $cart_item_key ) {
			$data = (array) WC()->session->get( 'wlfmc_wishlist_data', array() );
			if ( empty( $data[ $cart_item_key ] ) ) {
				$data[ $cart_item_key ] = array();
			}

			return $data[ $cart_item_key ];
		}


		/**
		 * Analytics check completed orders
		 *
		 * @param integer $order_id Order id.
		 * @param string  $old_status Not used.
		 * @param string  $new_status Updated status order.
		 *
		 * @return void
		 */
		public function order_status_analytics( int $order_id, string $old_status, string $new_status ) {
			global $wpdb;
			$new_status = str_replace( 'wc-', '', $new_status );
			$order      = wc_get_order( $order_id );

			if ( $order && in_array(
				$new_status,
				array(
					'processing',
					'completed',
				),
				true
			) && empty( $order->get_meta( '_wlfmc_analytics_processed', true ) ) ) {

				$items = $order->get_items();
				if ( empty( $items ) || ! is_array( $items ) ) {
					return;
				}

				$user_id     = $order->get_user_id();
				$customer_id = (int) $order->get_meta( 'wlfmc_customer_id', true );
				if ( ! empty( $customer_id ) ) {
					$customer = wlfmc_get_customer( $customer_id );
					if ( $customer ) {
						$order_customer_id = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
							$wpdb->prepare( "SELECT customer_id FROM {$wpdb->prefix}wc_customer_lookup WHERE email = %s", $order->get_billing_email() )
						);
						if ( $order_customer_id ) {
							$customer->set_order_customer_id( $order_customer_id );
							if ( '' === $customer->get_first_name() ) {
								$customer->set_first_name( $order->get_billing_first_name() );
							}
							if ( '' === $customer->get_last_name() ) {
								$customer->set_last_name( $order->get_billing_last_name() );
							}
							if ( '' === $customer->get_email() ) {
								$customer->set_email( $order->get_billing_email() );
							}
							if ( '' === $customer->get_phone() ) {
								$customer->set_phone( $order->get_billing_phone() );
							}
							$customer->save();
						}
					}
				}
				$customer_id = empty( $customer_id ) ? wlfmc_get_customer_id_by_user( $user_id ) : $customer_id;
				$coupon_used = false;
				$wishlists   = false;
				foreach ( $order->get_coupon_codes() as $coupon_code ) {
					// Get the WC_Coupon object.
					$coupon           = new WC_Coupon( $coupon_code );
					$analytics_coupon = $coupon->get_meta( 'wlfmc_analytics' );

					if ( in_array( $analytics_coupon, array( 'automation', 'campaign' ), true ) ) {
						$coupon_used = true;
					}
				}
				if ( ! $coupon_used ) {
					$wishlists = WLFMC_Wishlist_Factory::get_wishlists(
						array(
							'customer_id' => $customer_id,
						)
					);
				}

				foreach ( $items as $item ) {
					$price          = $item->get_quantity() > 0 ? $item->get_total() / $item->get_quantity() : $item->get_total();
					$data           = array(
						'order_id'      => $order_id,
						'user_id'       => $user_id,
						'quantity'      => $item->get_quantity(),
						'price'         => $price,
						'product_id'    => 0,
						'wishlist_id'   => 0,
						'wishlist_type' => '',
						'customer_id'   => $customer_id,
					);
					$find_in_lists  = false;
					$_wishlist_cart = $item->get_meta( '_wlfmc_wishlist_cart' );

					if ( $_wishlist_cart ) {

						$find_in_lists         = true;
						$data['wishlist_id']   = $_wishlist_cart['wishlist_id'];
						$data['product_id']    = $_wishlist_cart['product_id'];
						$data['wishlist_type'] = $_wishlist_cart['wishlist_type'] ?? '';
						$data['customer_id']   = $_wishlist_cart['customer_id'] ?? '';
					} else {

						if ( ! empty( $wishlists ) ) {
							foreach ( $wishlists as $wishlist ) {

								$product_id   = $item->get_product_id();
								$variation_id = $item->get_variation_id();

								if ( $variation_id && $wishlist->has_product( $variation_id ) ) {
									$find_in_lists         = true;
									$data['wishlist_id']   = $wishlist->get_id();
									$data['product_id']    = $variation_id;
									$data['wishlist_type'] = $wishlist->get_type();
									$data['customer_id']   = $wishlist->get_customer_id();
									break;
								} elseif ( $product_id && $wishlist->has_product( $product_id ) ) {
									$find_in_lists         = true;
									$data['wishlist_id']   = $wishlist->get_id();
									$data['product_id']    = $product_id;
									$data['wishlist_type'] = $wishlist->get_type();
									$data['customer_id']   = $wishlist->get_customer_id();
									break;
								}
							}
						}
					}

					if ( $coupon_used && $find_in_lists ) {

						$this->sell_product_with_lists( $data, 'buy-through-coupon' );

					} elseif ( $coupon_used ) {

						$product_id   = $item->get_product_id();
						$variation_id = $item->get_variation_id();
						$prod_id      = $variation_id ? $variation_id : $product_id;
						$this->sell_product_with_coupons( $customer_id, $order_id, $prod_id, $item->get_quantity(), $price );

					} elseif ( $find_in_lists ) {

						$this->sell_product_with_lists( $data );
					}
				}

				$order->update_meta_data( '_wlfmc_analytics_processed', '1' );
				$order->save();
			}
		}


		/**
		 * Returns single instance of the class.
		 *
		 * @access public
		 *
		 * @return WLFMC_Analytics
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}


	}

}
/**
 * Unique access to instance of WLFMC_Analytics class.
 *
 * @return WLFMC_Analytics
 */
function WLFMC_Analytics(): WLFMC_Analytics {  // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return WLFMC_Analytics::get_instance();
}
