<?php
/**
 * Customer Data Store
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @since 1.6.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'WLFMC_Customer_Data_Store' ) ) {
	/**
	 * This class implements CRUD methods for customers.
	 */
	class WLFMC_Customer_Data_Store {

		/**
		 * Create a new customer
		 *
		 * @param WLFMC_Customer $customer Customer to create.
		 */
		public function create( &$customer ) {
			global $wpdb;

			if ( empty( $customer->get_token() ) ) {
				$customer->set_token( $this->generate_token() ); // this token used for verified.
			}

			if ( ! $customer->get_date_added() ) {
				$customer->set_date_added( gmdate( 'Y-m-d H:i:s' ) );
			}

			if ( ! $customer->get_session_id() && ! $customer->get_user_id() ) {
				if ( is_user_logged_in() ) {
					$user_id = get_current_user_id();
					$customer->set_user_id( apply_filters( 'wlfmc_add_customer_user_id', $user_id ) );
				} else {
					$session_id = WLFMC_Session()->get_session_id();
					$customer->set_session_id( apply_filters( 'wlfmc_add_customer_session_id', $session_id ) );
				}
			}

			$columns = array(
				'first_name'        => '%s',
				'last_name'         => '%s',
				'email'             => '%s',
				'phone'             => '%s',
				'email_verified'    => '%d',
				'phone_verified'    => '%d',
				'unsubscribed'      => '%d',
				'notes'             => '%s',
				'customer_meta'     => '%s',
				'order_customer_id' => '%s',
				'lang'              => '%s',
			);
			$values  = array(
				apply_filters( 'wlfmc_add_customer_first_name', $customer->get_first_name() ),
				apply_filters( 'wlfmc_add_customer_last_name', $customer->get_last_name() ),
				apply_filters( 'wlfmc_add_customer_email', $customer->get_email() ),
				apply_filters( 'wlfmc_add_customer_phone', $customer->get_phone() ),
				apply_filters( 'wlfmc_add_customer_email_verified', $customer->is_email_verified() ),
				apply_filters( 'wlfmc_add_customer_phone_verified', $customer->is_phone_verified() ),
				apply_filters( 'wlfmc_add_customer_unsubscribed', $customer->is_unsubscribed() ),
				apply_filters( 'wlfmc_add_customer_notes', $customer->get_notes( 'edit' ), $customer ),
				apply_filters( 'wlfmc_add_customer_meta', $customer->get_customer_meta( 'edit' ), $customer ),
				apply_filters( 'wlfmc_add_customer_order_customer_id', $customer->get_order_customer_id() ),
				apply_filters( 'wlfmc_add_customer_lang', $customer->get_lang() ),
			);

			$session_id = $customer->get_session_id();

			if ( $session_id ) {
				$columns['session_id'] = '%s';
				$values[]              = apply_filters( 'wlfmc_add_customer_session_id', $session_id );
				$columns['token']      = '%s';
				$values[]              = apply_filters( 'wlfmc_add_customer_token', $customer->get_token() );
			}

			$user_id = $customer->get_user_id();

			if ( $user_id ) {
				$columns['user_id'] = '%d';
				$values[]           = apply_filters( 'wlfmc_add_customer_user_id', $user_id );
			}

			$date_added = $customer->get_date_added( 'edit' );

			if ( $date_added ) {
				$columns['dateadded'] = 'FROM_UNIXTIME( %d )';
				$values[]             = apply_filters( 'wlfmc_add_customer_date_added', $date_added->getTimestamp() );
			}

			$expiration = $customer->get_expiration( 'edit' );

			if ( $expiration ) {
				$columns['expiration'] = 'FROM_UNIXTIME( %d )';
				$values[]              = apply_filters( 'wlfmc_add_customer_expiration', $expiration->getTimestamp() );
			}

			// if session customer, set always an expiration.
			$session_expiration = WLFMC_Session()->get_session_expiration();

			if ( isset( $columns['session_id'] ) && ! $expiration && $session_expiration ) {
				$columns['expiration'] = 'FROM_UNIXTIME( %d )';
				$values[]              = apply_filters( 'wlfmc_add_customer_expiration', $session_expiration );
			}

			$query_columns = implode( ', ', array_map( 'esc_sql', array_keys( $columns ) ) );
			$query_values  = implode( ', ', array_values( $columns ) );
			$query         = "INSERT INTO $wpdb->wlfmc_wishlist_customers ( $query_columns ) VALUES ( $query_values )";

			$res = $wpdb->query( $wpdb->prepare( $query, $values ) ); // phpcs:ignore WordPress.DB

			if ( $res ) {
				$id = apply_filters( 'wlfmc_customer_correctly_created', $wpdb->insert_id );

				$customer->set_id( $id );
				$customer->apply_changes();
				$this->clear_caches( $customer );

				do_action( 'wlfmc_new_customer', $customer->get_id(), $customer );
			}
		}

		/**
		 * Read data from Database
		 *
		 * @param WLFMC_Customer $customer Customer to read from db.
		 *
		 * @throws Exception When cannot retrieve specified customer.
		 */
		public function read( &$customer ) {
			global $wpdb;
			$customer->set_defaults();
			$id    = $customer->get_id();
			$token = $customer->get_token();

			if ( ! $id && empty( $token ) ) {
				throw new Exception( esc_html__( 'Invalid Customer.', 'wc-wlfmc-wishlist' ) );
			}

			$customer_data = $customer->get_id() ? wp_cache_get( 'wlfmc-customer-id-' . $customer->get_id(), 'wlfmc-customers' ) : wp_cache_get( 'wlfmc-customer-token-' . $customer->get_token(), 'wlfmc-customers' );

			if ( ! $customer_data ) {

				$query = false;
				if ( $id ) {
					$query = $wpdb->prepare( "SELECT * FROM $wpdb->wlfmc_wishlist_customers WHERE customer_id = %d", $id );
				} elseif ( $token ) {
					$query = $wpdb->prepare( "SELECT * FROM $wpdb->wlfmc_wishlist_customers WHERE token = %s", $token );
				}

				// retrieve customer data.
				$customer_data = $wpdb->get_row( $query ); // phpcs:ignore WordPress.DB

				wp_cache_set( 'wlfmc-customer-id-' . $customer->get_id(), $customer_data, 'wlfmc-customers' );
				wp_cache_set( 'wlfmc-customer-token-' . $customer->get_token(), $customer_data, 'wlfmc-customers' );
			}

			if ( ! $customer_data ) {
				throw new Exception( esc_html__( 'Invalid customer Data.', 'wc-wlfmc-wishlist' ) );
			}

			$customer->set_props(
				array(
					'customer_id'       => $customer_data->customer_id,
					'user_id'           => $customer_data->user_id,
					'session_id'        => $customer_data->session_id ?? '',
					'first_name'        => $customer_data->first_name,
					'last_name'         => $customer_data->last_name,
					'token'             => $customer_data->token,
					'phone'             => $customer_data->phone,
					'email'             => $customer_data->email,
					'email_verified'    => $customer_data->email_verified,
					'phone_verified'    => $customer_data->phone_verified,
					'unsubscribed'      => $customer_data->unsubscribed,
					'date_added'        => $customer_data->dateadded,
					'expiration'        => $customer_data->expiration ?? '',
					'customer_meta'     => isset( $customer_data->customer_meta ) && '' !== $customer_data->customer_meta ? json_decode( $customer_data->customer_meta, true ) : null,
					'notes'             => isset( $customer_data->notes ) && '' !== $customer_data->notes ? json_decode( $customer_data->notes, true ) : null,
					'order_customer_id' => $customer_data->order_customer_id ?? '',
					'lang'              => $customer_data->lang ?? '',
				)
			);
			$customer->set_object_read( true );
		}

		/**
		 * Update customer data
		 *
		 * @param WLFMC_Customer $customer Customer to save on db, with $changes property.
		 */
		public function update( &$customer ) {

			if ( ! $customer->get_id() ) {
				return;
			}

			$changes = $customer->get_changes();

			if ( array_intersect(
				array(
					'user_id',
					'session_id',
					'first_name',
					'last_name',
					'token',
					'phone',
					'email',
					'email_verified',
					'phone_verified',
					'unsubscribed',
					'expiration',
					'date_added',
					'notes',
					'customer_meta',
					'order_customer_id',
					'lang',
				),
				array_keys( $changes )
			) ) {
				$columns = array(
					'first_name'        => '%s',
					'last_name'         => '%s',
					'email'             => '%s',
					'phone'             => '%s',
					'email_verified'    => '%d',
					'phone_verified'    => '%d',
					'unsubscribed'      => '%d',
					'customer_meta'     => '%s',
					'notes'             => '%s',
					'order_customer_id' => '%s',
					'lang'              => '%s',
					'dateadded'         => 'FROM_UNIXTIME( %d )',
				);
				$values  = array(
					$customer->get_first_name(),
					$customer->get_last_name(),
					$customer->get_email(),
					$customer->get_phone(),
					$customer->is_email_verified(),
					$customer->is_phone_verified(),
					$customer->is_unsubscribed(),
					$customer->get_customer_meta( 'edit' ),
					$customer->get_notes( 'edit' ),
					$customer->get_order_customer_id(),
					$customer->get_lang(),
					$customer->get_date_added( 'edit' ) ? $customer->get_date_added( 'edit' )->getTimestamp() : time(),
				);

				$session_id = $customer->get_session_id();

				if ( $session_id ) {
					$columns['session_id'] = '%s';
					$values[]              = apply_filters( 'wlfmc_update_customer_session_id', $session_id );
				} else {
					$columns['session_id'] = 'NULL';
				}

				$user_id = $customer->get_user_id();

				if ( $user_id ) {
					$columns['user_id'] = '%d';
					$values[]           = apply_filters( 'wlfmc_update_customer_user_id', $user_id );
				} else {
					$columns['user_id'] = 'NULL';
				}

				$expiration = $customer->get_expiration( 'edit' );

				if ( $expiration ) {
					$columns['expiration'] = 'FROM_UNIXTIME( %d )';
					$values[]              = apply_filters( 'wlfmc_update_customer_expiration', $expiration->getTimestamp() );
				} else {
					$columns['expiration'] = 'NULL';
				}

				$this->update_raw( $columns, $values, array( 'customer_id' => '%d' ), array( $customer->get_id() ) );
			}

			$customer->apply_changes();
			$this->clear_caches( $customer );

			do_action( 'wlfmc_update_customer', $customer->get_id(), $customer );
		}

		/**
		 * Delete a customer
		 *
		 * @param WLFMC_Customer $customer Customer to delete.
		 */
		public function delete( &$customer ) {
			global $wpdb;

			$id = $customer->get_id();

			if ( ! $id ) {
				return;
			}

			do_action( 'wlfmc_before_delete_customer', $customer->get_id() );

			$this->clear_caches( $customer );

			// delete customer and all its items.
			$wpdb->delete( $wpdb->wlfmc_wishlist_customers, array( 'customer_id' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->delete( $wpdb->wlfmc_wishlist_items, array( 'customer_id' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->delete( $wpdb->wlfmc_wishlists, array( 'customer_id' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->delete( $wpdb->wlfmc_wishlist_analytics, array( 'customer_id' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->delete( $wpdb->wlfmc_wishlist_offers, array( 'customer_id' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

			do_action( 'wlfmc_delete_customer', $customer->get_id() );

			$customer->set_id( 0 );

			do_action( 'wlfmc_deleted_customer', $id );
		}

		/**
		 * Delete expired session customers from DB
		 *
		 * @return void
		 */
		public function delete_expired() {
			global $wpdb;
			$wpdb->query( "DELETE FROM $wpdb->wlfmc_wishlist_items WHERE customer_id IN ( SELECT customer_id FROM $wpdb->wlfmc_wishlist_customers WHERE expiration < NOW() and user_id IS NULL AND email_verified=0 AND phone_verified=0 )" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->query( "DELETE FROM $wpdb->wlfmc_wishlists WHERE customer_id IN ( SELECT customer_id FROM $wpdb->wlfmc_wishlist_customers WHERE expiration < NOW() and user_id IS NULL AND email_verified=0 AND phone_verified=0 )" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}

		/**
		 * Unsubscribe a customer
		 *
		 * @param WLFMC_Customer $customer Customer to Unsubscribe.
		 *
		 * @return void
		 */
		public function unsubscribe( $customer ) {
			global $wpdb;

			if ( ! $customer ) {
				return;
			}

			$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_offers SET status = 'unsubscribed' WHERE customer_id = %d AND status IN ('sending' ,'not-send') ", $customer->get_id() ) );// db call ok; no-cache ok.

			$this->update_raw(
				array(
					'unsubscribed'           => 1,
					'unsubscribe_token'      => 'NULL',
					'unsubscribe_expiration' => 'NULL',
				),
				array(),
				array( 'customer_id' => '%d' ),
				array( $customer->get_id() ),
				true
			);

			$unsubscribed_users = get_option( 'wlfmc_unsubscribed_users', array() );

			if ( ! in_array( $customer->get_email(), $unsubscribed_users, true ) ) {
				$unsubscribed_users[] = $customer->get_email();
				update_option( 'wlfmc_unsubscribed_users', $unsubscribed_users );
			}

			do_action( 'wlfmc_unsubscribed_customer', $customer->get_id() );
		}

		/**
		 * Get user data by customer ,for guest generate data from email or retrieve data from db.
		 *
		 * @param WLFMC_Customer|int $customer Customer or customer_id.
		 *
		 * @return array|false
		 */
		public function get_customer_data( $customer ) {

			if ( ! empty( $customer ) && is_int( $customer ) ) {
				$customer = WLFMC_Wishlist_Factory::get_customer( $customer );
			}
			if ( ! $customer instanceof WLFMC_Customer ) {
				return false;
			}
			if ( $customer->is_session_based() ) {
				$user_email  = $customer->get_email();
				$email_parts = explode( '@', $user_email );
				if ( ! isset( $email_parts[0] ) || '' === trim( $email_parts[0] ) ) {
					return false;
				}
				$user_name  = trim( $email_parts[0] );
				$name_parts = preg_split( '/[._\-+]/', $user_name );

				$first_name = $name_parts[0] ?? $user_name;
				$last_name  = $name_parts[1] ?? '';

				$user_firstname = $customer->get_first_name();
				$user_lastname  = $customer->get_last_name();

				$user_firstname = empty( $user_firstname ) ? $first_name : $user_firstname;
				$user_lastname  = empty( $user_lastname ) ? $last_name : $user_lastname;

				return array(
					'user_id'    => false,
					'session_id' => $customer->get_session_id(),
					'user_email' => $user_email,
					'user_name'  => $user_name,
					'first_name' => $user_firstname,
					'last_name'  => $user_lastname,
					'user_phone' => $customer->get_phone(),
					'lang'       => $customer->get_lang(),
				);
			}
			$user = get_userdata( $customer->get_user_id() );
			if ( $user ) {
				return array(
					'user_id'    => $user->ID,
					'session_id' => false,
					'user_email' => $user->user_email,
					'user_name'  => $user->user_login,
					'first_name' => $user->user_firstname,
					'last_name'  => $user->user_lastname,
					'user_phone' => get_user_meta( $user->ID, '_billing_phone', true ),
					'lang'       => $customer->get_lang(),
				);
			}
			return false;
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
		 *   'unsubscribed'         // Unsubscribed<br/>
		 *   'user_search'          // String to search within user fields
		 *   'orderby'              // Any of the table columns<br/>
		 *   'order'                // ASC, DESC<br/>
		 *   'limit'                // Limit of items to retrieve<br/>
		 *   'offset'               // Offset of items to retrieve<br/>
		 *   'has_items'            // Whether to show customers with empty wishlists<br/>
		 * ].
		 *
		 * @return WLFMC_Customer[] Array of matched customers.
		 */
		public function query( $args = array() ) {
			global $wpdb;

			$default = array(
				'customer_id'      => false,
				'include_ids'      => false,
				'exclude_ids'      => false,
				'user_id'          => ( is_user_logged_in() ) ? get_current_user_id() : false,
				'session_id'       => ( ! is_user_logged_in() ) ? WLFMC_Session()->maybe_get_session_id() : false,
				'email'            => false,
				'phone'            => false,
				'token'            => false,
				'email_verified'   => false,
				'phone_verified'   => false,
				'unsubscribed'     => false,
				'user_search'      => false,
				'orderby'          => '',
				'order'            => 'DESC',
				'limit'            => false,
				'offset'           => 0,
				'has_items'        => true,
				'return_customers' => true,
			);

			$args = wp_parse_args( $args, $default );
			extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract

			$sql  = 'SELECT SQL_CALC_FOUND_ROWS l.customer_id';
			$sql .= " FROM $wpdb->wlfmc_wishlist_customers AS l";

			if ( ! empty( $user_search ) || ! empty( $email ) || ( ! empty( $orderby ) && 'user_login' === $orderby ) ) {
				$sql .= " LEFT JOIN $wpdb->users AS u ON l.`user_id` = u.ID";
			}

			if ( ! empty( $user_search ) ) {
				$sql .= " LEFT JOIN $wpdb->usermeta AS umn ON umn.`user_id` = u.`ID`";
				$sql .= " LEFT JOIN $wpdb->usermeta AS ums ON ums.`user_id` = u.`ID`";
			}

			$sql     .= ' WHERE 1';
			$sql_args = array();

			if ( ! empty( $user_id ) ) {
				$sql .= ' AND l.`user_id` = %d';

				$sql_args[] = $user_id;
			}

			if ( ! empty( $session_id ) ) {
				$sql .= ' AND l.`session_id` = %s';

				$sql_args[] = $session_id;
			}

			if ( ! empty( $user_search ) ) {
				$sql .= ' AND (
							(
								umn.`meta_key` = %s AND
								ums.`meta_key` = %s AND
								(
									u.`user_email` LIKE %s OR
									umn.`meta_value` LIKE %s OR
									ums.`meta_value` LIKE %s
								)
							) OR
							l.first_name LIKE %s OR
							l.last_name LIKE %s OR
							l.email LIKE %s OR
							l.phone LIKE %s 
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

			if ( ! empty( $customer_id ) ) {
				$sql       .= ' AND l.`customer_id` = %d';
				$sql_args[] = $customer_id;
			}

			if ( ! empty( $include_ids ) ) {
				$include_ids_sql = implode( ',', array_map( 'absint', is_string( $include_ids ) ? explode( ',', $include_ids ) : $include_ids ) );
				if ( ! empty( $include_ids_sql ) ) {
					$sql .= " AND l.`customer_id` IN ( $include_ids_sql )";
				}
			}

			if ( ! empty( $exclude_ids ) ) {
				$exclude_ids_sql = implode( ',', array_map( 'absint', is_string( $exclude_ids ) ? explode( ',', $exclude_ids ) : $exclude_ids ) );
				if ( ! empty( $exclude_ids_sql ) ) {
					$sql .= " AND l.`customer_id` NOT IN ( $exclude_ids_sql )";
				}
			}

			if ( ! empty( $token ) ) {
				$sql       .= ' AND l.`token` = %s';
				$sql_args[] = $token;
			}

			if ( ! empty( $email ) ) {
				$sql       .= ' AND ( l.`email` = %s OR u.user_email = %s )';
				$sql_args[] = esc_sql( $email );
				$sql_args[] = esc_sql( $email );
			}

			if ( ! empty( $phone ) ) {
				$sql       .= ' AND  l.`phone` = %s';
				$sql_args[] = esc_sql( $phone );
			}

			if ( ! empty( $email_verified ) ) {
				$sql       .= " AND  ( l.`email_verified` = %d  OR ( l.`user_id` IS NOT NULL AND l.`user_id` != '' ) ) ";
				$sql_args[] = esc_sql( $email_verified );
			}

			if ( ! empty( $phone_verified ) ) {
				$sql       .= ' AND  l.`phone_verified` = %d';
				$sql_args[] = esc_sql( $phone_verified );
			}

			if ( ! empty( $unsubscribed ) ) {
				$sql       .= ' AND  l.`unsubscribed` = %d';
				$sql_args[] = esc_sql( $unsubscribed );
			}

			if ( $has_items ) {
				$sql .= " AND l.`customer_id` IN ( SELECT customer_id FROM $wpdb->wlfmc_wishlist_items )";
			}

			$sql .= ' GROUP BY l.customer_id';
			$sql .= ' ORDER BY';

			if ( ! empty( $orderby ) && isset( $order ) ) {
				$sql .= ' ' . esc_sql( $orderby ) . ' ' . esc_sql( $order ) . ', ';
			}

			$sql .= ' dateadded DESC';

			if ( ! empty( $limit ) && isset( $offset ) ) {
				$sql       .= ' LIMIT %d, %d';
				$sql_args[] = $offset;
				$sql_args[] = $limit;
			}

			if ( ! empty( $sql_args ) ) {
				$sql = $wpdb->prepare( $sql, $sql_args ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}

			$customers = $wpdb->get_col( $sql ); // phpcs:ignore WordPress.DB

			if ( ! empty( $customers ) ) {
				$customers = $return_customers ? array_map( array( 'WLFMC_Wishlist_Factory', 'get_customer' ), $customers ) : $customers;
			} else {
				$customers = array();
			}

			return apply_filters( 'wlfmc_get_customers', $customers, $args );
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
		 * Retrieve current user/session; if none is found, generate it
		 *
		 * @param string|int|bool $id Pass this param when you want to retrieve a specific user/session by id or token.
		 * @param string          $context Context; when on edit context, customer will be created, if not exists.
		 *
		 * @return WLFMC_Customer|bool current user/session, or false on failure
		 */
		public function get_current_customer( $id = false, $context = 'read' ) {
			global $wpdb;
			$customer_id = false;
			$user_id     = get_current_user_id();
			$session_id  = WLFMC_Session()->maybe_get_session_id();

			if ( ! empty( $id ) && is_int( $id ) ) {
				$customer_id = $wpdb->get_var( $wpdb->prepare( "SELECT customer_id FROM $wpdb->wlfmc_wishlist_customers WHERE customer_id = %d", $id ) ); // phpcs:ignore WordPress.DB
			} elseif ( ! empty( $id ) && is_string( $id ) ) {
				$customer_id = $wpdb->get_var( $wpdb->prepare( "SELECT customer_id FROM $wpdb->wlfmc_wishlist_customers WHERE token = %s", $id ) ); // phpcs:ignore WordPress.DB
			} elseif ( $user_id ) {
				$customer_id = $wpdb->get_var( $wpdb->prepare( "SELECT customer_id FROM $wpdb->wlfmc_wishlist_customers WHERE user_id = %d", $user_id ) ); // phpcs:ignore WordPress.DB
			} elseif ( $session_id ) {
				$customer_id = $wpdb->get_var( $wpdb->prepare( "SELECT customer_id FROM $wpdb->wlfmc_wishlist_customers WHERE session_id = %s", $session_id ) ); // phpcs:ignore WordPress.DB
			}

			if ( $customer_id ) {
				return WLFMC_Wishlist_Factory::get_customer( (int) $customer_id );
			} elseif ( 'edit' === $context ) {
				return $this->generate_customer();
			} else {
				return false;
			}
		}

		/**
		 * Generate a new customer
		 *
		 * @return WLFMC_Customer|bool Brand-new customer, or false on failure
		 */
		private function generate_customer() {
			try {
				$customer = new WLFMC_Customer();
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
		 * Raw update method; useful when it is needed to update a bunch of customers
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

			// retrieves customers that will be affected by the changes.
			if ( $clear_caches ) {
				$query = "SELECT customer_id FROM $wpdb->wlfmc_wishlist_customers $query_where";
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
			$query  = "UPDATE $wpdb->wlfmc_wishlist_customers SET $query_columns $query_where";
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
		 * Generate default token for the customer
		 *
		 * @return string Customer token
		 */
		public function generate_token() {
			global $wpdb;

			$sql = "SELECT COUNT(*) as count FROM $wpdb->wlfmc_wishlist_customers WHERE `token` = %s";

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
		 * Merge multiple Customer together.
		 *
		 * @param WLFMC_Customer $main_customer main customer.
		 * @param WLFMC_Customer $merged_customer merged customer.
		 * @param bool           $change_email change email after merge or not.
		 * @return WLFMC_Customer
		 */
		public function merge_customers( $main_customer, $merged_customer, $change_email = false ) {
			global $wpdb;
			// phpcs:disable WordPress.DB
			$main_session_id     = $main_customer->get_session_id();
			$main_user_id        = $main_customer->get_user_id();
			$main_customer_id    = $main_customer->get_id();
			$main_wishlist       = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->wlfmc_wishlists WHERE is_default = 1 AND customer_id = %d", $main_customer_id ) );
			$main_waitlist       = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->wlfmc_wishlists WHERE wishlist_slug = 'waitlist' AND customer_id = %d", $main_customer_id ) );
			$main_save_for_later = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->wlfmc_wishlists WHERE wishlist_slug = 'save-for-later' AND customer_id = %d", $main_customer_id ) );

			$merged_customer_id    = $merged_customer->get_id();
			$merged_wishlist       = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->wlfmc_wishlists WHERE is_default = 1 AND customer_id = %d", $merged_customer_id ) );
			$merged_waitlist       = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->wlfmc_wishlists WHERE wishlist_slug = 'waitlist' AND customer_id = %d", $merged_customer_id ) );
			$merged_save_for_later = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->wlfmc_wishlists WHERE wishlist_slug = 'save-for-later' AND customer_id = %d", $merged_customer_id ) );
			$merged_lists          = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->wlfmc_wishlists WHERE customer_id = %d AND is_default = 0 AND wishlist_slug NOT IN ( 'waitlist', 'save-for-later')", $merged_customer_id ) );

			if ( ! empty( $merged_customer->get_order_customer_id() ) ) {
				$main_customer->set_order_customer_id( $merged_customer->get_order_customer_id() );
			}

			if ( $main_customer->is_session_based() ) {

				if ( ! $merged_customer->is_session_based() ) {

					$main_user_id = $merged_customer->get_user_id();
					$merged_customer->set_user_id( null );
					$merged_customer->save();
					$main_customer->set_user_id( $main_user_id );

				} else {

					if ( $merged_customer->is_phone_verified() && ! $main_customer->is_phone_verified() ) {
						$main_customer->set_phone( $merged_customer->get_phone() );
						$main_customer->set_phone_verified( 1 );
					}

					if ( $merged_customer->is_email_verified() ) {
						if ( ! $main_customer->is_email_verified() ) {
							$main_customer->set_email( $merged_customer->get_email() );
							$main_customer->set_email_verified( 1 );
						}
					} else {
						if ( ! empty( $merged_customer->get_email() ) && $change_email ) {
							$main_customer->set_email( $merged_customer->get_email() );
						}
					}

					if ( ! empty( $merged_customer->get_notes() ) && empty( $main_customer->get_notes() ) ) {
						$main_customer->set_notes( $merged_customer->get_notes() );
					}

					if ( ! empty( $merged_customer->get_first_name() ) && empty( $main_customer->get_first_name() ) ) {
						$main_customer->set_first_name( $merged_customer->get_first_name() );
					}

					if ( ! empty( $merged_customer->get_last_name() ) && empty( $main_customer->get_last_name() ) ) {
						$main_customer->set_last_name( $merged_customer->get_last_name() );
					}

					$expiration = $main_customer->get_expiration( 'edit' );

					if ( $expiration ) {
						$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlists SET expiration = FROM_UNIXTIME( %d ) WHERE customer_id = %d", $expiration->getTimestamp(), $merged_customer_id ) );
					}
				}
			}

			$main_customer->save();

			$all_items   = $wpdb->get_col( $wpdb->prepare( "SELECT i.ID FROM $wpdb->wlfmc_wishlist_items AS i LEFT JOIN $wpdb->wlfmc_wishlists AS l ON l.ID = i.wishlist_id WHERE l.customer_id = %d", $merged_customer_id ) ); // phpcs:ignore WordPress.DB
			$exclude_ids = array();

			if ( ! empty( $merged_lists ) ) {
				// merge multi-list.
				$merged_lists = implode( ',', array_map( 'esc_sql', $merged_lists ) );
				if ( ! empty( $merged_lists ) ) {
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlists SET customer_id = %d , session_id = %s WHERE customer_id = %d AND ID IN ( $merged_lists )", $main_customer_id, $main_session_id, $merged_customer_id ) );
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_items SET customer_id = %d WHERE customer_id = %d AND wishlist_id IN ( $merged_lists )", $main_customer_id, $merged_customer_id ) );
					// merge payment by lists.
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_analytics SET customer_id = %d WHERE customer_id = %d AND type !='add-to-list' AND order_id IS NOT NULL AND order_id != '' AND wishlist_id IN ( $merged_lists )", $main_customer_id, $merged_customer_id ) );
					// merge offers.
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_offers SET customer_id = %d WHERE customer_id = %d AND wishlist_id IN ( $merged_lists )", $main_customer_id, $merged_customer_id ) );
				}
			}

			if ( ! empty( $merged_save_for_later ) ) {
				// merge save for later.
				if ( ! empty( $main_save_for_later ) ) {
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_items SET wishlist_id = %d, customer_id = %d WHERE customer_id = %d AND wishlist_id = %d", $main_save_for_later, $main_customer_id, $merged_customer_id, $merged_save_for_later ) );
					// merge payment by save for later.
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_analytics SET  wishlist_id = %d, customer_id = %d WHERE customer_id = %d AND wishlist_id = %d AND type !='add-to-list' AND order_id IS NOT NULL AND order_id != ''", $main_save_for_later, $main_customer_id, $merged_customer_id, $merged_save_for_later ) );
					// merge offers.
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_offers SET customer_id = %d, wishlist_id = %d WHERE customer_id = %d AND wishlist_id = %d ", $main_customer_id, $main_save_for_later, $merged_customer_id, $merged_save_for_later ) );
				} else {
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlists SET customer_id = %d , session_id = %s WHERE customer_id = %d AND ID = %d ", $main_customer_id, $main_session_id, $merged_customer_id, $merged_save_for_later ) );
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_items SET customer_id = %d WHERE customer_id = %d AND wishlist_id = %d", $main_customer_id, $merged_customer_id, $merged_save_for_later ) );
					// merge payment by save for later.
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_analytics SET  customer_id = %d WHERE customer_id = %d AND wishlist_id = %d AND type !='add-to-list' AND order_id IS NOT NULL AND order_id != ''", $main_customer_id, $merged_customer_id, $merged_save_for_later ) );
					// merge offers.
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_offers SET customer_id = %d WHERE customer_id = %d AND wishlist_id = %d ", $main_customer_id, $merged_customer_id, $merged_save_for_later ) );
				}
			}

			if ( ! empty( $merged_wishlist ) ) {
				// merge wishlist.
				if ( ! empty( $main_wishlist ) ) {
					// ignore exists product in main wishlist.
					$product_ids     = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT prod_id FROM $wpdb->wlfmc_wishlist_items WHERE wishlist_id = %d AND customer_id = %d", $main_wishlist, $main_customer_id ) );
					$exclude_ids     = array_merge( $exclude_ids, $product_ids );
					$product_id_list = implode( ',', array_map( 'esc_sql', $product_ids ) );
					$where           = '';
					if ( ! empty( $product_id_list ) ) {
						$where .= "AND prod_id NOT IN( $product_id_list )";
					}
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_items SET wishlist_id = %d, customer_id = %d WHERE customer_id = %d AND wishlist_id = %d $where", $main_wishlist, $main_customer_id, $merged_customer_id, $merged_wishlist ) );
					// merge payment by wishlist.
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_analytics SET  wishlist_id = %d, customer_id = %d WHERE customer_id = %d AND wishlist_id = %d AND type !='add-to-list' AND order_id IS NOT NULL AND order_id != ''", $main_wishlist, $main_customer_id, $merged_customer_id, $merged_wishlist ) );
					// merge offers.
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_offers SET customer_id = %d, wishlist_id = %d WHERE customer_id = %d AND wishlist_id = %d ", $main_customer_id, $main_wishlist, $merged_customer_id, $merged_wishlist ) );
				} else {
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlists SET customer_id = %d , session_id = %s WHERE customer_id = %d AND ID = %d", $main_customer_id, $main_session_id, $merged_customer_id, $merged_wishlist ) );
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_items SET customer_id = %d WHERE customer_id = %d AND wishlist_id = %d", $main_customer_id, $merged_customer_id, $merged_wishlist ) );
					// merge payment by wishlist.
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_analytics SET  customer_id = %d WHERE customer_id = %d AND wishlist_id = %d AND type !='add-to-list' AND order_id IS NOT NULL AND order_id != ''", $main_customer_id, $merged_customer_id, $merged_wishlist ) );
					// merge offers.
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_offers SET customer_id = %d WHERE customer_id = %d AND wishlist_id = %d ", $main_customer_id, $merged_customer_id, $merged_wishlist ) );
				}
			}

			if ( ! empty( $merged_waitlist ) ) {
				// merge waitlist.
				if ( ! empty( $main_waitlist ) ) {
					// ignore exists product in main wishlist.
					$product_ids     = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT prod_id FROM $wpdb->wlfmc_wishlist_items WHERE wishlist_id = %d AND customer_id = %d", $main_waitlist, $main_customer_id ) );
					$exclude_ids     = array_merge( $exclude_ids, $product_ids );
					$product_id_list = implode( ',', array_map( 'esc_sql', $product_ids ) );
					$where           = '';
					if ( ! empty( $product_id_list ) ) {
						$where .= "AND prod_id NOT IN( $product_id_list )";
					}
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_items SET  wishlist_id = %d, customer_id = %d WHERE customer_id = %d AND wishlist_id = %d $where", $main_waitlist, $main_customer_id, $merged_customer_id, $merged_waitlist ) );
					if ( ! empty( $product_id_list ) ) {
						$wpdb->query(
							"UPDATE $wpdb->wlfmc_wishlist_items AS main
									JOIN (
									    SELECT prod_id,
											IF(SUM(on_sale) > 0, 1, 0) AS on_sale,
											IF(SUM(price_change) > 0, 1, 0) AS price_change,
											IF(SUM(low_stock) > 0, 1, 0) AS low_stock,
											IF(SUM(back_in_stock) > 0, 1, 0) AS back_in_stock
									    FROM $wpdb->wlfmc_wishlist_items
									    WHERE wishlist_id IN ($main_waitlist, $merged_waitlist)
									    AND customer_id IN ($main_customer_id, $merged_customer_id)
									    AND prod_id IN ($product_id_list)
									    GROUP BY prod_id
									) AS sub ON main.prod_id = sub.prod_id
									SET main.on_sale = sub.on_sale,
									    main.low_stock = sub.low_stock,
									    main.price_change = sub.price_change,
									    main.back_in_stock = sub.back_in_stock
									WHERE main.wishlist_id = $main_waitlist
									AND main.customer_id = $main_customer_id;"
						);
					}
					// merge payment by waitlist.
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_analytics SET  wishlist_id = %d, customer_id = %d WHERE customer_id = %d AND wishlist_id = %d AND type !='add-to-list' AND order_id IS NOT NULL AND order_id != ''", $main_waitlist, $main_customer_id, $merged_customer_id, $merged_waitlist ) );
					// merge offers.
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_offers SET customer_id = %d, wishlist_id = %d WHERE customer_id = %d AND wishlist_id = %d ", $main_customer_id, $main_waitlist, $merged_customer_id, $merged_waitlist ) );
				} else {
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlists SET customer_id = %d , session_id = %s WHERE customer_id = %d AND ID = %d", $main_customer_id, $main_session_id, $merged_customer_id, $merged_waitlist ) );
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_items SET customer_id = %d WHERE customer_id = %d AND wishlist_id = %d", $main_customer_id, $merged_customer_id, $merged_waitlist ) );
					// merge payment by waitlist.
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_analytics SET customer_id = %d WHERE customer_id = %d AND wishlist_id = %d AND type !='add-to-list' AND order_id IS NOT NULL AND order_id != ''", $main_customer_id, $merged_customer_id, $merged_waitlist ) );
					// merge offers.
					$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_offers SET customer_id = %d WHERE customer_id = %d AND wishlist_id = %d ", $main_customer_id, $merged_customer_id, $merged_waitlist ) );
				}
			}

			if ( $main_user_id > 0 ) {
				$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlist_items SET user_id = %d WHERE customer_id = %d ", $main_user_id, $main_customer_id ) );
				$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wlfmc_wishlists SET user_id = %d , session_id = NULL, expiration = NULL  WHERE customer_id = %d", $main_user_id, $main_customer_id ) );
			}

			$orders = wc_get_orders(
				array(
					'meta_key'     => 'wlfmc_customer_id',
					'meta_value'   => $merged_customer_id,
					'meta_compare' => '=',
				)
			);

			if ( ! empty( $orders ) ) {
				foreach ( $orders as $order ) {
					$order->update_meta_data( 'wlfmc_customer_id', $main_customer_id );
					$order->save();
				}
			}

			if ( ! empty( $all_items ) && apply_filters( 'wlfmc_add_to_analytics_after_merge_customers', true ) ) {
				$product_id_exclude = implode( ',', array_map( 'esc_sql', $exclude_ids ) );
				$where              = ' 1=1 ';
				if ( ! empty( $product_id_exclude ) ) {
					$where .= "AND i.prod_id NOT IN( $product_id_exclude )";
				}
				$items_ids = implode( ',', array_map( 'esc_sql', $all_items ) );
				if ( ! empty( $items_ids ) ) {
					$where .= "AND i.ID IN( $items_ids )";
					$wpdb->query(
						"INSERT INTO $wpdb->wlfmc_wishlist_analytics 
	                   ( prod_id , quantity, wishlist_id, customer_id, list_type, price, currency ) 
						SELECT i.prod_id, 1 as quantity ,i.wishlist_id,i.customer_id,
					       	CASE 
						    WHEN w.is_default=1 THEN 'wishlist'
						    WHEN w.wishlist_slug='waitlist' THEN 'waitlist'
						    WHEN w.wishlist_slug='save-for-later' THEN 'save-for-later'
						    ELSE 'lists' END AS list_type, 
						    i.original_price, i.original_currency 
						FROM $wpdb->wlfmc_wishlist_items as i
						INNER JOIN $wpdb->wlfmc_wishlists as w ON i.wishlist_id = w.ID 
						WHERE $where "
					);// phpcs:ignore WordPress.DB
				}
			}

			do_action( 'wlfmc_before_delete_merged_customers', $main_customer_id, $merged_customer_id );

			// phpcs:enable WordPress.DB
			$this->delete( $merged_customer );

			return $main_customer;
		}
		/**
		 * Clear customer related caches
		 *
		 * @param WLFMC_Customer|int|string $customer .
		 *
		 * @return void
		 */
		protected function clear_caches( &$customer ) {
			if ( $customer instanceof WLFMC_Customer ) {
				$id    = $customer->get_id();
				$token = $customer->get_token();
			} elseif ( intval( $customer ) ) {
				$id       = $customer;
				$customer = wlfmc_get_customer( $customer );
				$token    = $customer ? $customer->get_token() : false;
			} else {
				$token    = $customer;
				$customer = wlfmc_get_customer( $customer );
				$id       = $customer ? $customer->get_id() : false;
			}

			wp_cache_delete( 'wlfmc-customer-id-' . $id, 'wlfmc-customers' );
			wp_cache_delete( 'wlfmc-customer-token-' . $token, 'wlfmc-customers' );
		}
	}
}
