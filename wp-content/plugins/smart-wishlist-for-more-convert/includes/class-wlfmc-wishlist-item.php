<?php
/**
 * Wishlist Item Class
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WLFMC_Wishlist_Item' ) ) {
	/**
	 * This class describes Wishlist Item object, and it is meant to be created by WLFMC_Wishlist class, via
	 * get_items method
	 */
	class WLFMC_Wishlist_Item extends WC_Data implements ArrayAccess {

		/**
		 * Item Data array
		 *
		 * @var array
		 */
		protected $data = array(
			'wishlist_id'       => 0,
			'product_id'        => 0,
			'parent_product_id' => 0,
			'quantity'          => 1,
			'customer_id'       => 0,
			'user_id'           => 0,
			'date_added'        => '',
			'position'          => 0,
			'original_price'    => 0,
			'original_currency' => '',
			'on_sale'           => 0,
			'back_in_stock'     => 0,
			'price_change'      => 0,
			'low_stock'         => 0,
			'product_meta'      => '',
			'posted_data'       => '',
			'cart_item_key'     => '',
		);

		/**
		 * Calculated price.
		 *
		 * @var string
		 */
		protected $calculated_price = null;

		/**
		 * Register product to avoid retrieving it more than once
		 *
		 * @var WC_Product
		 */
		protected $product = null;

		/**
		 * Register origin wishlist ID;
		 * if item is moved to another wishlist, we can then clear origin wishlist cache
		 *
		 * @var int
		 */
		protected $origin_wishlist_id = 0;

		/**
		 * Stores meta in cache for future reads.
		 * A group must be set to enable caching.
		 *
		 * @var string
		 */
		protected $cache_group = 'wlfmc-wishlist-items';

		/**
		 * Constructor.
		 *
		 * @param int|object|array $item ID to load from the DB, or WLFMC_Wishlist_Item object.
		 *
		 * @throws Exception When cannot loading correct Data Store object.
		 */
		public function __construct( $item = 0 ) {
			parent::__construct( $item );

			if ( $item instanceof WLFMC_Wishlist_Item ) {
				$this->set_id( $item->get_id() );
			} elseif ( is_numeric( $item ) && $item > 0 ) {
				$this->set_id( $item );
			} else {
				$this->set_object_read( true );
			}

			$this->data_store = WC_Data_Store::load( 'wlfmc-wishlist-item' );

			if ( $this->get_id() > 0 ) {
				$this->data_store->read( $this );
			}

			if ( $this->get_object_read() ) {
				$this->origin_wishlist_id = $this->get_wishlist_id();
			}
		}

		/* === GETTERS === */

		/**
		 * Get wishlist ID for current item
		 *
		 * @param string $context Context.
		 *
		 * @return int Wishlist ID
		 */
		public function get_wishlist_id( $context = 'view' ) {
			return (int) $this->get_prop( 'wishlist_id', $context );
		}

		/**
		 * Get origin wishlist ID for current item
		 *
		 * @return int Wishlist ID
		 */
		public function get_origin_wishlist_id() {
			return $this->origin_wishlist_id;
		}

		/**
		 * Get origin product ID for current item (no WPML filtering)
		 *
		 * @param string $context Context.
		 *
		 * @return int Wishlist ID
		 */
		public function get_original_product_id( $context = 'view' ) {
			return (int) $this->get_prop( 'product_id', $context );
		}

		/**
		 * Get product ID for current item
		 *
		 * @param string $context Context.
		 *
		 * @return int Product ID
		 */
		public function get_product_id( $context = 'view' ) {
			return wlfmc_wpml_object_id( $this->get_original_product_id( $context ), 'product', true );
		}

		/**
		 * Get Parent product ID for current item
		 *
		 * @param string $context Context.
		 *
		 * @return int Parent Product ID
		 */
		public function get_parent_id( $context = 'view' ) {
			$product_id = $this->get_original_product_id();
			$product    = wc_get_product( $product_id );
			return $product && $product->is_type( 'variation' ) ? $product->get_parent_id() : $product_id;
		}

		/**
		 * Return product object related to current item
		 *
		 * @param string $context Context.
		 *
		 * @return WC_Product Product
		 */
		public function get_product( $context = 'view' ) {
			if ( empty( $this->product ) ) {

				$product = wc_get_product( $this->get_product_id( $context ) );

				if ( $product ) {
					$this->product = $product;
				}
			}

			return $this->product;
		}

		/**
		 * Return price of the produce related to current item
		 *
		 * @param string $context Context.
		 *
		 * @return float
		 * @throws Exception Exception.
		 */
		public function get_product_price( $context = 'view' ) {
			$product = $this->get_product( $context );

			if ( ! $product ) {
				return 0;
			}

			$meta = $this->get_product_meta( 'view' );
			if ( ! empty( $meta ) ) {
				return $this->calculate_product_price( $context );
			}

			switch ( $product->get_type() ) {
				case 'variable':
					/**
					 * Get product price variation
					 *
					 * @var $product WC_Product_Variable
					 */
					return (float) $product->get_variation_price( 'min' );
				default:
					$sale_price = $product->get_sale_price();

					return $sale_price ? (float) $sale_price : (float) $product->get_price();
			}
		}

		/**
		 * Calculate price of the produce related to current item
		 *
		 * @param string $context Context.
		 *
		 * @throws Exception Exception.
		 * @return float
		 */
		public function calculate_product_price( $context = 'view' ) {
			$product = $this->get_product( $context );

			if ( ! $product ) {
				return 0;
			}

			if ( null !== $this->calculated_price ) {
				return $this->calculated_price;
			}
			try {
				$variations = array();
				$meta       = $this->get_product_meta( 'view' );
				if ( empty( $meta ) ) {
					throw new Exception();
				}
				foreach ( $_REQUEST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					if ( 'attribute_' !== substr( $key, 0, 10 ) || '' === $value ) {
						continue;
					}
					$variations['attributes'][ sanitize_title( wp_unslash( $key ) ) ] = wp_unslash( $value );
				}
				$woocommerce_add_cart_item = apply_filters(
					'woocommerce_add_cart_item',
					array_merge(
						$meta,
						array(
							'key'          => '',
							'product_id'   => $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id(),
							'variation_id' => $product->is_type( 'variation' ) ? $product->get_id() : 0,
							'variation'    => $variations,
							'quantity'     => $this->get_quantity(),
							'data'         => $this->get_product(),
							'data_hash'    => '',
						)
					),
					''
				);
				$price                     = $woocommerce_add_cart_item['data']->get_price();
			} catch ( Exception $e ) {
				$sale_price = $product->get_sale_price();
				$price      = $sale_price ? (float) $sale_price : (float) $product->get_price();
			}
			$this->calculated_price = (float) apply_filters( 'wlfmc_wishlist_item_price', $price, $this->get_product_meta( 'view' ), $product, $this );

			return $this->calculated_price;
		}


		/**
		 * Retrieve formatted price for current item
		 *
		 * @param string $context Context.
		 *
		 * @return string Formatter price
		 * @throws Exception Exception.
		 */
		public function get_formatted_product_price( $context = 'view' ) {
			$product = $this->get_product( $context );

			$base_price = $product->is_type( 'variable' ) ? $product->get_variation_regular_price( 'max' ) : $product->get_price();
			$meta       = $this->get_product_meta( 'view' );
			if ( is_array( $meta ) && isset( $meta['attributes'] ) ) {
				unset( $meta['attributes'] );
			}
			if ( empty( $meta ) ) {
				$formatted_price = $base_price ? $product->get_price_html() : ( '0' === $base_price ? apply_filters( 'wlfmc_free_text', esc_html__( 'Free!', 'wc-wlfmc-wishlist' ), $product ) : '' );

			} else {
				$base_price      = $this->get_product_price( 'edit' );
				$formatted_price = $base_price ? wc_price( $this->get_product_price( 'view' ) ) : ( '0' === $base_price ? apply_filters( 'wlfmc_free_text', esc_html__( 'Free!', 'wc-wlfmc-wishlist' ), $product ) : '' );
			}

			return apply_filters( 'wlfmc_item_formatted_price', $formatted_price, $base_price, $product, $this );
		}

		/**
		 * Retrieve formatted original price for current item
		 *
		 * @param string $context Context.
		 *
		 * @return string Formatter price
		 */
		public function get_formatted_original_price( $context = 'view' ) {
			$product = $this->get_product( $context );

			$base_price      = $this->get_original_price( 'edit' );
			$formatted_price = $base_price ? $this->get_original_price( 'view' ) : ( '0' === $base_price ? apply_filters( 'wlfmc_free_text', esc_html__( 'Free!', 'wc-wlfmc-wishlist' ), $product ) : '' );

			return apply_filters( 'wlfmc_item_formatted_original_price', $formatted_price, $base_price, $product, $this );
		}

		/**
		 * Return formatted product name
		 *
		 * @param string $context Context.
		 *
		 * @return string Formatted name; empty string on failure
		 */
		public function get_formatted_product_name( $context = 'view' ) {
			$product = $this->get_product( $context );

			if ( ! $product ) {
				return '';
			}

			return $product->get_formatted_name();
		}

		/**
		 * Get quantity for current item
		 *
		 * @param string $context Context.
		 *
		 * @return int Quantity
		 */
		public function get_quantity( $context = 'view' ) {
			return max( 1, (int) $this->get_prop( 'quantity', $context ) );
		}

		/**
		 * Get user ID for current item
		 *
		 * @param string $context Context.
		 *
		 * @return int User ID
		 */
		public function get_user_id( $context = 'view' ) {
			return (int) $this->get_prop( 'user_id', $context );
		}

		/**
		 * Get customer ID for current item
		 *
		 * @param string $context Context.
		 *
		 * @return int Customer ID
		 */
		public function get_customer_id( $context = 'view' ) {
			return (int) $this->get_prop( 'customer_id', $context );
		}

		/**
		 * Get user for current item
		 *
		 * @param string $context Context.
		 *
		 * @return WP_User|bool User
		 */
		public function get_user( $context = 'view' ) {
			$user_id = (int) $this->get_prop( 'user_id', $context );

			if ( ! $user_id ) {
				return false;
			}

			return get_user_by( 'id', $user_id );
		}

		/**
		 * Get wishlist date added
		 *
		 * @param string $context Context.
		 *
		 * @return WC_DateTime|string Wishlist date of creation
		 */
		public function get_date_added( $context = 'view' ) {
			$date_added = $this->get_prop( 'date_added', $context );

			if ( $date_added && 'view' === $context ) {
				return $date_added->date_i18n( 'Y-m-d H:i:s' );
			} else {
				return $date_added;
			}
		}

		/**
		 * Get formatted wishlist date added
		 *
		 * @param string $format Date format (if empty, WP date format will be applied).
		 *
		 * @return string Wishlist date of creation
		 */
		public function get_date_added_formatted( $format = '' ) {
			$date_added = $this->get_date_added( 'edit' );

			if ( $date_added ) {
				$format = $format ? $format : get_option( 'date_format' );

				return $date_added->date_i18n( $format );
			}

			return '';
		}

		/**
		 * Get related wishlist
		 *
		 * @return WLFMC_Wishlist|bool Wishlist object, or false on failure
		 */
		public function get_wishlist() {
			$wishlist_id = $this->get_wishlist_id();

			if ( ! $wishlist_id ) {
				return false;
			}

			return WLFMC_Wishlist_Factory::get_wishlist( $wishlist_id );
		}

		/**
		 * Get related customer
		 *
		 * @return WLFMC_Customer|bool Customer object, or false on failure
		 */
		public function get_customer() {
			$customer_id = $this->get_customer_id();

			if ( ! $customer_id ) {
				return false;
			}

			return WLFMC_Wishlist_Factory::get_customer( $customer_id );
		}

		/**
		 * Get related wishlist slug
		 *
		 * @return string|bool Wishlist slug, or false on failure
		 */
		public function get_wishlist_slug() {
			$wishlist = $this->get_wishlist();

			if ( ! $wishlist ) {
				return false;
			}

			return $wishlist->get_slug();
		}

		/**
		 * Get related wishlist name
		 *
		 * @return string|bool Wishlist name, or false on failure
		 */
		public function get_wishlist_name() {
			$wishlist = $this->get_wishlist();

			if ( ! $wishlist ) {
				return false;
			}

			return $wishlist->get_name();
		}

		/**
		 * Get related wishlist token
		 *
		 * @return string|bool Wishlist token, or false on failure
		 */
		public function get_wishlist_token() {
			$wishlist = $this->get_wishlist();

			if ( ! $wishlist ) {
				return false;
			}

			return $wishlist->get_token();
		}

		/**
		 * Return item position inside the list
		 *
		 * @param string $context Context.
		 *
		 * @return int Position
		 */
		public function get_position( $context = 'view' ) {
			return (int) $this->get_prop( 'position', $context );
		}

		/**
		 * Return cart item key
		 *
		 * @param string $context Context.
		 *
		 * @return string cart_item_key
		 */
		public function get_cart_item_key( $context = 'view' ) {
			return $this->get_prop( 'cart_item_key', $context );
		}

		/**
		 * Return original price
		 *
		 * @param string $context Context.
		 *
		 * @return string Original price
		 */
		public function get_original_price( $context = 'view' ) {
			$price = $this->get_prop( 'original_price', 'edit' );

			if ( 'view' === $context ) {
				return wc_price(
					$price,
					array(
						'currency' => $this->get_original_currency(),
					)
				);
			}

			return $price;
		}

		/**
		 * Return original currency
		 *
		 * @param string $context Context.
		 *
		 * @return string Original price
		 */
		public function get_original_currency( $context = 'view' ) {
			$currency = $this->get_prop( 'original_currency', 'edit' );

			if ( 'view' === $context && ! $currency ) {
				$currency = get_woocommerce_currency();
			}

			return $currency;
		}

		/**
		 * Returns a formatted HTML template for the "Price variation" label
		 *
		 * @return string HTML for the template, or empty string if price variation is not applicable to current item
		 * @throws Exception Exception.
		 */
		public function get_price_variation() {
			$original_currency = $this->get_original_currency( 'edit' );

			// if currency changed, makes no sense to make comparisons.
			if ( get_woocommerce_currency() !== $original_currency ) {
				return '';
			}

			$original_price = $this->get_original_price( 'edit' );

			// original price wasn't stored in the wishlist.
			if ( ! $original_price ) {
				return '';
			}

			$product       = $this->get_product();
			$current_price = $this->get_product_price();

			if ( ! $current_price || ! is_numeric( $current_price ) ) {
				return '';
			}

			$difference = round( $original_price - $current_price, 2 );

			if ( $difference < 0 && apply_filters( 'wlfmc_hide_price_increase', true, $product, $original_price, $original_currency ) ) {
				return '';
			}

			$percentage_difference = - 1 * round( $difference / $original_price * 100, 2 );

			if ( 0 === absint( $percentage_difference ) ) {
				return '';
			}

			$class    = $percentage_difference > 0 ? 'increase' : 'decrease';
			$template = apply_filters(
				'wlfmc_price_variation_template',
				sprintf(
					'<span class="price-variation %s"><span class="variation-rate">%s</span></span>',
					$class,
					// translators: 1. % of reduction/increase in price. 2: original product price.
					_x( 'Price changed by %1$s%% from %2$s', 'The template that shows price variation since addition to list.', 'wc-wlfmc-wishlist' )
				),
				$class,
				$percentage_difference,
				$original_price,
				$original_currency,
				$this
			);
			return sprintf( $template, $percentage_difference, wc_price( $original_price, array( 'currency' => $original_currency ) ) );
		}

		/**
		 * Return meta product
		 *
		 * @param string $context Context.
		 *
		 * @return string|array meta product
		 */
		public function get_product_meta( $context = 'view' ) {
			$meta = $this->get_prop( 'product_meta', 'edit' );
			if ( $meta && 'view' === $context ) {
				return json_decode( $meta, true );
			} else {
				return $meta;
			}
		}

		/**
		 * Return post data
		 *
		 * @param string $context Context.
		 *
		 * @return string|mixed meta product
		 */
		public function get_posted_data( $context = 'view' ) {
			$meta = $this->get_prop( 'posted_data', 'edit' );
			if ( $meta && 'view' === $context ) {
				return json_decode( $meta, true );
			} else {
				return $meta;
			}
		}

		/**
		 * Return state of on_sale flag
		 * Important: this flag is used for email campaigns, and doesn't necessarily represent
		 * current on_sale status for the product
		 * Plugins checks every day to find on_sale products, and to schedule email sending
		 *
		 * @param string $context Context.
		 *
		 * @return bool Whether product was on sale during last check that plugin performed
		 */
		public function is_on_sale( $context = 'view' ) {
			return (bool) $this->get_prop( 'on_sale', $context );
		}

		/**
		 * Return state of back_in_stock flag
		 * Important: this flag is used for email campaigns, and doesn't necessarily represent
		 * current back_in_stock status for the product
		 * Plugins checks every day to find back_in_stock products, and to schedule email sending
		 *
		 * @param string $context Context.
		 *
		 * @return bool Whether product was on back in stock during last check that plugin performed
		 */
		public function is_back_in_stock( $context = 'view' ) {
			return (bool) $this->get_prop( 'back_in_stock', $context );
		}

		/**
		 * Return state of on_sale flag
		 * Important: this flag is used for email campaigns, and doesn't necessarily represent
		 * current price_change status for the product
		 * Plugins checks every day to find price_change products, and to schedule email sending
		 *
		 * @param string $context Context.
		 *
		 * @return bool Whether product was on sale during last check that plugin performed
		 */
		public function is_price_change( $context = 'view' ) {
			return (bool) $this->get_prop( 'price_change', $context );
		}

		/**
		 * Return state of low_stock flag
		 * Important: this flag is used for email campaigns, and doesn't necessarily represent
		 * current low_stock status for the product
		 * Plugins checks every day to find low_stock products, and to schedule email sending
		 *
		 * @param string $context Context.
		 *
		 * @return bool Whether product was on sale during last check that plugin performed
		 */
		public function is_low_stock( $context = 'view' ) {
			return (bool) $this->get_prop( 'low_stock', $context );
		}

		/**
		 * Get product availability class
		 *
		 * @param string $context Context of the operation.
		 * @return string Availability class.
		 */
		public function get_stock_status( $context = 'view' ) {
			$product = $this->get_product( $context );

			if ( ! $product ) {
				return false;
			}

			$availability = $product->get_availability();

			return $availability['class'] ?? false;
		}


		/**
		 * Get the total amount (COUNT) of ratings, or just the count for one rating e.g. number of 5-star ratings.
		 *
		 * @return int
		 */
		public function get_rating_count() {
			$product = $this->get_product();

			if ( $product && $product->get_parent_id() > 0 ) {
				$product = wc_get_product( $product->get_parent_id() );
			}

			if ( ! $product ) {
				return false;
			}

			return $product->get_rating_count();
		}

		/**
		 * Get average rating.
		 *
		 * @return float
		 */
		public function get_average_rating() {
			$product = $this->get_product();

			if ( $product && $product->get_parent_id() > 0 ) {
				$product = wc_get_product( $product->get_parent_id() );
			}

			if ( ! $product ) {
				return false;
			}

			return $product->get_average_rating();
		}

		/**
		 * Checks whether product is purchasable or not
		 *
		 * @param string $context Context of the operation.
		 * @return bool Whether product is purchasable or not
		 */
		public function is_purchasable( $context = 'view' ) {
			$product = $this->get_product( $context );

			if ( ! $product ) {
				return false;
			}

			return $product->is_purchasable();
		}

		/**
		 * Get cart item.
		 *
		 * @param  bool $is_permalink get cart item for permalink in wishlist table .
		 * @return array|false
		 */
		public function get_cart_item( $is_permalink = false ) {
			$product = $this->get_product();
			$meta    = $this->get_product_meta( 'view' );
			if ( ! $product ) {
				return false;
			}
			$default      = array(
				'product_id'   => $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id(),
				'variation_id' => $product->is_type( 'variation' ) ? $product->get_id() : 0,
				'variation'    => $is_permalink ? ( isset( $meta['attributes'] ) && ! empty( $meta['attributes'] ) ? $meta['attributes'] : '' ) : '',
				'quantity'     => $this->get_quantity(),
			);
			$meta         = is_array( $meta ) && ! empty( $meta ) ? array_merge( $default, $meta ) : $default;
			$meta['data'] = $product;
			return $meta;
		}

		/* === SETTERS === */

		/**
		 * Set wishlist ID for current item
		 *
		 * @param int $wishlist_id Wishlist ID.
		 */
		public function set_wishlist_id( $wishlist_id ) {
			$this->set_prop( 'wishlist_id', $wishlist_id );

			$wishlist = wlfmc_get_wishlist( $wishlist_id );
			if ( $wishlist && $this->get_user_id() !== $wishlist->get_user_id() ) {
				$this->set_user_id( $wishlist->get_user_id() );
			}

			if ( $wishlist && $this->get_customer_id() !== $wishlist->get_customer_id() ) {
				$this->set_customer_id( $wishlist->get_customer_id() );
			}
		}

		/**
		 * Set product ID for current item
		 *
		 * @param int $product_id Product ID.
		 */
		public function set_product_id( $product_id ) {
			$product_id = wlfmc_object_id( $product_id, 'product', true, 'default' );

			if ( ! empty( $this->product ) ) {
				$this->product = null;
			}

			$this->set_prop( 'product_id', $product_id );
		}

		/**
		 * Set quantity for current item
		 *
		 * @param int $quantity Quantity.
		 */
		public function set_quantity( $quantity ) {
			$this->set_prop( 'quantity', $quantity );
		}

		/**
		 * Set user ID for current item
		 *
		 * @param int $user_id User ID.
		 */
		public function set_user_id( $user_id ) {
			$this->set_prop( 'user_id', $user_id );
		}

		/**
		 * Set customer ID for current item
		 *
		 * @param int $customer_id customer ID.
		 */
		public function set_customer_id( $customer_id ) {
			$this->set_prop( 'customer_id', $customer_id );
		}

		/**
		 * Set date added for current item
		 *
		 * @param int $date_added Date added.
		 */
		public function set_date_added( $date_added ) {
			$this->set_date_prop( 'date_added', $date_added );
		}

		/**
		 * Set position in wishlist for current item
		 *
		 * @param int $position Position.
		 */
		public function set_position( $position ) {
			$this->set_prop( 'position', (int) $position );
		}

		/**
		 * Set original price
		 *
		 * @param double $original_price Price.
		 */
		public function set_original_price( $original_price ) {
			$this->set_prop( 'original_price', $original_price );
		}

		/**
		 * Set original currency
		 *
		 * @param string $original_currency Currency.
		 */
		public function set_original_currency( $original_currency ) {
			$this->set_prop( 'original_currency', $original_currency );
		}

		/**
		 * Set cart item key
		 *
		 * @param string $cart_item_key cart item key.
		 */
		public function set_cart_item_key( $cart_item_key ) {
			$this->set_prop( 'cart_item_key', $cart_item_key );
		}

		/**
		 * Set on sale value
		 *
		 * @param bool $on_sale Whether product was found as on sale.
		 *
		 * @return void
		 */
		public function set_on_sale( $on_sale ) {
			if ( $this->get_object_read() && $on_sale && $this->is_on_sale() !== $on_sale ) {
				do_action( 'wlfmc_item_is_on_sale', $this );
			}

			$this->set_prop( 'on_sale', $on_sale );
		}

		/**
		 * Set back in stock value
		 *
		 * @param bool $back_in_stock Whether product become back in stock.
		 *
		 * @return void
		 */
		public function set_back_in_stock( $back_in_stock ) {
			if ( $this->get_object_read() && $back_in_stock && $this->is_back_in_stock() !== $back_in_stock ) {
				do_action( 'wlfmc_item_is_back_in_stock', $this );
			}

			$this->set_prop( 'back_in_stock', $back_in_stock );
		}

		/**
		 * Set price change value
		 *
		 * @param bool $price_change Whether price of the product changed.
		 *
		 * @return void
		 */
		public function set_price_change( $price_change ) {
			if ( $this->get_object_read() && $price_change && $this->is_price_change() !== $price_change ) {
				do_action( 'wlfmc_item_is_price_change', $this );
			}

			$this->set_prop( 'price_change', $price_change );
		}
		/**
		 * Set low stock value
		 *
		 * @param bool $low_stock Whether product was low stock.
		 *
		 * @return void
		 */
		public function set_low_stock( $low_stock ) {
			if ( $this->get_object_read() && $low_stock && $this->is_low_stock() !== $low_stock ) {
				do_action( 'wlfmc_item_is_low_stock', $this );
			}

			$this->set_prop( 'low_stock', $low_stock );
		}


		/**
		 * Set meta value
		 *
		 * @param array $product_meta Product meta data.
		 *
		 * @return void
		 */
		public function set_product_meta( $product_meta ) {
			$this->set_prop( 'product_meta', $product_meta ? wp_json_encode( $product_meta ) : null );
		}

		/**
		 * Set post data
		 *
		 * @param array $posted_data $_POST data.
		 *
		 * @return void
		 */
		public function set_posted_data( $posted_data ) {
			$this->set_prop( 'posted_data', $posted_data ? wp_json_encode( $posted_data ) : null );
		}

		/* === ARRAY ACCESS METHODS === */

		/**
		 * OffsetSet for ArrayAccess.
		 *
		 * @param string $offset Offset.
		 * @param mixed  $value Value.
		 */
		#[\ReturnTypeWillChange]
		public function offsetSet( $offset, $value ) {
			$offset = $this->map_legacy_offsets( $offset );

			if ( array_key_exists( $offset, $this->data ) ) {
				$setter = "set_$offset";
				if ( is_callable( array( $this, $setter ) ) ) {
					$this->$setter( $value );
				}
			}
		}

		/**
		 * OffsetUnset for ArrayAccess.
		 *
		 * @param string $offset Offset.
		 */
		#[\ReturnTypeWillChange]
		public function offsetUnset( $offset ) {
			$offset = $this->map_legacy_offsets( $offset );

			if ( array_key_exists( $offset, $this->data ) ) {
				unset( $this->data[ $offset ] );
			}

			if ( array_key_exists( $offset, $this->changes ) ) {
				unset( $this->changes[ $offset ] );
			}
		}

		/**
		 * OffsetExists for ArrayAccess.
		 *
		 * @param string $offset Offset.
		 *
		 * @return bool
		 */
		#[\ReturnTypeWillChange]
		public function offsetExists( $offset ) {
			$offset = $this->map_legacy_offsets( $offset );

			$getter = "get_$offset";
			if ( is_callable( array( $this, $getter ) ) ) {
				return true;
			}

			return false;
		}

		/**
		 * OffsetGet for ArrayAccess.
		 *
		 * @param string $offset Offset.
		 *
		 * @return mixed
		 */
		#[\ReturnTypeWillChange]
		public function offsetGet( $offset ) {
			$offset = $this->map_legacy_offsets( $offset );

			$getter = "get_$offset";
			if ( is_callable( array( $this, $getter ) ) ) {
				return $this->$getter();
			}

			return null;
		}

		/**
		 * Map legacy indexes to new properties, for ArrayAccess
		 *
		 * @param string $offset Offset to search.
		 *
		 * @return string Mapped offset
		 */
		protected function map_legacy_offsets( $offset ) {
			$legacy_offset = $offset;

			if ( 'prod_id' === $offset ) {
				$offset = 'product_id';
			} elseif ( 'dateadded' === $offset ) {
				$offset = 'date_added';
			}

			return apply_filters( 'wlfmc_wishlist_item_map_legacy_offsets', $offset, $legacy_offset );
		}
	}
}
