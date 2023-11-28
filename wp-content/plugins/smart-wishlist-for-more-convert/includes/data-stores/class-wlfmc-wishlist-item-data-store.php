<?php
/**
 * Wishlist Data Store
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WLFMC_Wishlist_Item_Data_Store' ) ) {
	/**
	 * This class implements CRUD methods for wishlist items
	 */
	class WLFMC_Wishlist_Item_Data_Store {

		/**
		 * Create a new wishlist item in the database.
		 *
		 * @param WLFMC_Wishlist_Item $item Wishlist item object.
		 *
		 * @throws Exception Exception.
		 */
		public function create( &$item ) {
			global $wpdb;

			$product_id  = $item->get_original_product_id();
			$wishlist_id = $item->get_wishlist_id();

			if ( ! $product_id || ! $wishlist_id ) {
				return;
			}

			$item_id = WLFMC_Wishlist_Factory::get_wishlist_item_by_product_id( $wishlist_id, $product_id );
			// In save for later, we can add a product several times, so there is no need to update the product and a new product must be added.
			if ( $item_id instanceof WLFMC_Wishlist_Item && ! in_array( $item_id->get_wishlist_slug(), wlfmc_allow_duplicate_products_slugs(), true ) ) {
				$item->set_id( $item_id->get_id() );
				$item->set_original_price( $item->get_original_price( 'edit' ) );
				$this->update( $item );
				return;
			}

			$columns = array(
				'prod_id'           => '%d',
				'parent_id'         => '%d',
				'quantity'          => '%d',
				'wishlist_id'       => '%d',
				'position'          => '%d',
				'original_price'    => '%f',
				'original_currency' => '%s',
				'on_sale'           => '%d',
				'back_in_stock'     => '%d',
				'price_change'      => '%d',
				'low_stock'         => '%d',
				'product_meta'      => '%s',
				'posted_data'       => '%s',
				'cart_item_key'     => '%s',
				'customer_id'       => '%d',
			);
			$values  = array(
				apply_filters( 'wlfmc_adding_to_wishlist_product_id', $product_id ),
				apply_filters( 'wlfmc_adding_to_wishlist_parent_product_id', $item->get_parent_id() ),
				apply_filters( 'wlfmc_adding_to_wishlist_quantity', $item->get_quantity() ),
				apply_filters( 'wlfmc_adding_to_wishlist_wishlist_id', $wishlist_id ),
				apply_filters( 'wlfmc_adding_to_wishlist_position', $item->get_position() ),
				apply_filters( 'wlfmc_adding_to_wishlist_original_price', ( ! $item_id instanceof WLFMC_Wishlist_Item || ! in_array( $item_id->get_wishlist_slug(), wlfmc_allow_duplicate_products_slugs(), true ) ) ? $item->calculate_product_price() : $item->get_original_price( 'edit' ) ),
				apply_filters( 'wlfmc_adding_to_wishlist_original_currency', $item->get_original_currency() ),
				apply_filters( 'wlfmc_adding_to_wishlist_on_sale', $item->is_on_sale() ),
				apply_filters( 'wlfmc_adding_to_wishlist_back_in_stock', $item->is_back_in_stock() ),
				apply_filters( 'wlfmc_adding_to_wishlist_price_change', $item->is_price_change() ),
				apply_filters( 'wlfmc_adding_to_wishlist_low_stock', $item->is_low_stock() ),
				apply_filters( 'wlfmc_adding_to_wishlist_product_meta', $item->get_product_meta( 'edit' ), $product_id ),
				apply_filters( 'wlfmc_adding_to_wishlist_posted_data', $item->get_posted_data( 'edit' ), $product_id ),
				apply_filters( 'wlfmc_adding_to_wishlist_cart_item_key', $item->get_cart_item_key() ),
				apply_filters( 'wlfmc_adding_to_wishlist_customer_id', $item->get_customer_id() ),
			);
			$user_id = $item->get_user_id();
			if ( $user_id ) {
				$columns['user_id'] = '%d';
				$values[]           = apply_filters( 'wlfmc_adding_to_wishlist_user_id', $user_id );
			}
			$date_added = $item->get_date_added( 'edit' );
			if ( $date_added ) {
				$columns['dateadded'] = 'FROM_UNIXTIME( %d )';
				$values[]             = apply_filters( 'wlfmc_adding_to_wishlist_date_added', $date_added->getTimestamp() );
			}

			$query_columns = implode( ', ', array_map( 'esc_sql', array_keys( $columns ) ) );
			$query_values  = implode( ', ', array_values( $columns ) );
			$query         = "INSERT INTO $wpdb->wlfmc_wishlist_items ( $query_columns ) VALUES ( $query_values ) ";

			$res = $wpdb->query( $wpdb->prepare( $query, $values ) );// phpcs:ignore WordPress.DB

			if ( $res ) {
				$item->set_id( $wpdb->insert_id );
				$item->apply_changes();
				$this->clear_cache( $item );

				do_action( 'wlfmc_new_wishlist_item', $item->get_id(), $item, $item->get_wishlist_id() );
			}
		}

		/**
		 * Read/populate data properties specific to this order item.
		 *
		 * @param WLFMC_Wishlist_Item $item Wishlist item object..
		 *
		 * @throws Exception When wishlist item is not found.
		 */
		public function read( &$item ) {
			global $wpdb;

			$item->set_defaults();

			// Get from cache if available.
			$data = wp_cache_get( 'item-' . $item->get_id(), 'wlfmc-wishlist-items' );

			if ( false === $data ) {
				$data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->wlfmc_wishlist_items WHERE ID = %d LIMIT 1;", $item->get_id() ) );// phpcs:ignore WordPress.DB
				wp_cache_set( 'item-' . $item->get_id(), $data, 'wlfmc-wishlist-items' );
			}

			if ( ! $data ) {
				throw new Exception( esc_html__( 'Invalid wishlist item.', 'wc-wlfmc-wishlist' ) );
			}
			$item->set_props(
				array(
					'wishlist_id'       => $data->wishlist_id,
					'product_id'        => $data->prod_id,
					'parent_product_id' => $data->parent_id,
					'customer_id'       => $data->customer_id,
					'user_id'           => $data->user_id,
					'quantity'          => $data->quantity,
					'date_added'        => $data->dateadded,
					'position'          => $data->position,
					'original_price'    => $data->original_price,
					'original_currency' => $data->original_currency,
					'on_sale'           => $data->on_sale,
					'back_in_stock'     => $data->back_in_stock ?? 0,
					'price_change'      => $data->price_change ?? 0,
					'low_stock'         => $data->low_stock ?? 0,
					'cart_item_key'     => $data->cart_item_key,
					'product_meta'      => isset( $data->product_meta ) && '' !== $data->product_meta ? json_decode( $data->product_meta, true ) : null,
					'posted_data'       => isset( $data->posted_data ) && '' !== $data->posted_data ? json_decode( $data->posted_data, true ) : null,
				)
			);
			$item->set_object_read( true );
		}

		/**
		 * Update a wishlist item in the database.
		 *
		 * @param WLFMC_Wishlist_Item $item Wishlist item object.
		 */
		public function update( &$item ) {

			if ( ! $item->get_id() ) {
				return;
			}

			$changes = $item->get_changes();

			if ( array_intersect(
				array(
					'quantity',
					'wishlist_id',
					'product_id',
					'parent_product_id',
					'user_id',
					'customer_id',
					'position',
					'on_sale',
					'back_in_stock',
					'price_change',
					'low_stock',
					'date_added',
					'product_meta',
					'posted_data',
					'cart_item_key',
				),
				array_keys( $changes )
			) ) {
				$columns = array(
					'quantity'       => '%d',
					'wishlist_id'    => '%d',
					'prod_id'        => '%d',
					'parent_id'      => '%d',
					'position'       => '%d',
					'on_sale'        => '%d',
					'back_in_stock'  => '%d',
					'price_change'   => '%d',
					'low_stock'      => '%d',
					'dateadded'      => 'FROM_UNIXTIME( %d )',
					'product_meta'   => '%s',
					'posted_data'    => '%s',
					'cart_item_key'  => '%s',
					'original_price' => '%f',
					'customer_id'    => '%d',
					'user_id'        => $item->get_user_id() ? '%d' : 'NULL',
				);
				$values  = array(
					$item->get_quantity(),
					$item->get_wishlist_id(),
					$item->get_original_product_id(),
					$item->get_parent_id(),
					$item->get_position(),
					$item->is_on_sale(),
					$item->is_back_in_stock(),
					$item->is_price_change(),
					$item->is_low_stock(),
					$item->get_date_added( 'edit' ) ? $item->get_date_added( 'edit' )->getTimestamp() : time(),
					$item->get_product_meta( 'edit' ),
					$item->get_posted_data( 'edit' ),
					$item->get_cart_item_key(),
					$item->get_original_price( 'edit' ),
					$item->get_customer_id(),
				);

				$user_id = $item->get_user_id();

				if ( $user_id ) {
					$values[] = $user_id;
				}

				$this->update_raw( $columns, $values, array( 'ID' => '%d' ), array( $item->get_id() ) );
			}

			$item->apply_changes();
			$this->clear_cache( $item );

			do_action( 'wlfmc_update_wishlist_item', $item->get_id(), $item, $item->get_wishlist_id() );
		}

		/**
		 * Remove a wishlist item from the database.
		 *
		 * @param WLFMC_Wishlist_Item $item Wishlist item object.
		 */
		public function delete( &$item ) {
			global $wpdb;

			$id = $item->get_id();

			if ( ! $id ) {
				return;
			}

			do_action( 'wlfmc_before_delete_wishlist_item', $item->get_id() );

			$wpdb->delete( $wpdb->wlfmc_wishlist_items, array( 'ID' => $item->get_id() ) );// phpcs:ignore WordPress.DB

			do_action( 'wlfmc_delete_wishlist_item', $item->get_id() );

			$item->set_id( 0 );
			$this->clear_cache( $item );
		}

		/**
		 * Retrieves wishlist items that match a set of conditions
		 *
		 * @param mixed $args Arguments array; it may contain any of the following:<br/>
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
		 * @return WLFMC_Wishlist_Item[]
		 */
		public function query( $args = array() ) {
			global $wpdb;

			$default = array(
				'user_id'             => ( is_user_logged_in() ) ? get_current_user_id() : false,
				'session_id'          => ( ! is_user_logged_in() ) ? WLFMC_Session()->maybe_get_session_id() : false,
				'customer_id'         => false,
				'product_id'          => false,
				'wishlist_id'         => false,
				// wishlist_id for a specific wishlist, false for default, or all for any wishlist.
				'wishlist_token'      => false,
				'wishlist_visibility' => apply_filters( 'wlfmc_wishlist_visibility_string_value', 'all' ),
				// all , visible , public , private.
				'is_default'          => false,
				'on_sale'             => false,
				'back_in_stock'       => false,
				'price_change'        => false,
				'low_stock'           => false,
				'id'                  => false,
				// only for table select.
				'limit'               => false,
				'offset'              => 0,
				'orderby'             => '',
				'order'               => 'DESC',
			);

			// if there is no current wishlist, and user was asking for current one, short-circuit query, as pointless.
			if ( ! is_user_logged_in() && ! WLFMC_Session()->has_session() && ! isset( $args['user_id'] ) && ! isset( $args['session_id'] ) ) {
				return array();
			}

			$args = wp_parse_args( $args, $default );
			extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract

			$sql = "SELECT SQL_CALC_FOUND_ROWS i.*
                    FROM $wpdb->wlfmc_wishlist_items AS i
                    LEFT JOIN $wpdb->wlfmc_wishlists AS l ON l.`ID` = i.`wishlist_id`
                    INNER JOIN $wpdb->posts AS p ON p.ID = i.prod_id
                    WHERE 1 AND p.post_type IN ( %s, %s ) AND p.post_status = %s";

			// remove hidden products from result.
			$hidden_products = wlfmc_get_hidden_products();

			if ( is_array( $hidden_products ) && ! empty( $hidden_products ) && apply_filters( 'wlfmc_remove_hidden_products_via_query', true ) ) {
				$hidden_products_sql = implode( ', ', array_filter( $hidden_products, 'esc_sql' ) );
				if ( ! empty( $hidden_products_sql ) ) {
					$sql .= " AND p.ID NOT IN ( $hidden_products_sql )";
				}
			}

			$sql_args = array(
				'product',
				'product_variation',
				'publish',
			);

			if ( ! empty( $user_id ) ) {
				$sql       .= ' AND i.`user_id` = %d';
				$sql_args[] = $user_id;
			}

			if ( ! empty( $session_id ) ) {
				$sql       .= ' AND l.`session_id` = %s AND l.`expiration` > NOW()';
				$sql_args[] = $session_id;
			}

			if ( ! empty( $customer_id ) ) {
				$sql       .= ' AND i.`customer_id` = %d';
				$sql_args[] = $customer_id;
			}

			if ( ! empty( $product_id ) ) {
				$product_id = wlfmc_object_id( $product_id, 'product', true, 'default' );

				$sql       .= ' AND i.`prod_id` = %d';
				$sql_args[] = $product_id;
			}

			if ( ! empty( $wishlist_id ) && 'all' !== $wishlist_id ) {
				$sql       .= ' AND i.`wishlist_id` = %d';
				$sql_args[] = $wishlist_id;
			} elseif ( ( empty( $wishlist_id ) ) && empty( $wishlist_token ) && empty( $is_default ) ) {
				$sql .= ' AND i.`wishlist_id` IS NULL';
			}

			if ( ! empty( $wishlist_token ) ) {
				$sql       .= ' AND l.`wishlist_token` = %s';
				$sql_args[] = $wishlist_token;
			}

			if ( ! empty( $wishlist_visibility ) && 'all' !== $wishlist_visibility ) {
				switch ( $wishlist_visibility ) {
					case 'visible':
						$sql       .= ' AND ( l.`wishlist_privacy` = %d OR l.`wishlist_privacy` = %d )';
						$sql_args[] = 0;
						$sql_args[] = 1;
						break;
					case 'private':
						$sql       .= ' AND l.`wishlist_privacy` = %d';
						$sql_args[] = 1;
						break;
					case 'public':
					default:
						$sql       .= ' AND l.`wishlist_privacy` = %d';
						$sql_args[] = 0;
						break;
				}
			}

			if ( ! empty( $is_default ) ) {
				WLFMC_Wishlist_Factory::generate_default_wishlist();

				$sql       .= ' AND l.`is_default` = %d';
				$sql_args[] = $is_default;
			}

			if ( isset( $on_sale ) && false !== $on_sale ) {
				$sql       .= ' AND i.`on_sale` = %d';
				$sql_args[] = $on_sale;
			}
			if ( isset( $back_in_stock ) && false !== $back_in_stock ) {
				$sql       .= ' AND i.`back_in_stock` = %d';
				$sql_args[] = $back_in_stock;
			}
			if ( isset( $price_change ) && false !== $price_change ) {
				$sql       .= ' AND i.`price_change` = %d';
				$sql_args[] = $price_change;
			}
			if ( isset( $low_stock ) && false !== $low_stock ) {
				$sql       .= ' AND i.`low_stock` = %d';
				$sql_args[] = $low_stock;
			}

			if ( ! empty( $id ) ) {
				$sql       .= ' AND `i.ID` = %d';
				$sql_args[] = $id;
			}

			$sql .= ' GROUP BY i.prod_id, l.ID';

			if ( ! empty( $orderby ) ) {
				$order = ! empty( $order ) ? $order : 'DESC';
				$sql  .= ' ORDER BY i.' . esc_sql( $orderby ) . ' ' . esc_sql( $order ) . ', i.position ASC';
			} else {
				$sql .= ' ORDER BY i.position ASC, i.ID DESC';
			}

			if ( ! empty( $limit ) && isset( $offset ) ) {
				$sql       .= ' LIMIT %d, %d';
				$sql_args[] = $offset;
				$sql_args[] = $limit;
			}

			$items = $wpdb->get_results( $wpdb->prepare( $sql, $sql_args ) ); // phpcs:ignore WordPress.DB

			/**
			 * This filter was added to allow developer remove hidden products using a foreach loop, instead of the query
			 * It is required when the store contains a huge number of hidden products, and the resulting query would fail
			 * to be submitted to DBMS because of its size
			 *
			 * This code requires reasonable amount of products in the wishlist
			 * A great number of products retrieved from the main query could easily degrade performance of the overall system
			 */
			if ( ! empty( $hidden_products ) && ! empty( $items ) && ! apply_filters( 'wlfmc_remove_hidden_products_via_query', true ) ) {
				foreach ( $items as $item_id => $item ) {
					if ( ! in_array( $item->prod_id, $hidden_products, true ) ) {
						continue;
					}

					unset( $items[ $item_id ] );
				}
			}

			if ( ! empty( $items ) ) {
				$items = array_map( array( 'WLFMC_Wishlist_Factory', 'get_wishlist_item' ), $items );
			} else {
				$items = array();
			}

			return apply_filters( 'wlfmc_get_products', $items, $args );
		}

		/**
		 * Counts items that match
		 *
		 * @param array $args Same parameters allowed for {@see query} method.
		 *
		 * @return int Count of items
		 */
		public function count( $args = array() ) {
			return count( $this->query( $args ) );
		}

		/**
		 * Query items table to retrieve distinct products added to wishlist, with count of occurrences
		 *
		 * @param mixed $args Arguments array; it may contain any of the following:<br/>
		 * [<br/>
		 *     'parent_product_id'   // Parent Product to search in the wishlist<br/>
		 *     'product_id'          // Product to search in the wishlist<br/>
		 *     'search' => '',       // search string; will be matched against product name<br/>
		 *     'interval' => '',     // Interval of dates; this should be an associative array, that may contain start_date or end_date<br/>
		 *     'orderby' => 'ID',    // order param; a valid column in the result set<br/>
		 *     'order' => 'desc',    // order param; asc or desc<br/>
		 *     'limit' => false,     // pagination param; number of items per page. 0 to get all items<br/>
		 *     'offset' => 0         // pagination param; offset for the current set. 0 to start from the first item<br/>
		 * ].
		 *
		 * @return array|object|stdClass[]|null Result set
		 */
		public function query_products( $args ) {
			global $wpdb;

			$default = array(
				'parent_product_id' => '',
				'product_id'        => '',
				'search'            => '',
				'interval'          => array(),
				'limit'             => false,
				'offset'            => 0,
				'orderby'           => 'ID',
				'order'             => 'DESC',
			);

			$args = wp_parse_args( $args, $default );
			extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract

			$sql = "SELECT
		            DISTINCT i.prod_id AS id,
		            p.post_title AS post_title,
		            i2.wishlist_count AS wishlist_count
		            FROM $wpdb->wlfmc_wishlist_items AS i
		            INNER JOIN $wpdb->posts AS p ON p.ID = i.prod_id
		            LEFT JOIN (
		                SELECT
		                COUNT( DISTINCT ID ) AS wishlist_count,
                        prod_id
		                FROM $wpdb->wlfmc_wishlist_items
		                GROUP BY prod_id
	                ) AS i2 ON p.ID = i2.prod_id
		            WHERE p.post_status = %s";

			$sql_args = array( 'publish' );

			if ( ! empty( $product_id ) ) {
				$sql       .= ' AND i.prod_id = %d';
				$sql_args[] = $product_id;
			}

			if ( ! empty( $parent_product_id ) ) {
				$sql       .= ' AND i.parent_id = %d';
				$sql_args[] = $parent_product_id;
			}

			if ( ! empty( $search ) ) {
				$sql       .= ' AND p.post_title LIKE %s';
				$sql_args[] = '%' . $search . '%';
			}

			if ( ! empty( $args['interval'] ) && is_array( $args['interval'] ) && ( isset( $args['interval']['start_date'] ) || isset( $args['interval']['end_date'] ) ) ) {
				if ( ! empty( $args['interval']['start_date'] ) ) {
					$sql       .= ' AND i.dateadded >= %s';
					$sql_args[] = $args['interval']['start_date'];
				}

				if ( ! empty( $args['interval']['end_date'] ) ) {
					$sql       .= ' AND i.dateadded <= %s';
					$sql_args[] = $args['interval']['end_date'];
				}
			}

			if ( ! empty( $orderby ) ) {
				$order = ! empty( $order ) ? $order : 'DESC';
				$sql  .= ' ORDER BY ' . esc_sql( $orderby ) . ' ' . esc_sql( $order );
			}

			if ( ! empty( $limit ) && isset( $offset ) ) {
				$sql       .= ' LIMIT %d, %d';
				$sql_args[] = $offset;
				$sql_args[] = $limit;
			}

			return $wpdb->get_results( $wpdb->prepare( $sql, $sql_args ), ARRAY_A ); // phpcs:ignore WordPress.DB
		}

		/**
		 * Counts total number of distinct products added to wishlist.
		 *
		 * @param array $args Same parameters allowed for {@see query_products} method.
		 *
		 * @return int Count of items
		 */
		public function count_products( $args ) {
			return count( $this->query_products( $args ) );
		}

		/**
		 * Counts how many distinct users added a product in wishlist.
		 *
		 * @param int   $product_id Product id.
		 * @param mixed $user user.
		 * @param array $columns An array of custom columns with values. Column keys can be 'on_sale', 'back_in_stock', 'price_change' or 'low_stock'.
		 *
		 * @return int Count of times product was added to wishlist.
		 */
		public function count_times_added( $product_id, $user = false, $columns = array() ) {
			global $wpdb;

			$query_args     = array();
			$user_condition = '';
			$condition      = '';

			if ( $user ) {
				if ( 'current' === $user ) {
					if ( is_user_logged_in() ) {
						$user_condition = ' AND l.`user_id` = %d';
						$query_args[]   = get_current_user_id();
					} elseif ( WLFMC_Session()->has_session() ) {
						$user_condition = ' AND l.`session_id` = %s';
						$query_args[]   = WLFMC_Session()->get_session_id();
					} else {
						return 0;
					}
				} elseif ( is_int( $user ) ) {
					$user_condition = ' AND l.`user_id` = %d';
					$query_args[]   = $user;
				} elseif ( is_string( $user ) ) {
					$user_condition = ' AND l.`session_id` = %s';
					$query_args[]   = $user;
				}
			}
			if ( ! empty( $columns ) ) {
				// Check if the $column is a multidimensional array.
				if ( isset( $columns[0] ) && is_array( $columns[0] ) ) {
					foreach ( $columns as $inner_array ) {
						foreach ( $inner_array as $key => $value ) {
							if ( in_array( $key, array( 'on_sale', 'back_in_stock', 'price_change', 'low_stock' ), true ) ) {
								$condition   .= ' i.`' . $key . '` = %d AND';
								$query_args[] = $value;
							}
						}
					}
				} else {
					foreach ( $columns as $key => $value ) {
						if ( in_array( $key, array( 'on_sale', 'back_in_stock', 'price_change', 'low_stock' ), true ) ) {
							$condition   .= ' i.`' . $key . '` = %d AND';
							$query_args[] = $value;
						}
					}
				}
			}
			$query = "SELECT
       				      COUNT( DISTINCT( v.`u_id` ) ) as count
					  FROM (
					      SELECT
					          ( CASE WHEN l.`user_id` IS NULL THEN l.`session_id` ELSE l.`user_id` END) AS u_id,
					          l.`ID` as wishlist_id
					      FROM $wpdb->wlfmc_wishlists AS l
					      WHERE ( l.`expiration` > NOW() OR l.`expiration` IS NULL ) $user_condition
				      ) as v
				      LEFT JOIN $wpdb->wlfmc_wishlist_items AS i USING( wishlist_id )
					  WHERE $condition i.`prod_id` = %d ";

			$query_args[] = $product_id;

			$res = $wpdb->get_var( $wpdb->prepare( $query, $query_args ) ); // phpcs:ignore WordPress.DB

			return (int) $res;
		}

		/**
		 * Counts how many distinct users added a product in wishlist.
		 *
		 * @param int    $parent_product_id Product id.
		 * @param mixed  $user user.
		 * @param array  $columns An array of custom columns with values. Column keys can be 'on_sale', 'back_in_stock', 'price_change' or 'low_stock'.
		 * @param string $list_type list type. List type can be 'all','lists', 'wishlist', 'waitlist', 'save-for-later', 'on_sale', 'back_in_stock', 'price_change' or 'low_stock'.
		 *
		 * @return int Count of times product was added to wishlist.
		 */
		public function count_times_parent_added( $parent_product_id, $user = false, $columns = array(), $list_type = 'all' ) {
			global $wpdb;

			$query_args          = array();
			$user_condition      = '';
			$list_type_condition = '';
			$condition           = '';

			if ( $user ) {
				if ( 'current' === $user ) {
					if ( is_user_logged_in() ) {
						$user_condition = ' AND l.`user_id` = %d';
						$query_args[]   = get_current_user_id();
					} elseif ( WLFMC_Session()->has_session() ) {
						$user_condition = ' AND l.`session_id` = %s';
						$query_args[]   = WLFMC_Session()->get_session_id();
					} else {
						return 0;
					}
				} elseif ( is_int( $user ) ) {
					$user_condition = ' AND l.`user_id` = %d';
					$query_args[]   = $user;
				} elseif ( is_string( $user ) ) {
					$user_condition = ' AND l.`session_id` = %s';
					$query_args[]   = $user;
				}
			}
			if ( ! empty( $columns ) ) {
				// Check if the $column is a multidimensional array.
				if ( isset( $columns[0] ) && is_array( $columns[0] ) ) {
					foreach ( $columns as $inner_array ) {
						foreach ( $inner_array as $key => $value ) {
							if ( in_array( $key, array( 'on_sale', 'back_in_stock', 'price_change', 'low_stock' ), true ) ) {
								$condition   .= ' i.`' . $key . '` = %d AND';
								$query_args[] = $value;
							}
						}
					}
				} else {
					foreach ( $columns as $key => $value ) {
						if ( in_array( $key, array( 'on_sale', 'back_in_stock', 'price_change', 'low_stock' ), true ) ) {
							$condition   .= ' i.`' . $key . '` = %d AND';
							$query_args[] = $value;
						}
					}
				}
			}

			if ( 'all' !== $list_type ) {
				if ( in_array( $list_type, array( 'on_sale', 'back_in_stock', 'price_change', 'low_stock' ), true ) ) {
					$condition = ' i.`' . $list_type . '` = 1 AND';
					$list_type = 'waitlist';
				}
				$list_type_condition .= " GROUP BY u_id, wishlist_id , list_type having list_type = '$list_type' ";
			}

			$query = "SELECT
       				      COUNT( DISTINCT( v.`u_id` ) ) as count
					  FROM (
					      SELECT
					          ( CASE WHEN l.`user_id` IS NULL THEN l.`session_id` ELSE l.`user_id` END) AS u_id,
					          l.`ID` as wishlist_id,
					           CASE 
	                                WHEN is_default = 1 THEN 'wishlist'
	                                WHEN wishlist_slug = 'waitlist' THEN 'waitlist'
	                                WHEN wishlist_slug = 'save-for-later' THEN 'save-for-later'
	                                ELSE 'lists'
	                            END AS list_type 
					      FROM $wpdb->wlfmc_wishlists AS l
					      WHERE ( l.`expiration` > NOW() OR l.`expiration` IS NULL ) $user_condition $list_type_condition
				      ) as v
				      LEFT JOIN $wpdb->wlfmc_wishlist_items AS i USING( wishlist_id )
					  WHERE $condition i.`parent_id` = %d";

			$query_args[] = $parent_product_id;

			$res = $wpdb->get_var( $wpdb->prepare( $query, $query_args ) ); // phpcs:ignore WordPress.DB

			return (int) $res;
		}

		/**
		 * Raw update method; useful when it is needed to update a bunch of items
		 *
		 * @param array $columns Array of columns to update, in the following format: 'column_id' => 'column_type'.
		 * @param array $column_values Array of values to apply to the query; must have same numbed of elements of columns, and they must respect defined type.
		 * @param array $conditions Array of where conditions, in the following format: 'column_id' => 'columns_type'.
		 * @param array $conditions_values Array of values to apply to where condition; must have same numbed of elements of columns, and they must respect defined type.
		 * @param bool  $clear_caches Whether system should clear caches (this is optional since other methods may want to run more optimized clear).
		 *
		 * @return void
		 */
		public function update_raw( $columns, $column_values, $conditions = array(), $conditions_values = array(), $clear_caches = false ) {
			global $wpdb;

			// calculate where statement.
			$query_where = '';

			if ( ! empty( $conditions ) ) {
				$query_where = array();

				foreach ( $conditions as $column => $value ) {
					$query_where[] = $column . '=' . $value;
				}

				$query_where = ' WHERE ' . implode( ' AND ', $query_where );
			}

			// retrieves wishlists that will be affected by the changes.
			if ( $clear_caches ) {

				$query = "SELECT ID FROM $wpdb->wlfmc_wishlist_items $query_where";
				$query = $conditions ? $wpdb->prepare( $query, $conditions_values ) : $query; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$ids   = $wpdb->get_col( $query ); // phpcs:ignore WordPress.DB
			}

			// calculate set statement.
			$query_columns = array();

			foreach ( $columns as $column => $value ) {
				$query_columns[] = $column . '=' . $value;
			}

			$query_columns = implode( ', ', $query_columns );

			// build query, and execute it.
			$query  = "UPDATE $wpdb->wlfmc_wishlist_items SET $query_columns $query_where";
			$values = $conditions ? array_merge( $column_values, $conditions_values ) : $column_values;

			$wpdb->query( $wpdb->prepare( $query, $values ) ); // phpcs:ignore WordPress.DB

			// clear cache for updated items.
			if ( $clear_caches && $ids ) {
				foreach ( $ids as $id ) {
					$this->clear_cache( $id );
				}
			}
		}

		/**
		 * Clear meta cache.
		 *
		 * @param WLFMC_Wishlist_Item|int $item Wishlist item object, or id of the item.
		 */
		public function clear_cache( &$item ) {
			if ( ! $item instanceof WLFMC_Wishlist_Item ) {
				$item = WLFMC_Wishlist_Factory::get_wishlist_item( $item );
			}

			wp_cache_delete( 'item-' . $item->get_id(), 'wlfmc-wishlist-items' );
			wp_cache_delete( 'wishlist-items-' . $item->get_wishlist_id(), 'wlfmc-wishlists' );
			wp_cache_delete( 'wishlist-items-' . $item->get_origin_wishlist_id(), 'wlfmc-wishlists' );
		}

		/* === MISC === */

		/**
		 * Here we collected all methods related to db implementation of the items
		 * They can be used without creating an instance of the Data Store, and are
		 * listed here just for
		 */

		/**
		 * Alter join section of the query, for ordering purpose
		 *
		 * @param string $join Join sql.
		 *
		 * @return string
		 */
		public static function filter_join_for_wishlist_count( $join ) {
			global $wpdb;
			$join .= " LEFT JOIN ( SELECT COUNT(*) AS wishlist_counts, parent_id FROM $wpdb->wlfmc_wishlist_items GROUP BY parent_id ) AS i ON ID = i.parent_id";

			return $join;
		}

		/**
		 * Alter orderby section of the query, for ordering purpose
		 *
		 * @param string $orderby Orderby sql.
		 *
		 * @return string
		 */
		public static function filter_orderby_for_wishlist_count( $orderby ) {
			return 'i.wishlist_counts ' . ( isset( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'ASC' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
	}
}
