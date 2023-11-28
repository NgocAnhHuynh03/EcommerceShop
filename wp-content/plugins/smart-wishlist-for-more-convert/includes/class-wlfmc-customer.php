<?php
/**
 * Customer Wishlist Class
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WLFMC_Customer' ) ) {
	/**
	 * Class WLFMC_Customer
	 */
	class WLFMC_Customer extends WC_Data implements ArrayAccess {

		/**
		 * Customer token (Unique identifier)
		 *
		 * @var string
		 */
		protected $token = '';

		/**
		 * Customer Data array
		 *
		 * @var array
		 */
		protected $data;

		/**
		 * Stores meta in cache for future reads.
		 *
		 * A group must be set to enable caching.
		 *
		 * @var string
		 */
		protected $cache_group = 'wlfmc-customers';

		/* === MAGIC METHODS === */

		/**
		 * Constructor
		 *
		 * @param int|string|WLFMC_Customer $customer Customer identifier.
		 *
		 * @throws Exception When not able to load Data Store class.
		 */
		public function __construct( $customer = 0 ) {
			// set default values.
			$this->data = array(
				'user_id'                => 0,
				'order_customer_id'      => '',
				'lang'                   => '',
				'session_id'             => '',
				'first_name'             => '',
				'last_name'              => '',
				'email'                  => '',
				'phone'                  => '',
				'token'                  => '',
				'email_verified'         => 0,
				'phone_verified'         => 0,
				'expiration'             => '',
				'unsubscribed'           => 0,
				'unsubscribe_token'      => '',
				'unsubscribe_expiration' => '',
				'notes'                  => '',
				'customer_meta'          => '',
			);

			parent::__construct();
			if ( is_numeric( $customer ) && $customer > 0 ) {
				$this->set_id( $customer );
			} elseif ( $customer instanceof self ) {
				$this->set_id( $customer->get_id() );
			} elseif ( is_string( $customer ) ) {
				$this->set_token( $customer );
			} else {
				$this->set_object_read( true );
			}

			$this->data_store = WC_Data_Store::load( 'wlfmc-customer' );
			if ( $this->get_id() > 0 || ! empty( $this->get_token() ) ) {
				$this->data_store->read( $this );
			}

		}

		/* === HELPERS === */

		/**
		 * Check whether customer was created for unauthenticated user
		 *
		 * @return bool
		 */
		public function is_session_based() {
			return (bool) $this->get_session_id();
		}

		/**
		 * Check whether customer was created for authenticated user
		 *
		 * @return bool
		 */
		public function has_owner() {
			return (bool) $this->get_user_id();
		}

		/**
		 * Check if current user is owner of this customer (works both for authenticated users & guests)
		 *
		 * @param string|int|bool $current_user Optional user identifier, in the form of a User id or session id; false for default.
		 *
		 * @return bool
		 */
		public function is_current_user_owner( $current_user = false ) {
			$user_id    = $this->get_user_id();
			$session_id = $this->get_session_id();

			if ( $current_user && ( (int) $current_user === $user_id || $current_user === $session_id ) ) {
				return true;
			}

			if ( $this->has_owner() && is_user_logged_in() && get_current_user_id() === $user_id ) {
				return true;
			}

			if ( $this->is_session_based() && WLFMC_Session()->maybe_get_session_id() === $session_id ) {
				return true;
			}

			return false;
		}

		/**
		 * Check whether customer email verified
		 *
		 * @param string $context Context.
		 *
		 * @return bool customer  email verified
		 */
		public function is_email_verified( $context = 'view' ) {
			return (bool) $this->get_prop( 'email_verified', $context );
		}

		/**
		 * Check whether customer phone verified
		 *
		 * @param string $context Context.
		 *
		 * @return bool customer phone verified
		 */
		public function is_phone_verified( $context = 'view' ) {
			return (bool) $this->get_prop( 'phone_verified', $context );
		}

		/**
		 * Check whether customer unsubscribed
		 *
		 * @param string $context Context.
		 *
		 * @return bool customer unsubscribed
		 */
		public function is_unsubscribed( $context = 'view' ) {
			return (bool) $this->get_prop( 'unsubscribed', $context );
		}

		/* === GETTERS === */

		/**
		 * Get customer token
		 *
		 * @return string customer unique token
		 */
		public function get_token() {
			return $this->token;
		}

		/**
		 * Get customer unsubscribe token
		 *
		 * @param string $context Context.
		 *
		 * @return string customer unique unsubscribe token
		 */
		public function get_unsubscribe_token( $context = 'view' ) {
			return $this->get_prop( 'unsubscribe_token', $context );
		}

		/**
		 * Get order customer ids
		 *
		 * @param string $context Context.
		 *
		 * @return string order customer ids ( separate with comma )
		 */
		public function get_order_customer_id( $context = 'view' ) {
			return $this->get_prop( 'order_customer_id', $context );
		}

		/**
		 * Get language
		 *
		 * @param string $context Context.
		 *
		 * @return string language
		 */
		public function get_lang( $context = 'view' ) {
			return $this->get_prop( 'lang', $context );
		}

		/**
		 * Get owner id
		 *
		 * @param string $context Context.
		 *
		 * @return int customer owner id
		 */
		public function get_user_id( $context = 'view' ) {
			return (int) $this->get_prop( 'user_id', $context );
		}

		/**
		 * Get session id
		 *
		 * @param string $context Context.
		 *
		 * @return int customer owner id
		 */
		public function get_session_id( $context = 'view' ) {
			return $this->get_prop( 'session_id', $context );
		}

		/**
		 * Get customer first name
		 *
		 * @param string $context Context.
		 *
		 * @return string customer first name
		 */
		public function get_first_name( $context = 'view' ) {
			return wc_clean( stripslashes( $this->get_prop( 'first_name', $context ) ) );
		}

		/**
		 * Get customer last name
		 *
		 * @param string $context Context.
		 *
		 * @return string customer last name
		 */
		public function get_last_name( $context = 'view' ) {
			return wc_clean( stripslashes( $this->get_prop( 'last_name', $context ) ) );
		}

		/**
		 * Get customer email
		 *
		 * @param string $context Context.
		 *
		 * @return string customer last name
		 */
		public function get_email( $context = 'view' ) {
			return $this->get_prop( 'email', $context );
		}

		/**
		 * Get customer phone
		 *
		 * @param string $context Context.
		 *
		 * @return string customer phone
		 */
		public function get_phone( $context = 'view' ) {
			return $this->get_prop( 'phone', $context );
		}

		/**
		 * Return customer_meta
		 *
		 * @param string $context Context.
		 *
		 * @return array|string customer meta
		 */
		public function get_customer_meta( $context = 'view' ) {
			$meta = $this->get_prop( 'customer_meta', 'edit' );
			if ( $meta && 'view' === $context ) {
				return json_decode( $meta, true );
			} else {
				return $meta;
			}
		}

		/**
		 * Return notes
		 *
		 * @param string $context Context.
		 *
		 * @return mixed|null|string notes
		 */
		public function get_notes( $context = 'view' ) {
			$notes = $this->get_prop( 'notes', 'edit' );
			if ( $notes && 'view' === $context ) {
				return json_decode( $notes, true );
			} else {
				return $notes;
			}
		}

		/**
		 * Get customer date added
		 *
		 * @param string $context Context.
		 *
		 * @return WC_DateTime|string customer date of creation
		 */
		public function get_date_added( $context = 'view' ) {
			$date_added = $this->get_prop( 'date_added', $context );

			if ( $date_added && 'view' === $context ) {
				return $date_added->date_i18n( 'Y-m-d H:i:s' );
			}

			return $date_added;
		}

		/**
		 * Get formatted customer date added
		 *
		 * @param string $format Date format (if empty, WP date format will be applied).
		 *
		 * @return string customer date of creation
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
		 * Get customer date expiration
		 *
		 * @param string $context Context.
		 *
		 * @return WC_DateTime|string customer date of expiration
		 */
		public function get_expiration( $context = 'view' ) {
			$expiration = $this->get_prop( 'expiration', $context );

			if ( $expiration && 'view' === $context ) {
				return $expiration->date_i18n( 'Y-m-d H:i:s' );
			}

			return $expiration;
		}

		/**
		 * Get customer unsubscribe expiration date
		 *
		 * @param string $context Context.
		 *
		 * @return WC_DateTime|string unsubscribe expiration date
		 */
		public function get_unsubscribe_expiration( $context = 'view' ) {
			$expiration = $this->get_prop( 'unsubscribe_expiration', $context );

			if ( $expiration && 'view' === $context ) {
				return $expiration->date_i18n( 'Y-m-d H:i:s' );
			}

			return $expiration;
		}

		/**
		 * Get formatted customer expiration added
		 *
		 * @param string $format Date format (if empty, WP date format will be applied).
		 *
		 * @return string Wishlist date of expiration
		 */
		public function get_expiration_formatted( $format = '' ) {
			$expiration = $this->get_expiration( 'edit' );

			if ( $expiration ) {
				$format = $format ? $format : get_option( 'date_format' );
				return $expiration->date_i18n( $format );
			}

			return '';
		}

		/* === SETTERS === */

		/**
		 * Set customer token
		 *
		 * @param string $token Customer unique token.
		 */
		public function set_token( $token ) {
			$this->token = (string) $token;
		}

		/**
		 * Set customer unsubscribe token
		 *
		 * @param string $token unsubscribe unique token.
		 */
		public function set_unsubscribe_token( $token ) {
			$this->set_prop( 'unsubscribe_token', $token );
		}

		/**
		 * Set order customer id
		 *
		 * @param string $ids order customer ids ( separate with comma ).
		 */
		public function set_order_customer_id( $ids ) {
			$ids = $this->add_int_to_string( $this->get_order_customer_id(), $ids );
			$this->set_prop( 'order_customer_id', $ids );
		}

		/**
		 * Set language
		 *
		 * @param string $lang language.
		 */
		public function set_lang( $lang ) {
			$this->set_prop( 'lang', $lang );
		}

		/**
		 * Add integer to comma-separated string if not already present.
		 *
		 * @param array|string|int $original_string The original comma-separated string.
		 * @param array|string|int $string_to_add The string to add.
		 * @return string The updated comma-separated string.
		 */
		private function add_int_to_string( $original_string, $string_to_add ) {
			if ( '' === $string_to_add ) {
				return $original_string;
			}

			$original_array      = array_filter( array_map( 'absint', ( is_array( $original_string ) ? $original_string : explode( ',', $original_string ) ) ) );
			$string_to_add_array = array_filter( array_map( 'absint', ( is_array( $string_to_add ) ? $string_to_add : explode( ',', $string_to_add ) ) ) );

			$updated_array = array_unique( array_merge( $original_array, $string_to_add_array ) );

			if ( empty( $updated_array ) ) {
				return '';
			}

			return implode( ',', $updated_array );
		}

		/**
		 * Set user id
		 *
		 * @param int|null $user_id User id.
		 */
		public function set_user_id( $user_id ) {
			$this->set_prop( 'user_id', $user_id );
			if ( $user_id > 0 ) {
				$this->set_prop( 'expiration', null );
				$this->set_prop( 'session_id', null );
			}
		}

		/**
		 * Set session id
		 *
		 * @param string $session_id Session id.
		 */
		public function set_session_id( $session_id ) {
			$this->set_prop( 'session_id', $session_id );
		}

		/**
		 * Set customer first name
		 *
		 * @param string $first_name customer first name.
		 */
		public function set_first_name( $first_name ) {
			$this->set_prop( 'first_name', $first_name );
		}

		/**
		 * Set customer last name
		 *
		 * @param string $last_name customer last name.
		 */
		public function set_last_name( $last_name ) {
			$this->set_prop( 'last_name', $last_name );
		}

		/**
		 * Set customer phone
		 *
		 * @param string $phone customer phone.
		 */
		public function set_phone( $phone ) {
			$this->set_prop( 'phone', $phone );
		}

		/**
		 * Set customer email
		 *
		 * @param string $email customer phone.
		 */
		public function set_email( $email ) {
			$this->set_prop( 'email', $email );
		}

		/**
		 * Set customer email verified
		 *
		 * @param bool $state verified state.
		 */
		public function set_email_verified( bool $state ) {
			$this->set_prop( 'email_verified', $state );
			if ( $state ) {
				$this->set_prop( 'expiration', null );
			}
		}

		/**
		 * Set customer meta
		 *
		 * @param array $meta customer meta.
		 *
		 * @return void
		 */
		public function set_customer_meta( $meta ) {
			$this->set_prop( 'customer_meta', $meta ? wp_json_encode( $meta ) : null );
		}

		/**
		 * Set customer notes
		 *
		 * @param array $notes customer notes.
		 *
		 * @return void
		 */
		public function set_notes( $notes ) {
			$this->set_prop( 'notes', $notes ? wp_json_encode( $notes ) : null );
		}

		/**
		 * Set customer phone verified
		 *
		 * @param bool $state verified state.
		 */
		public function set_phone_verified( bool $state ) {
			$this->set_prop( 'phone_verified', $state );
			if ( $state ) {
				$this->set_prop( 'expiration', null );
			}
		}

		/**
		 * Set customer subscribe state
		 *
		 * @param bool $state subscribe state.
		 */
		public function set_unsubscribed( bool $state ) {
			$this->set_prop( 'unsubscribed', $state );
			if ( $state ) {
				$this->set_prop( 'unsubscribe_token', null );
				$this->set_prop( 'unsubscribe_expiration', null );
			}
		}

		/**
		 * Set customer date added
		 *
		 * @param int|string $date_added Wishlist date of creation (timestamp or date).
		 */
		public function set_date_added( $date_added ) {
			$this->set_date_prop( 'date_added', $date_added );
		}

		/**
		 * Set customer date expiration
		 *
		 * @param int|string $expiration Wishlist date of expiration (timestamp or date).
		 */
		public function set_expiration( $expiration ) {
			$this->set_date_prop( 'expiration', $expiration );
		}

		/**
		 * Set customer unsubscribe date expiration
		 *
		 * @param int|string $expiration unsubscribe date of expiration (timestamp or date).
		 */
		public function set_unsubscribe_expiration( $expiration ) {
			$this->set_date_prop( 'unsubscribe_expiration', $expiration );
		}

		/* === CRUD METHODS === */

		/**
		 * Save data to the database.
		 *
		 * @return int customer id
		 */
		public function save() {
			if ( $this->data_store ) {
				// Trigger action before saving to the DB. Allows you to adjust object props before save.
				do_action( 'woocommerce_before_' . $this->object_type . '_object_save', $this, $this->data_store );

				if ( $this->get_id() ) {
					$this->data_store->update( $this );
				} else {
					$this->data_store->create( $this );
				}
			}
			return $this->get_id();
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

			if ( '' !== $offset && false !== strpos( $offset, 'customer_' ) ) {
				$offset = str_replace( 'customer_', '', $offset );
			}

			if ( 'dateadded' === $offset ) {
				$offset = 'date_added';
			}

			if ( 'firstname' === $offset ) {
				$offset = 'first_name';
			}

			if ( 'lastname' === $offset ) {
				$offset = 'last_name';
			}

			return apply_filters( 'wlfmc_customer_map_legacy_offsets', $offset, $legacy_offset );
		}
	}
}
