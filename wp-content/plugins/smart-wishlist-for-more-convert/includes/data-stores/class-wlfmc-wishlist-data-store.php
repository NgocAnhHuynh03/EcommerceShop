<?php
/**
 * Wishlist Data Store
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.5.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WLFMC_Wishlist_Data_Store' ) ) {
	/**
	 * This class implements CRUD methods for wishlists.
	 */
	class WLFMC_Wishlist_Data_Store {

		/**
		 * Create a new wishlist
		 *
		 * @param WLFMC_Wishlist $wishlist Wishlist to create.
		 */
		public function create( &$wishlist ) {
			global $wpdb;

			if ( ! $wishlist->get_token() ) {
				$wishlist->set_token( $this->generate_token() );
			}

			if ( ! $wishlist->get_customer_id() ) {
				$customer = WLFMC_Wishlist_Factory::get_current_customer( false, 'edit' );
			} else {
				$customer = WLFMC_Wishlist_Factory::get_customer( $wishlist->get_customer_id() );
			}

			if ( ! $customer ) {
				return;
			}

			$wishlist->set_customer_id( $customer->get_id() );
			if ( $customer->is_session_based() ) {
				$wishlist->set_session_id( $customer->get_session_id() );
				$wishlist->set_expiration( $customer->get_expiration() );
			} else {
				$wishlist->set_user_id( $customer->get_user_id() );
			}

			// set slug if missing.
			$wishlist_slug = $wishlist->get_slug();

			if ( ! $wishlist_slug ) {
				$wishlist_slug = sanitize_title_with_dashes( $wishlist->get_name() );
				$wishlist->set_slug( $wishlist_slug );
			}

			if ( ! $wishlist->get_date_added() ) {
				$wishlist->set_date_added( gmdate( 'Y-m-d H:i:s' ) );
			}

			if ( in_array( $wishlist_slug, wlfmc_reserved_slugs(), true ) ) {
				// avoid slug duplicate, adding -n to the end of the string.
				$wishlist->set_slug( $wishlist_slug );
			} else {
				// avoid slug duplicate, adding -n to the end of the string.
				$wishlist->set_slug( $this->generate_slug( $wishlist_slug ) );
			}

			$columns = array(
				'wishlist_privacy' => '%d',
				'wishlist_name'    => '%s',
				'wishlist_desc'    => '%s',
				'wishlist_slug'    => '%s',
				'wishlist_token'   => '%s',
				'is_default'       => '%d',
				'customer_id'      => '%d',
			);
			$values  = array(
				apply_filters( 'wlfmc_add_wishlist_privacy', $wishlist->get_privacy() ),
				apply_filters( 'wlfmc_add_wishlist_name', $wishlist->get_name() ),
				apply_filters( 'wlfmc_add_wishlist_description', $wishlist->get_description() ),
				apply_filters( 'wlfmc_add_wishlist_slug', $wishlist->get_slug() ),
				apply_filters( 'wlfmc_add_wishlist_token', $wishlist->get_token() ),
				apply_filters( 'wlfmc_add_wishlist_is_default', $wishlist->get_is_default() ),
				apply_filters( 'wlfmc_add_wishlist_customer_id', $wishlist->get_customer_id() ),
			);

			$session_id = $wishlist->get_session_id();

			if ( $session_id ) {
				$columns['session_id'] = '%s';
				$values[]              = apply_filters( 'wlfmc_add_wishlist_session_id', $session_id );
			}

			$user_id = $wishlist->get_user_id();

			if ( $user_id ) {
				$columns['user_id'] = '%d';
				$values[]           = apply_filters( 'wlfmc_add_wishlist_user_id', $user_id );
			}

			$date_added = $wishlist->get_date_added( 'edit' );

			if ( $date_added ) {
				$columns['dateadded'] = 'FROM_UNIXTIME( %d )';
				$values[]             = apply_filters( 'wlfmc_add_wishlist_date_added', $date_added->getTimestamp() );
			}

			$expiration = $wishlist->get_expiration( 'edit' );

			if ( $expiration ) {
				$columns['expiration'] = 'FROM_UNIXTIME( %d )';
				$values[]              = apply_filters( 'wlfmc_add_wishlist_expiration', $expiration->getTimestamp() );
			}

			// if session wishlist, set always an expiration.
			$session_expiration = WLFMC_Session()->get_session_expiration();

			if ( isset( $columns['session_id'] ) && ! $expiration && $session_expiration ) {
				$columns['expiration'] = 'FROM_UNIXTIME( %d )';
				$values[]              = apply_filters( 'wlfmc_add_wishlist_expiration', $session_expiration );
			}

			$query_columns = implode( ', ', array_map( 'esc_sql', array_keys( $columns ) ) );
			$query_values  = implode( ', ', array_values( $columns ) );
			$query         = "INSERT INTO $wpdb->wlfmc_wishlists ( $query_columns ) VALUES ( $query_values ) ";

			$res = $wpdb->query( $wpdb->prepare( $query, $values ) ); // phpcs:ignore WordPress.DB

			if ( $res ) {
				$id = apply_filters( 'wlfmc_wishlist_correctly_created', $wpdb->insert_id );

				$wishlist->set_id( $id );
				$wishlist->apply_changes();
				$this->clear_caches( $wishlist );

				do_action( 'wlfmc_new_wishlist', $wishlist->get_id(), $wishlist );
			}
		}

		/**
		 * Read data from Database
		 *
		 * @param WLFMC_Wishlist $wishlist Wishlist to read from db.
		 *
		 * @throws Exception When cannot retrieve specified wishlist.
		 */
		public function read( &$wishlist ) {
			global $wpdb;

			$wishlist->set_defaults();

			$id    = $wishlist->get_id();
			$token = $wishlist->get_token();

			if ( ! $id && ! $token ) {
				throw new Exception( esc_html__( 'Invalid wishlist.', 'wc-wlfmc-wishlist' ) );
			}

			$wishlist_data = $wishlist->get_id() ? wp_cache_get( 'wlfmc-wishlist-id-' . $wishlist->get_id(), 'wlfmc-wishlists' ) : wp_cache_get( 'wlfmc-wishlist-token-' . $wishlist->get_token(), 'wlfmc-wishlists' );

			if ( ! $wishlist_data ) {

				$query = false;
				if ( $id ) {
					$query = $wpdb->prepare( "SELECT * FROM $wpdb->wlfmc_wishlists WHERE ID = %d", $id );
				} elseif ( $token ) {
					$query = $wpdb->prepare( "SELECT * FROM $wpdb->wlfmc_wishlists WHERE wishlist_token = %s", $token );
				}

				// retrieve wishlist data.
				$wishlist_data = $wpdb->get_row( $query ); // phpcs:ignore WordPress.DB

				wp_cache_set( 'wlfmc-wishlist-id-' . $wishlist->get_id(), $wishlist_data, 'wlfmc-wishlists' );
				wp_cache_set( 'wlfmc-wishlist-token-' . $wishlist->get_token(), $wishlist_data, 'wlfmc-wishlists' );
			}

			if ( ! $wishlist_data ) {
				throw new Exception( esc_html__( 'Invalid wishlist.', 'wc-wlfmc-wishlist' ) );
			}

			$wishlist->set_props(
				array(
					'id'          => $wishlist_data->ID,
					'privacy'     => $wishlist_data->wishlist_privacy,
					'user_id'     => $wishlist_data->user_id,
					'customer_id' => $wishlist_data->customer_id,
					'session_id'  => $wishlist_data->session_id ?? '',
					'name'        => wc_clean( stripslashes( $wishlist_data->wishlist_name ) ),
					'description' => wc_clean( stripslashes( $wishlist_data->wishlist_desc ) ),
					'slug'        => $wishlist_data->wishlist_slug,
					'token'       => $wishlist_data->wishlist_token,
					'is_default'  => $wishlist_data->is_default,
					'date_added'  => $wishlist_data->dateadded,
					'expiration'  => $wishlist_data->expiration ?? '',
				)
			);
			$wishlist->set_object_read( true );
		}

		/**
		 * Update wishlist data
		 *
		 * @param WLFMC_Wishlist $wishlist Wishlist to save on db, with $changes property.
		 */
		public function update( &$wishlist ) {

			if ( ! $wishlist->get_id() ) {
				return;
			}

			$data    = $wishlist->get_data();
			$changes = $wishlist->get_changes();

			if ( array_intersect(
				array(
					'user_id',
					'session_id',
					'customer_id',
					'slug',
					'name',
					'description',
					'token',
					'privacy',
					'expiration',
					'date_added',
					'is_default',
				),
				array_keys( $changes )
			) ) {
				$columns = array(
					'customer_id'      => '%d',
					'wishlist_privacy' => '%d',
					'wishlist_name'    => '%s',
					'wishlist_desc'    => '%s',
					'wishlist_token'   => '%s',
					'is_default'       => '%d',
					'dateadded'        => 'FROM_UNIXTIME( %d )',
				);
				$values  = array(
					$wishlist->get_customer_id(),
					$wishlist->get_privacy(),
					$wishlist->get_name(),
					$wishlist->get_description(),
					$wishlist->get_token(),
					$wishlist->get_is_default(),
					$wishlist->get_date_added( 'edit' ) ? $wishlist->get_date_added( 'edit' )->getTimestamp() : time(),
				);

				$session_id = $wishlist->get_session_id();

				if ( $session_id ) {
					$columns['session_id'] = '%s';
					$values[]              = apply_filters( 'wlfmc_update_wishlist_session_id', $session_id );
				} else {
					$columns['session_id'] = 'NULL';
				}

				$user_id = $wishlist->get_user_id();

				if ( $user_id ) {
					$columns['user_id'] = '%d';
					$values[]           = apply_filters( 'wlfmc_update_wishlist_user_id', $user_id );
				} else {
					$columns['user_id'] = 'NULL';
				}

				$expiration = $wishlist->get_expiration( 'edit' );

				if ( $expiration ) {
					$columns['expiration'] = 'FROM_UNIXTIME( %d )';
					$values[]              = apply_filters( 'wlfmc_update_wishlist_expiration', $expiration->getTimestamp() );
				} else {
					$columns['expiration'] = 'NULL';
				}

				$wishlist_slug = $wishlist->get_slug();

				if ( isset( $changes['slug'] ) && $wishlist_slug !== $data['slug'] ) {
					$columns['wishlist_slug'] = '%s';
					$values[]                 = $this->generate_slug( $wishlist_slug );
				}

				$this->update_raw( $columns, $values, array( 'ID' => '%d' ), array( $wishlist->get_id() ) );
			}

			$wishlist->apply_changes();
			$this->clear_caches( $wishlist );

			do_action( 'wlfmc_update_wishlist', $wishlist->get_id(), $wishlist );
		}

		/**
		 * Delete a wishlist
		 *
		 * @param WLFMC_Wishlist $wishlist Wishlist to delete.
		 */
		public function delete( &$wishlist ) {
			global $wpdb;

			$id         = $wishlist->get_id();
			$is_default = $wishlist->is_default();
			$user_id    = $wishlist->get_user_id();
			$session_id = $wishlist->get_session_id();

			if ( ! $id ) {
				return;
			}

			do_action( 'wlfmc_before_delete_wishlist', $wishlist->get_id() );

			$this->clear_caches( $wishlist );

			// delete wishlist and all its items.
			$wpdb->delete( $wpdb->wlfmc_wishlist_items, array( 'wishlist_id' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->delete( $wpdb->wlfmc_wishlists, array( 'ID' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

			do_action( 'wlfmc_delete_wishlist', $wishlist->get_id() );

			$wishlist->set_id( 0 );

			do_action( 'wlfmc_deleted_wishlist', $id );
		}

		/**
		 * Delete expired session wishlist from DB
		 *
		 * @return void
		 */
		public function delete_expired() {
			global $wpdb;
			$wpdb->query( "DELETE FROM $wpdb->wlfmc_wishlist_items WHERE wishlist_id IN ( SELECT ID FROM $wpdb->wlfmc_wishlists WHERE expiration < NOW() and user_id IS NULL )" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->query( "DELETE FROM $wpdb->wlfmc_wishlists WHERE expiration < NOW() and user_id IS NULL" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}

		/**
		 * Query database to search
		 *
		 * @param array $args Array of parameters used for the query:<br/>
		 * [<br/>
		 *   'id'                   // Wishlist id<br/>
		 *   'user_id'              // User id<br/>
		 *   'session_id'           // Session id<br/>
		 *   'wishlist_slug'        // Wishlist slug, exact match<br/>
		 *   'wishlist_name'        // Wishlist name, like<br/>
		 *   'wishlist_token'       // Wishlist token, exact match<br/>
		 *   'wishlist_visibility'  // all, visible, public, private<br/>
		 *   'user_search'          // String to search within user fields<br/>
		 *   's'                    // String to search within wishlist fields<br/>
		 *   'is_default'           // Whether searched wishlist is default<br/>
		 *   'orderby'              // Any of the table columns<br/>
		 *   'order'                // ASC, DESC<br/>
		 *   'limit'                // Limit of items to retrieve<br/>
		 *   'offset'               // Offset of items to retrieve<br/>
		 *   'show_empty'           // Whether to show empty wishlists<br/>
		 * ].
		 *
		 * @return WLFMC_Wishlist[] Array of matched wishlists.
		 */
		public function query( $args = array() ) {
			global $wpdb;

			$default = array(
				'id'                  => false,
				'customer_id'         => false,
				'user_id'             => ( is_user_logged_in() ) ? get_current_user_id() : false,
				'session_id'          => ( ! is_user_logged_in() ) ? WLFMC_Session()->maybe_get_session_id() : false,
				'wishlist_slug'       => false,
				'wishlist_name'       => false,
				'wishlist_token'      => false,
				'wishlist_visibility' => apply_filters( 'wlfmc_wishlist_visibility_string_value', 'all' ),
				'user_search'         => false,
				's'                   => false,
				'is_default'          => false,
				'orderby'             => '',
				'order'               => 'DESC',
				'limit'               => false,
				'offset'              => 0,
				'show_empty'          => true,
				'return_wishlists'    => true,
				'exclude_slugs'       => array(),
				'exclude_default'     => false,
				'on_sale'             => false,
				'back_in_stock'       => false,
				'price_change'        => false,
				'low_stock'           => false,
			);

			// if there is no current wishlist, and user was asking for current one, short-circuit query, as pointless.
			if ( ! is_user_logged_in() && ! WLFMC_Session()->has_session() && ! isset( $args['user_id'] ) && ! isset( $args['session_id'] ) ) {
				return array();
			}

			$args = wp_parse_args( $args, $default );
			extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract

			$sql  = 'SELECT SQL_CALC_FOUND_ROWS l.ID';
			$sql .= " FROM $wpdb->wlfmc_wishlists AS l";

			if ( ! empty( $user_search ) || ! empty( $s ) || ( ! empty( $orderby ) && 'user_login' === $orderby ) ) {
				$sql .= " LEFT JOIN $wpdb->users AS u ON l.`user_id` = u.ID";
			}

			if ( ! empty( $user_search ) || ! empty( $s ) ) {
				$sql .= " LEFT JOIN $wpdb->usermeta AS umn ON umn.`user_id` = u.`ID`";
				$sql .= " LEFT JOIN $wpdb->usermeta AS ums ON ums.`user_id` = u.`ID`";
				$sql .= " LEFT JOIN $wpdb->wlfmc_wishlist_customers AS customers ON customers.`customer_id` = l.`customer_id`";
			}

			$sql     .= ' WHERE 1';
			$sql_args = array();

			if ( ! empty( $customer_id ) ) {
				$sql .= ' AND l.`customer_id` = %d';

				$sql_args[] = $customer_id;
			} else {
				if ( ! empty( $user_id ) ) {
					$sql .= ' AND l.`user_id` = %d';

					$sql_args[] = $user_id;
				}

				if ( ! empty( $session_id ) ) {
					$sql .= ' AND l.`session_id` = %s AND l.`expiration` > NOW()';

					$sql_args[] = $session_id;
				}
			}

			if ( ! empty( $user_search ) && empty( $s ) ) {
				$sql .= ' AND (
							(
								umn.`meta_key` = %s AND
								ums.`meta_key` = %s AND
								(
									u.`user_email` LIKE %s OR
									umn.`meta_value` LIKE %s OR
									ums.`meta_value` LIKE %s
								)
							)OR
							customers.first_name LIKE %s OR
							customers.last_name LIKE %s OR
							customers.email LIKE %s OR
							customers.phone LIKE %s 
							
						)';

				$search_value = '%' . esc_sql( $user_search ) . '%';

				$sql_args[] = 'first_name';
				$sql_args[] = 'last_name';
				$sql_args[] = $search_value;
				$sql_args[] = $search_value;
				$sql_args[] = $search_value;
				$sql_args[] = $search_value;
				$sql_args[] = $search_value;
				$sql_args[] = $search_value;
				$sql_args[] = $search_value;
			}

			if ( ! empty( $s ) ) {
				$sql .= ' AND (
							(
								umn.`meta_key` = %s AND
								ums.`meta_key` = %s AND
								(
									u.`user_email` LIKE %s OR
									u.`user_login` LIKE %s OR
									umn.`meta_value` LIKE %s OR
									ums.`meta_value` LIKE %s
								)
							) OR
							customers.first_name LIKE %s OR
							customers.last_name LIKE %s OR
							customers.email LIKE %s OR
							customers.phone LIKE %s OR
							l.wishlist_name LIKE %s OR
							l.wishlist_slug LIKE %s OR
							l.wishlist_token LIKE %s
						)';

				$search_value = '%' . esc_sql( $s ) . '%';

				$sql_args[] = 'first_name';
				$sql_args[] = 'last_name';
				$sql_args[] = $search_value;
				$sql_args[] = $search_value;
				$sql_args[] = $search_value;
				$sql_args[] = $search_value;
				$sql_args[] = $search_value;
				$sql_args[] = $search_value;
				$sql_args[] = $search_value;
				$sql_args[] = $search_value;
				$sql_args[] = $search_value;
				$sql_args[] = $search_value;
				$sql_args[] = $search_value;
			}

			if ( ! empty( $is_default ) ) {
				$sql       .= ' AND l.`is_default` = %d';
				$sql_args[] = $is_default;
			} elseif ( ! empty( $exclude_default ) ) {
				$sql .= ' AND l.`is_default` = 0';
			}

			if ( ! empty( $id ) ) {
				$sql       .= ' AND l.`ID` = %d';
				$sql_args[] = $id;
			}

			if ( isset( $wishlist_slug ) && false !== $wishlist_slug ) {
				$sql       .= ' AND l.`wishlist_slug` = %s';
				$sql_args[] = sanitize_title_with_dashes( $wishlist_slug );
			}

			if ( is_array( $exclude_slugs ) && ! empty( $exclude_slugs ) ) {
				$exclude_slugs_sql = implode( "','", array_filter( $exclude_slugs, 'sanitize_title_with_dashes' ) );
				if ( ! empty( $exclude_slugs_sql ) ) {
					$sql .= " AND l.`wishlist_slug` NOT IN ( '$exclude_slugs_sql' )";
				}
			}

			if ( ! empty( $wishlist_token ) ) {
				$sql       .= ' AND l.`wishlist_token` = %s';
				$sql_args[] = $wishlist_token;
			}

			if ( ! empty( $wishlist_name ) ) {
				$sql       .= ' AND l.`wishlist_name` LIKE %s';
				$sql_args[] = '%' . esc_sql( $wishlist_name ) . '%';
			}

			if ( isset( $wishlist_visibility ) && 'all' !== $wishlist_visibility ) {
				if ( ! is_int( $wishlist_visibility ) ) {
					$wishlist_visibility = wlfmc_get_privacy_value( $wishlist_visibility );
				}

				$sql       .= ' AND l.`wishlist_privacy` = %d';
				$sql_args[] = $wishlist_visibility;
			}

			if ( empty( $show_empty ) ) {
				$sql .= " AND l.`ID` IN ( SELECT wishlist_id FROM $wpdb->wlfmc_wishlist_items )";
			}

			if ( $on_sale ) {
				$sql .= " AND l.`ID` IN ( SELECT DISTINCT wishlist_id FROM $wpdb->wlfmc_wishlist_items WHERE on_sale = 1 )";
			}
			if ( $back_in_stock ) {
				$sql .= " AND l.`ID` IN ( SELECT DISTINCT wishlist_id FROM $wpdb->wlfmc_wishlist_items WHERE back_in_stock = 1 )";
			}
			if ( $price_change ) {
				$sql .= " AND l.`ID` IN ( SELECT DISTINCT wishlist_id FROM $wpdb->wlfmc_wishlist_items WHERE price_change = 1 )";
			}
			if ( $low_stock ) {
				$sql .= " AND l.`ID` IN ( SELECT DISTINCT wishlist_id FROM $wpdb->wlfmc_wishlist_items WHERE low_stock = 1 )";
			}

			$sql .= ' GROUP BY l.ID';
			$sql .= ' ORDER BY';

			if ( ! empty( $orderby ) && isset( $order ) ) {
				$sql .= ' ' . esc_sql( $orderby ) . ' ' . esc_sql( $order ) . ', ';
			}

			$sql .= ' is_default DESC';

			if ( ! empty( $limit ) && isset( $offset ) ) {
				$sql       .= ' LIMIT %d, %d';
				$sql_args[] = $offset;
				$sql_args[] = $limit;
			}

			if ( ! empty( $sql_args ) ) {
				$sql = $wpdb->prepare( $sql, $sql_args ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}

			$lists = $wpdb->get_col( $sql ); // phpcs:ignore WordPress.DB

			if ( ! empty( $lists ) ) {
				$lists = $return_wishlists ? array_map( array( 'WLFMC_Wishlist_Factory', 'get_wishlist' ), $lists ) : $lists;
			} else {
				$lists = array();
			}

			return apply_filters( 'wlfmc_get_wishlists', $lists, $args );
		}

		/**
		 * Counts items that match
		 *
		 * @param array $args Same parameters allowed for {@see query} method.
		 *
		 * @return int Count of items
		 */
		public function count( $args = array() ) {
			// retrieve number of items found.
			return count( $this->query( $args ) );
		}

		/**
		 * Search user ids whose wishlists match passed parameters
		 * NOTE: this will only retrieve wishlists for a logged-in user, while guests wishlist will be ignored
		 *
		 * @param mixed $args Array of valid arguments<br/>
		 * [<br/>
		 *     'search' // String to match against first name / last name / user login or user email of wishlist owner<br/>
		 *     'limit'  // Pagination param: number of items to show in one page. 0 to show all items<br/>
		 *     'offset' // Pagination param: offset for the current set. 0 to start from the first item<br/>
		 * ].
		 *
		 * @return int[] Array of user ids
		 */
		public function search_users( $args = array() ) {
			global $wpdb;

			$default = array(
				'search' => false,
				'limit'  => false,
				'offset' => 0,
			);

			$args = wp_parse_args( $args, $default );
			extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract

			$sql = "SELECT DISTINCT i.user_id
                    FROM $wpdb->wlfmc_wishlist_items AS i
                    LEFT JOIN $wpdb->wlfmc_wishlists AS l ON i.wishlist_id = l.ID";

			if ( ! empty( $search ) ) {
				$sql .= " LEFT JOIN $wpdb->users AS u ON l.`user_id` = u.ID";
				$sql .= " LEFT JOIN $wpdb->usermeta AS umn ON umn.`user_id` = u.`ID`";
				$sql .= " LEFT JOIN $wpdb->usermeta AS ums ON ums.`user_id` = u.`ID`";
			}

			$sql     .= ' WHERE l.wishlist_privacy = %d';
			$sql_args = array( 0 );

			if ( ! empty( $search ) ) {
				$sql .= ' AND (
							umn.`meta_key` = %s AND
							ums.`meta_key` = %s AND
							(
								u.`user_email` LIKE %s OR
								u.`user_login` LIKE %s OR
								umn.`meta_value` LIKE %s OR
								ums.`meta_value` LIKE %s
							)
						)';

				$search_string = '%' . esc_sql( $search ) . '%';

				$sql_args[] = 'first_name';
				$sql_args[] = 'last_name';
				$sql_args[] = $search_string;
				$sql_args[] = $search_string;
				$sql_args[] = $search_string;
				$sql_args[] = $search_string;
			}

			if ( ! empty( $limit ) && isset( $offset ) ) {
				$sql .= " LIMIT $offset, $limit";
			}

			return $wpdb->get_col( $wpdb->prepare( $sql, $sql_args ) ); // phpcs:ignore WordPress.DB
		}

		/**
		 * Raw update method; useful when it is needed to update a bunch of wishlists
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
				$query = "SELECT ID FROM $wpdb->wlfmc_wishlists $query_where";
				$query = $conditions ? $wpdb->prepare( $query, $conditions_values ) : $query; // phpcs:ignore WordPress.DB
				$ids   = $wpdb->get_col( $query ); // phpcs:ignore WordPress.DB
			}

			// calculate set statement.
			$query_columns = array();

			foreach ( $columns as $column => $value ) {
				$query_columns[] = $column . '=' . $value;
			}

			$query_columns = implode( ', ', $query_columns );

			// build query, and execute it.
			$query  = "UPDATE $wpdb->wlfmc_wishlists SET $query_columns $query_where";
			$values = $conditions ? array_merge( $column_values, $conditions_values ) : $column_values;

			$wpdb->query( $wpdb->prepare( $query, $values ) ); // phpcs:ignore WordPress.DB

			// clear cache for updated items.
			if ( $clear_caches && $ids ) {
				foreach ( $ids as $id ) {
					$this->clear_caches( $id );
				}
			}
		}

		/**
		 * Retrieve all items for the wishlist
		 *
		 * @param WLFMC_Wishlist $wishlist wishlist that read items.
		 * @param string         $type item type.
		 *
		 * @return WLFMC_Wishlist_Item[] Array or Wishlist items for the wishlist
		 */
		public function read_items( $wishlist, $type = 'prod_id' ) {
			global $wpdb;

			// Get from cache if available.
			$items = 0 < $wishlist->get_id() ? wp_cache_get( 'wishlist-items-' . $wishlist->get_id(), 'wlfmc-wishlists' ) : false;

			if ( false === $items ) {
				$query = "SELECT i.* FROM $wpdb->wlfmc_wishlist_items as i INNER JOIN $wpdb->posts as p on i.prod_id = p.ID WHERE i.wishlist_id = %d AND p.post_type IN ( %s, %s ) AND p.post_status = %s";

				// remove hidden products from result.
				$hidden_products = wlfmc_get_hidden_products();

				if ( is_array( $hidden_products ) && ! empty( $hidden_products ) && apply_filters( 'wlfmc_remove_hidden_products_via_query', true ) ) {
					$hidden_products_sql = implode( ', ', array_filter( $hidden_products, 'esc_sql' ) );
					if ( ! empty( $hidden_products_sql ) ) {
						$query .= " AND prod_id NOT IN ( $hidden_products_sql )";
					}
				}

				// order by statement.
				$query .= ' ORDER BY position ASC, ID DESC;';

				$items = $wpdb->get_results(  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
					$wpdb->prepare(
						$query, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						array(
							$wishlist->get_id(),
							'product',
							'product_variation',
							'publish',
						)
					)
				);

				/**
				 * This filter was added to allow developer remove hidden products using a foreach loop, instead of the query
				 * It is required when the store contains a huge number of hidden products, and the resulting query would fail
				 * to be submitted to DBMS because of its size
				 *
				 * This code requires reasonable amount of products in the wishlist
				 * A great number of products retrieved from the main query could easily degrade performance of the overall system.
				 */
				if ( ! empty( $hidden_products ) && ! empty( $items ) && ! apply_filters( 'wlfmc_remove_hidden_products_via_query', true ) ) {
					foreach ( $items as $item_id => $item ) {
						if ( ! in_array( $item->prod_id, $hidden_products, true ) ) {
							continue;
						}

						unset( $items[ $item_id ] );
					}
				}

				foreach ( $items as $item ) {
					wp_cache_set( 'item-' . $item->ID, $item, 'wlfmc-wishlist-items' );
				}

				if ( 0 < $wishlist->get_id() ) {
					wp_cache_set( 'wishlist-items-' . $wishlist->get_id(), $items, 'wlfmc-wishlists' );
				}
			}

			if ( ! empty( $items ) ) {
				$items = array_map(
					array(
						'WLFMC_Wishlist_Factory',
						'get_wishlist_item',
					),
					array_combine(
						wp_list_pluck( $items, $type ),
						$items
					)
				);
			} else {
				$items = array();
			}

			return apply_filters( 'wlfmc_get_products', $items, array( 'wishlist_id' => $wishlist->get_id() ) );
		}

		/**
		 * Delete all items from the wishlist
		 *
		 * @param WLFMC_Wishlist $wishlist wishlist that deleted items.
		 *
		 * @return void
		 */
		public function delete_items( $wishlist ) {
			global $wpdb;

			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->wlfmc_wishlist_items WHERE wishlist_id = %d", $wishlist->get_id() ) ); // phpcs:ignore WordPress.DB

			$this->clear_caches( $wishlist );
		}

		/**
		 * Generate default token for the wishlist
		 *
		 * @return string Wishlist token
		 */
		public function generate_token() {
			global $wpdb;

			$sql = "SELECT COUNT(ID) as count FROM $wpdb->wlfmc_wishlists WHERE `wishlist_token` = %s";

			do {
				$dictionary = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
				$nchars     = 12;
				$token      = '';

				for ( $i = 0; $i <= $nchars - 1; $i ++ ) {
					$token .= $dictionary[ wp_rand( 0, strlen( $dictionary ) - 1 ) ];
				}

				$count = $wpdb->get_var( $wpdb->prepare( $sql, $token ) ); // phpcs:ignore WordPress.DB
			} while ( $count );

			return $token;
		}

		/**
		 * When a session is finalized, all session wishlists will be converted to user wishlists
		 * This method takes also care of allowing just one default per time after finalization
		 *
		 * @param string $session_id Session id.
		 * @param int    $user_id User id.
		 *
		 * @version 1.6.3
		 * @return void
		 */
		public function assign_to_user( $session_id, $user_id ) {
			global $wpdb;
			$guest_customer_id = $wpdb->get_var( $wpdb->prepare( "SELECT customer_id FROM $wpdb->wlfmc_wishlist_customers WHERE session_id = %s", $session_id ) );//phpcs:ignore WordPress.DB
			if ( ! $guest_customer_id ) {
				return;
			}

			// update any item that is assigned to the user.
			$customer_id = $wpdb->get_var( $wpdb->prepare( "SELECT customer_id FROM $wpdb->wlfmc_wishlist_customers WHERE user_id = %d", $user_id ) );//phpcs:ignore WordPress.DB
			if ( ! $customer_id ) {
				try {
					$customer = new WLFMC_Customer( (int) $guest_customer_id );
					$customer->set_user_id( $user_id );
					$customer->save();
					$this->update_raw(
						array(
							'session_id' => 'NULL',
							'expiration' => 'NULL',
							'user_id'    => '%d',
						),
						array( $user_id ),
						array( 'customer_id' => '%s' ),
						array( $guest_customer_id )
					);
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_items SET user_id = %d WHERE customer_id = %d", $user_id, $guest_customer_id ) );//phpcs:ignore WordPress.DB
				} catch ( Exception $e ) {
					return;
				}
			} else {
				try {
					$user_customer    = WLFMC_Wishlist_Factory::get_customer( (int) $customer_id );
					$session_customer = WLFMC_Wishlist_Factory::get_customer( (int) $guest_customer_id );
					if ( ! $user_customer || ! $session_customer ) {
						return;
					}
					WC_Data_Store::load( 'wlfmc-customer' )->merge_customers( $user_customer, $session_customer );
				} catch ( Exception $e ) {
					return;
				}
			}
		}

		/**
		 * Retrieve default wishlist for current user/session; if none is found, generate it
		 *
		 * @param string|int|bool $id Pass this param when you want to retrieve a wishlist for a specific user/session.
		 * @param string          $context Context; when on edit context, wishlist will be created, if not exists.
		 *
		 * @return WLFMC_Wishlist|bool Default wishlist for current user/session, or false on failure
		 */
		public function get_default_wishlist( $id = false, $context = 'read' ) {
			global $wpdb;
			$wishlist_id = false;
			$cache_key   = false;

			$user_id    = get_current_user_id();
			$session_id = WLFMC_Session()->maybe_get_session_id();

			if ( ! empty( $id ) && is_int( $id ) ) {
				$cache_key   = 'wishlist-default-' . $id;
				$wishlist_id = wp_cache_get( $cache_key, 'wlfmc-wishlists' );
				$wishlist_id = false !== $wishlist_id ? $wishlist_id : $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->wlfmc_wishlists WHERE user_id = %d AND is_default = 1", $id ) ); // phpcs:ignore WordPress.DB
			} elseif ( ! empty( $id ) && is_string( $id ) ) {
				$cache_key   = 'wishlist-default-' . $id;
				$wishlist_id = wp_cache_get( $cache_key, 'wlfmc-wishlists' );
				$wishlist_id = false !== $wishlist_id ? $wishlist_id : $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->wlfmc_wishlists WHERE session_id = %s AND expiration > NOW() AND is_default = 1", $id ) ); // phpcs:ignore WordPress.DB
			} elseif ( $user_id ) {
				$cache_key   = 'wishlist-default-' . $user_id;
				$wishlist_id = wp_cache_get( $cache_key, 'wlfmc-wishlists' );
				$wishlist_id = false !== $wishlist_id ? $wishlist_id : $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->wlfmc_wishlists WHERE user_id = %d AND is_default = 1", $user_id ) ); // phpcs:ignore WordPress.DB
			} elseif ( $session_id ) {
				$cache_key   = 'wishlist-default-' . $session_id;
				$wishlist_id = wp_cache_get( $cache_key, 'wlfmc-wishlists' );
				$wishlist_id = false !== $wishlist_id ? $wishlist_id : $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->wlfmc_wishlists WHERE session_id = %s AND expiration > NOW() AND is_default = 1", $session_id ) ); // phpcs:ignore WordPress.DB
			}

			if ( $wishlist_id ) {
				if ( $cache_key ) {
					wp_cache_set( $cache_key, $wishlist_id, 'wlfmc-wishlists' );
				}

				return WLFMC_Wishlist_Factory::get_wishlist( $wishlist_id );
			} elseif ( 'edit' === $context ) {
				$id       = ( ! empty( $id ) && is_int( $id ) ) ? $id : ( $user_id ? $user_id : ( $session_id ? $session_id : false ) );
				$wishlist = $this->generate_default_wishlist( $id );

				if ( $cache_key ) {
					wp_cache_set( $cache_key, $wishlist->get_id(), 'wlfmc-wishlists' );
				}

				return $wishlist;
			} else {
				/**
				 * If no default wishlist was found, register null as cache value
				 * This will be used until someone tries to edit the list (entering previous elseif),
				 * causing a new default wishlist to be automatically generated and stored in cache, replacing null.
				 */
				if ( $cache_key ) {
					wp_cache_set( $cache_key, null, 'wlfmc-wishlists' );
				}

				return false;
			}
		}

		/**
		 * Retrieve wishlist by slug for current user/session; if none is found, generate it
		 *
		 * @param string $slug wishlist slug.
		 * @param string $context Context; when on edit context, wishlist will be created, if not exists.
		 *
		 * @return WLFMC_Wishlist|bool Default wishlist for current user/session, or false on failure
		 */
		public function get_wishlist_by_slug( $slug = false, $context = 'read' ) {
			global $wpdb;

			if ( ! $slug ) {
				return false;
			}

			$wishlist_id = false;
			$cache_key   = false;

			$user_id    = get_current_user_id();
			$session_id = WLFMC_Session()->maybe_get_session_id();

			if ( $user_id ) {
				$cache_key   = 'wishlist-' . $slug . '-' . $user_id;
				$wishlist_id = wp_cache_get( $cache_key, 'wlfmc-wishlists' );
				$wishlist_id = false !== $wishlist_id ? $wishlist_id : $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->wlfmc_wishlists WHERE user_id = %d AND wishlist_slug = %s", $user_id, $slug ) ); // phpcs:ignore WordPress.DB
			} elseif ( $session_id ) {
				$cache_key   = 'wishlist-' . $slug . '-' . $session_id;
				$wishlist_id = wp_cache_get( $cache_key, 'wlfmc-wishlists' );
				$wishlist_id = false !== $wishlist_id ? $wishlist_id : $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->wlfmc_wishlists WHERE session_id = %s AND expiration > NOW() AND wishlist_slug = %s", $session_id, $slug ) ); // phpcs:ignore WordPress.DB
			}

			if ( $wishlist_id ) {
				if ( $cache_key ) {
					wp_cache_set( $cache_key, $wishlist_id, 'wlfmc-wishlists' );
				}

				return WLFMC_Wishlist_Factory::get_wishlist( $wishlist_id );
			} elseif ( 'edit' === $context ) {
				$id       = $user_id ? $user_id : ( $session_id ? $session_id : false );
				$wishlist = $this->generate_wishlist_by_slug( $id, $slug );

				if ( $cache_key ) {
					wp_cache_set( $cache_key, $wishlist->get_id(), 'wlfmc-wishlists' );
				}

				return $wishlist;
			} else {
				/**
				 * If no default wishlist was found, register null as cache value
				 * This will be used until someone tries to edit the list (entering previous elseif),
				 * causing a new default wishlist to be automatically generated and stored in cache, replacing null.
				 */
				if ( $cache_key ) {
					wp_cache_set( $cache_key, null, 'wlfmc-wishlists' );
				}

				return false;
			}
		}


		/**
		 * Retrieve list by slug for user/session; if none is found, generate it if customer for user/session exists
		 *
		 * @param string          $slug wishlist slug.
		 * @param string|int|bool $id Pass this param when you want to retrieve a wishlist for a specific user/session.
		 * @param string          $context Context; when on edit context, wishlist will be created, if not exists.
		 *
		 * @since 1.6.1
		 * @return WLFMC_Wishlist|bool List by slug for user/session, or false on failure
		 */
		public function get_list_by_slug( $slug, $id, $context = 'read' ) {
			global $wpdb;

			if ( ! $slug || ! $id ) {
				return false;
			}

			$wishlist_id = false;
			$cache_key   = false;

			if ( ! empty( $id ) && is_int( $id ) ) {
				$cache_key   = 'wishlist-' . $slug . '-' . $id;
				$wishlist_id = wp_cache_get( $cache_key, 'wlfmc-wishlists' );
				$wishlist_id = false !== $wishlist_id ? $wishlist_id : $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->wlfmc_wishlists WHERE user_id = %d AND wishlist_slug = %s", $id, $slug ) ); // phpcs:ignore WordPress.DB

			} elseif ( ! empty( $id ) && is_string( $id ) ) {
				$cache_key   = 'wishlist-' . $slug . '-' . $id;
				$wishlist_id = wp_cache_get( $cache_key, 'wlfmc-wishlists' );
				$wishlist_id = false !== $wishlist_id ? $wishlist_id : $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->wlfmc_wishlists WHERE session_id = %s AND expiration > NOW() AND wishlist_slug = %s", $id, $slug ) ); // phpcs:ignore WordPress.DB
			}

			if ( $wishlist_id ) {
				if ( $cache_key ) {
					wp_cache_set( $cache_key, $wishlist_id, 'wlfmc-wishlists' );
				}

				return WLFMC_Wishlist_Factory::get_wishlist( $wishlist_id );
			} elseif ( 'edit' === $context ) {
				$customer_id = wlfmc_get_customer_id_by_user( $id );
				if ( ! $customer_id ) {
					return false;
				}
				$wishlist = $this->generate_wishlist_for_customer( $id, $slug, $customer_id );

				if ( $cache_key ) {
					wp_cache_set( $cache_key, $wishlist->get_id(), 'wlfmc-wishlists' );
				}

				return $wishlist;
			} else {
				/**
				 * If no default wishlist was found, register null as cache value
				 * This will be used until someone tries to edit the list (entering previous elseif),
				 * causing a new default wishlist to be automatically generated and stored in cache, replacing null.
				 */
				if ( $cache_key ) {
					wp_cache_set( $cache_key, null, 'wlfmc-wishlists' );
				}

				return false;
			}
		}

		/**
		 * Generate a new default wishlist
		 *
		 * @param string|int|bool $id Pass this param when you want to create a wishlist for a specific user/session.
		 *
		 * @return WLFMC_Wishlist|bool Brand-new default wishlist, or false on failure
		 */
		public function generate_default_wishlist( $id ) {
			global $wpdb;
			try {
				$customer_id      = false;
				$default_wishlist = new WLFMC_Wishlist();
				if ( ! empty( $id ) && is_int( $id ) ) {
					$customer_id = $wpdb->get_var( $wpdb->prepare( "SELECT customer_id FROM $wpdb->wlfmc_wishlist_customers WHERE user_id = %d", $id ) );//phpcs:ignore WordPress.DB
					$default_wishlist->set_user_id( $id );
				} elseif ( ! empty( $id ) && is_string( $id ) ) {
					$default_wishlist->set_session_id( $id );
					$customer_id = $wpdb->get_var( $wpdb->prepare( "SELECT customer_id FROM $wpdb->wlfmc_wishlist_customers WHERE session_id = %s", $id ) );//phpcs:ignore WordPress.DB
				}
				if ( ! $customer_id ) {
					$customer = new WLFMC_Customer();
					if ( is_int( $id ) ) {
						$customer->set_user_id( $id );
					} elseif ( is_string( $id ) ) {
						$customer->set_session_id( $id );
					}
					$customer->save();
					$customer_id = $customer->get_id();
				}
				$default_wishlist->set_customer_id( $customer_id );
				$default_wishlist->set_is_default( 1 );
				$default_wishlist->save();

				/**
				 * Let developers perform processing when default wishlist is created.
				 */
				do_action( 'wlfmc_generated_default_wishlist', $default_wishlist, $id );
			} catch ( Exception $e ) {
				return false;
			}

			return $default_wishlist;
		}


		/**
		 * Generate a new list by slug
		 *
		 * @param string|int|bool $id Pass this param when you want to create a wishlist for a specific user/session.
		 * @param string          $slug wishlist slug.
		 *
		 * @return WLFMC_Wishlist|bool Brand-new default wishlist, or false on failure
		 */
		public function generate_wishlist_by_slug( $id, $slug ) {
			try {
				$wishlist = new WLFMC_Wishlist();
				$wishlist->set_slug( $slug );

				if ( ! empty( $id ) && is_int( $id ) ) {
					$wishlist->set_user_id( $id );
				} elseif ( ! empty( $id ) && is_string( $id ) ) {
					$wishlist->set_session_id( $id );
				}

				$wishlist->save();

				/**
				 * Let developers perform processing when default wishlist is created.
				 */
				do_action( 'wlfmc_generated_' . $slug . '_wishlist', $wishlist, $id );
			} catch ( Exception $e ) {
				return false;
			}

			return $wishlist;
		}

		/**
		 * Generate a new list by slug for customer
		 *
		 * @param string|int|bool $id Pass this param when you want to create a list for a specific user/session.
		 * @param string          $slug wishlist slug.
		 * @param int             $customer_id customer id.
		 *
		 * @return WLFMC_Wishlist|bool Brand-new default wishlist, or false on failure
		 */
		public function generate_wishlist_for_customer( $id, $slug, $customer_id ) {
			try {
				$wishlist = new WLFMC_Wishlist();
				$wishlist->set_slug( $slug );

				if ( ! empty( $id ) && is_int( $id ) ) {
					$wishlist->set_user_id( $id );
				} elseif ( ! empty( $id ) && is_string( $id ) ) {
					$wishlist->set_session_id( $id );
				}
				$wishlist->set_customer_id( $customer_id );
				$wishlist->save();

				/**
				 * Let developers perform processing when default wishlist is created.
				 */
				do_action( 'wlfmc_generated_' . $slug . '_wishlist_by_customer_id', $wishlist, $id, $customer_id );
			} catch ( Exception $e ) {
				return false;
			}

			return $wishlist;
		}

		/**
		 * Generate unique slug for the wishlist
		 *
		 * @param string $slug Original slug assigned to the wishlist (it could be custom assigned, or generated from the title).
		 *
		 * @return string Unique slug, derived from original one adding ordinal number when necessary
		 */
		public function generate_slug( $slug ) {
			if ( empty( $slug ) ) {
				return '';
			}

			while ( $this->slug_exists( $slug ) ) {
				$match = array();

				if ( ! preg_match( '/([a-z-]+)-([0-9]+)/', $slug, $match ) ) {
					$i = 2;
				} else {
					$i    = intval( $match[2] ) + 1;
					$slug = $match[1];
				}

				$suffix = '-' . $i;
				$slug   = substr( $slug, 0, 200 - strlen( $suffix ) ) . $suffix;
			}

			return $slug;
		}

		/**
		 * Checks if a slug already exists
		 *
		 * @param string $slug Slug to check on db.
		 *
		 * @return bool Whether slug already exists for current session or not
		 */
		public function slug_exists( $slug ) {
			global $wpdb;

			$res = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) as count FROM $wpdb->wlfmc_wishlists WHERE wishlist_slug = %s", $slug ) ); // phpcs:ignore WordPress.DB

			return (bool) $res;
		}

		/**
		 * Check if we're registering first wishlist for the user/session
		 *
		 * @return bool Whether current wishlist should be default
		 */
		protected function should_be_default() {
			global $wpdb;
			$user_id    = get_current_user_id();
			$session_id = WLFMC_Session()->maybe_get_session_id();

			if ( $user_id ) {
				$wishlists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( ID ) as count FROM $wpdb->wlfmc_wishlists WHERE user_id = %d AND is_default = %d", $user_id, 1 ) ); // phpcs:ignore WordPress.DB

				return ! $wishlists;
			}

			if ( $session_id ) {
				$wishlists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( ID ) as count FROM $wpdb->wlfmc_wishlists WHERE session_id = %s AND expiration > NOW() AND is_default = %d", $session_id, 1 ) ); // phpcs:ignore WordPress.DB

				return ! $wishlists;
			}

			return true;
		}

		/**
		 * Clear wishlist related caches
		 *
		 * @param WLFMC_Wishlist|int|string $wishlist .
		 *
		 * @return void
		 */
		protected function clear_caches( &$wishlist ) {
			if ( $wishlist instanceof WLFMC_Wishlist ) {
				$id    = $wishlist->get_id();
				$token = $wishlist->get_token();
			} elseif ( intval( $wishlist ) ) {
				$id       = $wishlist;
				$wishlist = wlfmc_get_wishlist( $wishlist );
				$token    = $wishlist ? $wishlist->get_token() : false;
			} else {
				$token    = $wishlist;
				$wishlist = wlfmc_get_wishlist( $wishlist );
				$id       = $wishlist ? $wishlist->get_id() : false;
			}

			$user_id    = $wishlist ? $wishlist->get_user_id() : false;
			$session_id = $wishlist ? $wishlist->get_session_id() : false;

			wp_cache_delete( 'wishlist-items-' . $id, 'wlfmc-wishlists' );
			wp_cache_delete( 'wishlist-id-' . $id, 'wlfmc-wishlists' );
			wp_cache_delete( 'wishlist-token-' . $token, 'wlfmc-wishlists' );

			if ( $user_id ) {
				wp_cache_delete( 'user-wishlists-' . $user_id . '-object', 'wlfmc-wishlists' );
				wp_cache_delete( 'user-wishlists-' . $user_id . '-ids', 'wlfmc-wishlists' );
			}

			if ( $session_id ) {
				wp_cache_delete( 'user-wishlists-' . $session_id . '-object', 'wlfmc-wishlists' );
				wp_cache_delete( 'user-wishlists-' . $session_id . '-ids', 'wlfmc-wishlists' );
			}
		}
	}
}
