<?php
/**
 * Wishlist Factory Class
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WLFMC_Wishlist_Factory' ) ) {
	/**
	 * This class is used to create all Wishlist object required by the plugin
	 */
	class WLFMC_Wishlist_Factory {


		/**
		 * Retrieve a specific customer from ID or token
		 *
		 * @param string|int|bool $customer_id Customer id or token.
		 * @param string          $context Context; when on edit context, and no customer matches selection, customer will be created and returned.
		 *
		 * @return WLFMC_Customer|bool Customer object or false on failure
		 */
		public static function get_customer( $customer_id = false, $context = 'view' ) {
			if ( ! $customer_id ) {
				return self::get_current_customer( false, $context );
			}
			try {
				return new WLFMC_Customer( $customer_id );
			} catch ( Exception $e ) {
				wc_caught_exception( $e, __FUNCTION__, func_get_args() );

				return false;
			}
		}

		/**
		 * Delete a customer by id
		 *
		 * @param int $customer_id Customer id.
		 *
		 * @return bool Customer object or false on failure
		 */
		public static function delete_customer( $customer_id ) {
			try {
				$customer = new WLFMC_Customer( $customer_id );
				WC_Data_Store::load( 'wlfmc-customer' )->delete( $customer );
				return true;
			} catch ( Exception $e ) {
				wc_caught_exception( $e, __FUNCTION__, func_get_args() );
				return false;
			}
		}

		/**
		 * Unsubscribe a Customer
		 *
		 * @param WLFMC_Customer $customer customer.
		 *
		 * @return void
		 */
		public static function unsubscribe_customer( $customer ) {
			if ( ! $customer ) {
				return;
			}
			try {
				WC_Data_Store::load( 'wlfmc-customer' )->unsubscribe( $customer );
			} catch ( Exception $e ) {
				return;
			}
		}

		/**
		 * Get user data by customer ,for guest generate data from email or retrieve data from db.
		 *
		 * @param WLFMC_Customer|int $customer Customer or customer_id.
		 *
		 * @return array|false
		 */
		public static function get_customer_data( $customer ) {
			try {
				return WC_Data_Store::load( 'wlfmc-customer' )->get_customer_data( $customer );
			} catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Retrieve current customer
		 *
		 * @param string|int|bool $id Customer or token; false if you want to use current customer or token.
		 * @param string          $context Context; when on edit context, customer will be created, if not exists.
		 *
		 * @return WLFMC_Customer|bool Customer object or false on failure
		 */
		public static function get_current_customer( $id = false, $context = 'read' ) {
			try {
				$current_customer = WC_Data_Store::load( 'wlfmc-customer' )->get_current_customer( $id, $context );

				return apply_filters( 'wlfmc_current_customer', $current_customer, $id, $context );
			} catch ( Exception $e ) {
				wc_caught_exception( $e, __FUNCTION__, func_get_args() );
				return false;
			}
		}

		/**
		 * Query database to search
		 *
		 * @param array $args Array of parameters used for the query:<br/>
		 * [<br/>
		 *   'customer_id'          // Customer id<br/>
		 *   'user_id'              // User id<br/>
		 *   'session_id'           // Session id<br/>
		 *   'email'                // email exact match<br/>
		 *   'phone'                // phone exact match<br/>
		 *   'token'                // token exact match<br/>
		 *   'email_verified'       // email verified<br/>
		 *   'phone_verified'       // phone verified<br/>
		 *   'user_search'          // String to search within user fields
		 *   'orderby'              // Any of the table columns<br/>
		 *   'order'                // ASC, DESC<br/>
		 *   'limit'                // Limit of items to retrieve<br/>
		 *   'offset'               // Offset of items to retrieve<br/>
		 *   'has_items'            // Whether to show customers with empty wishlists<br/>
		 * ].
		 *
		 * @return WLFMC_Customer[]|bool Array of matched customers.
		 */
		public static function search_customer( $args = array() ) {
			try {
				$results = WC_Data_Store::load( 'wlfmc-customer' )->query( $args );

				return apply_filters( 'wlfmc_customer_query', $results, $args );
			} catch ( Exception $e ) {
				wc_caught_exception( $e, __FUNCTION__, func_get_args() );
				return false;
			}
		}

		/**
		 * Generate a new manual customer for third party use
		 *
		 * @return WLFMC_Customer|bool Brand-new customer, or false on failure
		 */
		public static function create_manual_customer() {

			try {
				require_once ABSPATH . 'wp-includes/class-phpass.php';
				$hasher     = new PasswordHash( 8, false );
				$session_id = md5( $hasher->get_random_bytes( 32 ) );
				$customer   = new WLFMC_Customer();
				$customer->set_session_id( $session_id );
				$customer->set_expiration( time() + wlfmc_get_cookie_expiration() );
				$customer->save();
				/**
				 * Let developers perform processing when customer is created.
				 */
				do_action( 'wlfmc_generated_customer', $customer );
			} catch ( Exception $e ) {
				return false;
			}

			return $customer;
		}


		/**
		 * Retrieve a specific wishlist from ID or token
		 *
		 * @param string|int|bool $wishlist_id Wishlist id or token or false, when you want to retrieve default.
		 * @param string          $context Context; when on edit context, and no wishlist matches selection, default wishlist will be created and returned.
		 *
		 * @return WLFMC_Wishlist|bool Wishlist object or false on failure
		 */
		public static function get_wishlist( $wishlist_id = false, $context = 'view' ) {
			if ( ! $wishlist_id ) {
				return self::get_default_wishlist( false, $context );
			}

			try {
				return new WLFMC_Wishlist( $wishlist_id );
			} catch ( Exception $e ) {
				wc_caught_exception( $e, __FUNCTION__, func_get_args() );

				return false;
			}
		}

		/**
		 * Query database to search for wishlists that matches specific parameters
		 *
		 * @param mixed $args Array of valid arguments<br/>
		 *              [<br/>
		 *              'id'                  // Wishlist id to search, if any<br/>
		 *              'customer_id'         // customer Id<br/>
		 *              'user_id'             // User owner<br/>
		 *              'wishlist_slug'       // Slug of the wishlist to search<br/>
		 *              'wishlist_name'       // Name of the wishlist to search<br/>
		 *              'wishlist_token'      // Token of the wishlist to search<br/>
		 *              'wishlist_visibility' // Wishlist visibility: all, visible, public, private<br/>
		 *              'user_search'         // String to match against first name / last name or email of the wishlist owner<br/>
		 *              'is_default'          // Whether wishlist should be default or not<br/>
		 *              'orderby'             // Column used to sort final result (could be any wishlist lists column)<br/>
		 *              'order'               // Sorting order<br/>
		 *              'limit'               // Pagination param: maximum number of elements in the set. 0 to retrieve all elements<br/>
		 *              'offset'              // Pagination param: offset for the current set. 0 to start from the first item<br/>
		 *              'show_empty'          // Whether to show empty lists' os not<br/>
		 *              ].
		 *
		 * @return WLFMC_Wishlist[]|bool A list of matching wishlists or false on failure
		 */
		public static function get_wishlists( $args = array() ) {
			$args = apply_filters( 'wlfmc_wishlist_query_args', $args );

			try {
				$results = WC_Data_Store::load( 'wlfmc-wishlist' )->query( $args );

				return apply_filters( 'wlfmc_wishlist_query', $results, $args );
			} catch ( Exception $e ) {
				wc_caught_exception( $e, __FUNCTION__, func_get_args() );

				return false;
			}
		}

		/**
		 * Query database to count wishlists that matches specific parameters
		 *
		 * @param array $args Same parameters allowed for {@see get_wishlists}.
		 *
		 * @return int Count
		 */
		public static function get_wishlists_count( $args = array() ) {
			$args = apply_filters( 'wlfmc_wishlists_count_query_args', $args );

			try {
				$result = WC_Data_Store::load( 'wlfmc-wishlist' )->count( $args );

				return apply_filters( 'wlfmc_wishlist_count_query', $result, $args );
			} catch ( Exception $e ) {
				wc_caught_exception( $e, __FUNCTION__, func_get_args() );
				return 0;
			}
		}

		/**
		 * Search user ids whose wishlists match passed parameters
		 * NOTE: this will only retrieve wishlists for a logged-in user, while guests wishlist will be ignored
		 *
		 * @param array $args Array of valid arguments<br/>
		 * [<br/>
		 *     'search' // String to match against first name / last name / user login or user email of wishlist owner<br/>
		 *     'limit'  // Pagination param: number of items to show in one page. 0 to show all items<br/>
		 *     'offset' // Pagination param: offset for the current set. 0 to start from the first item<br/>
		 * ].
		 *
		 * @return int[]|bool Array of user ids, or false on failure
		 */
		public static function get_wishlist_users( $args = array() ) {
			$args = apply_filters( 'wlfmc_wishlist_users_query_args', $args );

			try {
				$results = WC_Data_Store::load( 'wlfmc-wishlist' )->search_users( $args );

				return apply_filters( 'wlfmc_wishlist_user_query', $results, $args );
			} catch ( Exception $e ) {
				wc_caught_exception( $e, __FUNCTION__, func_get_args() );
				return false;
			}
		}

		/**
		 * Retrieve current wishlist, basing on query string parameters, user or session
		 *
		 * @param array $args Array of arguments<br/>
		 *              [<br/>
		 *              'action_params' // query string parameters
		 *              'user_id'       // user we need to retrieve wishlist for
		 *              'wishlist_id'   // id of the wishlist we need to retrieve
		 *              ].
		 *
		 * @return WLFMC_Wishlist|bool
		 */
		public static function get_current_wishlist( $args = array() ) {
			$defaults = array(
				'action_params' => get_query_var( WLFMC()->wishlist_param, false ),
				'user_id'       => isset( $_GET['user_id'] ) ? intval( $_GET['user_id'] ) : false, // phpcs:ignore WordPress.Security.NonceVerification
				'wishlist_id'   => false,
			);
			/**
			 * Variables
			 *
			 * @var $action_params
			 * @var $user_id
			 * @var $wishlist_id
			 */
			$args = wp_parse_args( $args, $defaults );
			extract( $args );// phpcs:ignore WordPress.PHP.DontExtract

			// retrieve options from query string.
			$action_params = explode( '/', apply_filters( 'wlfmc_current_wishlist_view_params', $action_params ) );

			$action = ( isset( $action_params[0] ) ) ? $action_params[0] : 'view';
			$value  = ( isset( $action_params[1] ) ) ? $action_params[1] : '';

			if ( ! empty( $wishlist_id ) ) {
				return self::get_wishlist( $wishlist_id );
			}

			if ( ! empty( $user_id ) ) {
				return self::get_default_wishlist( $user_id );
			}

			if (
				empty( $action ) ||
				! in_array( $action, WLFMC()->get_available_views(), true ) ||
				in_array( $action, array( 'view', 'user' ), true )
			) {
				switch ( $action ) {
					case 'user':
						$user_id = $value;
						$user_id = ( ! $user_id ) ? get_query_var( $user_id, false ) : $user_id;

						return self::get_default_wishlist( intval( $user_id ) );
					case 'view':
					default:
						return self::get_wishlist( sanitize_text_field( $value ) );
				}
			}

			return false;
		}

		/**
		 * Retrieve default wishlist for current user (or current session)
		 *
		 * @param string|int|bool $id User or session id; false if you want to use current user or session.
		 * @param string          $context Context; when on edit context, wishlist will be created, if not exists.
		 *
		 * @return WLFMC_Wishlist|bool Wishlist object or false on failure
		 */
		public static function get_default_wishlist( $id = false, $context = 'read' ) {
			try {
				$default_wishlist = WC_Data_Store::load( 'wlfmc-wishlist' )->get_default_wishlist( $id, $context );

				return apply_filters( 'wlfmc_default_wishlist', $default_wishlist, $id, $context );
			} catch ( Exception $e ) {
				wc_caught_exception( $e, __FUNCTION__, func_get_args() );
				return false;
			}
		}

		/**
		 * Retrieve wishlist by slug for current user (or current session)
		 *
		 * @param string $slug Customer or session id; false if you want to use current customer or session.
		 * @param string $context Context; when on edit context, wishlist will be created, if not exists.
		 *
		 * @since 1.5.0
		 * @return WLFMC_Wishlist|bool Wishlist object or false on failure
		 */
		public static function get_wishlist_by_slug( $slug, $context = 'read' ) {

			if ( ! $slug ) {
				return false;
			}

			try {
				$wishlist = WC_Data_Store::load( 'wlfmc-wishlist' )->get_wishlist_by_slug( $slug, $context );

				return apply_filters( 'wlfmc_' . $slug . '_wishlist', $wishlist, $slug, $context );
			} catch ( Exception $e ) {
				wc_caught_exception( $e, __FUNCTION__, func_get_args() );
				return false;
			}
		}

		/**
		 * Retrieve wishlist by slug and user_id or session_id
		 *
		 * @param string          $slug Customer or session id; false if you want to use current customer or session.
		 * @param string|int|bool $id Pass this param when you want to retrieve a wishlist for a specific user/session.
		 * @param string          $context Context; when on edit context, wishlist will be created, if not exists.
		 *
		 * @since 1.6.1
		 * @return WLFMC_Wishlist|bool Wishlist object or false on failure
		 */
		public static function get_list_by_slug( $slug, $id, $context = 'read' ) {

			if ( ! $slug ) {
				return false;
			}

			try {
				$wishlist = WC_Data_Store::load( 'wlfmc-wishlist' )->get_list_by_slug( $slug, $id, $context );

				return apply_filters( 'wlfmc_' . $slug . '_wishlist', $wishlist, $id, $context );
			} catch ( Exception $e ) {
				wc_caught_exception( $e, __FUNCTION__, func_get_args() );
				return false;
			}
		}

		/**
		 * Retrieve default wishlist for current user (or current session)
		 *
		 * @param string|int|bool $id Customer or session id; false if you want to use current customer or session.
		 *
		 * @return WLFMC_Wishlist|bool Wishlist object or false on failure
		 */
		public static function generate_default_wishlist( $id = false ) {
			return self::get_default_wishlist( $id );
		}

		/**
		 * Generate new token for a wishlist
		 *
		 * @return string|bool Brand-new token, or false on failure
		 */
		public static function generate_wishlist_token() {
			try {
				return WC_Data_Store::load( 'wlfmc-wishlist' )->generate_token();
			} catch ( Exception $e ) {
				wc_caught_exception( $e, __FUNCTION__, func_get_args() );
				return false;
			}
		}

		/**
		 * Retrieve a specific wishlist item from ID
		 *
		 * @param int|WLFMC_Wishlist_Item|stdClass $item_id Item identifier, or item itself.
		 *
		 * @return WLFMC_Wishlist_Item|bool Wishlist item, or false on failure
		 */
		public static function get_wishlist_item( $item_id = 0 ) {
			if ( is_numeric( $item_id ) ) {
				$id = $item_id;
			} elseif ( $item_id instanceof WLFMC_Wishlist_Item ) {
				$id = $item_id->get_id();
			} elseif ( is_object( $item_id ) && ! empty( $item_id->ID ) ) {
				$id = $item_id->ID;
			} else {
				$id = false;
			}

			if ( $id ) {
				try {
					return new WLFMC_Wishlist_Item( $id );
				} catch ( Exception $e ) {
					return false;
				}
			}

			return false;
		}

		/**
		 * Retrieve item from a wishlist by product id
		 *
		 * @param int|string $wishlist_id Wishlist id or token.
		 * @param int        $product_id  Product ID.
		 *
		 * @return WLFMC_Wishlist_Item|bool Item, or false when no item found
		 */
		public static function get_wishlist_item_by_product_id( $wishlist_id, $product_id ) {
			$wishlist = self::get_wishlist( $wishlist_id );

			if ( $wishlist ) {
				return $wishlist->get_product( $product_id );
			}

			return false;
		}

		/**
		 * Query database to search for wishlist items that matches specific parameters
		 *
		 * @param array $args Arguments array; it may contain any of the following:<br/>
		 * [<br/>
		 *     'user_id'             // Owner of the wishlist; default to current user logged in (if any), or false for cookie wishlist<br/>
		 *     'product_id'          // Product to search in the wishlist<br/>
		 *     'wishlist_id'         // wishlist_id for a specific wishlist, false for default, or all for any wishlist<br/>
		 *     'wishlist_token'      // wishlist token, or false as default<br/>
		 *     'wishlist_visibility' // all, visible, public, private<br/>
		 *     'is_default' =>       // whether searched wishlist should be default one <br/>
		 *     'id' => false,        // only for table select<br/>
		 *     'limit' => false,     // pagination param; number of items per page. 0 to get all items<br/>
		 *     'offset' => 0         // pagination param; offset for the current set. 0 to start from the first item<br/>
		 * ].
		 *
		 * @return WLFMC_Wishlist_Item[]|bool A list of matching items or false on failure
		 */
		public static function get_wishlist_items( $args = array() ) {
			$args = apply_filters( 'wlfmc_wishlist_items_query_args', $args );

			try {
				$results = WC_Data_Store::load( 'wlfmc-wishlist-item' )->query( $args );

				return apply_filters( 'wlfmc_wishlist_item_query', $results, $args );
			} catch ( Exception $e ) {
				wc_caught_exception( $e, __FUNCTION__, func_get_args() );
				return false;
			}
		}

		/**
		 * Query database to count wishlist items that matches specific parameters
		 *
		 * @param array $args Same parameters allowed for {@see get_wishlist_items}.
		 *
		 * @return int Count
		 */
		public static function get_wishlist_items_count( $args = array() ) {
			$args = apply_filters( 'wlfmc_wishlist_items_count_query_args', $args );

			try {
				$result = WC_Data_Store::load( 'wlfmc-wishlist-item' )->count( $args );

				return apply_filters( 'wlfmc_wishlist_item_count_query', $result, $args );
			} catch ( Exception $e ) {
				wc_caught_exception( $e, __FUNCTION__, func_get_args() );
				return 0;
			}
		}

		/**
		 * Count how many times a specific product was added to wishlist
		 *
		 * @param int    $parent_product_id  Product id.
		 * @param array  $columns An array of custom columns with values. Column keys can be 'on_sale', 'back_in_stock', 'price_change' or 'low_stock'.
		 * @param string $list_type list type. List type can be 'all','lists', 'wishlist', 'waitlist', 'save-for-later', 'on-sale', 'back-in-stock', 'price-change' or 'low-stock'.
		 *
		 * @return int Count of times product was added to wishlist
		 */
		public static function get_times_parent_added_count( $parent_product_id, $columns = array(), $list_type = 'all' ) {
			try {
				$result = WC_Data_Store::load( 'wlfmc-wishlist-item' )->count_times_parent_added( $parent_product_id, false, $columns, $list_type );
				return (int) apply_filters( 'wlfmc_wishlist_times_parent_added_count_query', $result, $parent_product_id, $columns, $list_type );
			} catch ( Exception $e ) {
				wc_caught_exception( $e, __FUNCTION__, func_get_args() );
				return 0;
			}
		}

		/**
		 * Count how many times a specific product was added to wishlist
		 *
		 * @param int   $product_id  Product id.
		 * @param array $columns An array of custom columns with values. Column keys can be 'on_sale', 'back_in_stock', 'price_change' or 'low_stock'.
		 *
		 * @return int Count of times product was added to wishlist
		 */
		public static function get_times_added_count( $product_id, $columns = array() ) {
			try {
				$result = WC_Data_Store::load( 'wlfmc-wishlist-item' )->count_times_added( $product_id, false, $columns );

				return (int) apply_filters( 'wlfmc_wishlist_times_added_count_query', $result, $product_id, $columns );
			} catch ( Exception $e ) {
				wc_caught_exception( $e, __FUNCTION__, func_get_args() );
				return 0;
			}
		}

		/**
		 * Count how many times a specific product was added to wishlist by the current user
		 *
		 * @param int $product_id  Product id.
		 *
		 * @return int Count of times product was added to wishlist
		 */
		public static function get_times_current_user_added_count( $product_id ) {
			try {
				$result = WC_Data_Store::load( 'wlfmc-wishlist-item' )->count_times_added( $product_id, 'current' );

				return (int) apply_filters( 'wlfmc_wishlist_times_current_user_added_count_query', $result, $product_id );
			} catch ( Exception $e ) {
				wc_caught_exception( $e, __FUNCTION__, func_get_args() );
				return 0;
			}
		}
	}
}
