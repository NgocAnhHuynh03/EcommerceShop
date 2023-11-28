<?php
/**
 * Main Class
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.6.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WLFMC' ) ) {
	/**
	 * WooCommerce Wishlist
	 */
	class WLFMC {
		/**
		 * Single instance of the class
		 *
		 * @var WLFMC
		 */
		protected static $instance;

		/**
		 * Plugin version
		 *
		 * @var string
		 */
		public $version = '1.7.0';

		/**
		 * Plugin database version
		 *
		 * @var string
		 */
		public $db_version = '1.3.1';

		/**
		 * Store class WLFMC_Install.
		 *
		 * @var object
		 * @access private
		 */
		protected $wlfmc_install;

		/**
		 * Last operation token
		 *
		 * @var string
		 */
		public $last_operation_token;

		/**
		 * Query string parameter used to generate Wishlist urls
		 *
		 * @var string
		 */
		public $wishlist_param = 'wishlist-action';

		/**
		 * Automation email
		 *
		 * @var WLFMC_Automation_Emails | WLFMC_Automation_Emails_Premium
		 */
		private $wlfmc_automation_emails;

		/**
		 * Wishlist Frontend
		 *
		 * @var WLFMC_Frontend
		 */
		private $wlfmc_frontend;

		/**
		 * Wishlist Cron
		 *
		 * @var WLFMC_Cron
		 */
		private $wlfmc_cron;

		/**
		 * Wishlist Session
		 *
		 * @var WLFMC_Session
		 */
		private $wlfmc_session;

		/**
		 * Wishlist Elementor Widgets
		 *
		 * @var WLFMC_Elementor
		 */
		private $wlfmc_elementor;

		/**
		 * Wishlist Gutenberg Widgets
		 *
		 * @var WLFMC_Gutenberg
		 */
		private $wlfmc_gutenberg;

		/**
		 * Wishlist Admin
		 *
		 * @var WLFMC_Admin
		 */
		private $wlfmc_admin;

		/**
		 * Wishlist Admin Notices
		 *
		 * @var WLFMC_Admin_Notice
		 */
		private $wlfmc_admin_notice;

		/**
		 * Wishlist Automation Admin
		 *
		 * @var WLFMC_Automation_Admin
		 */
		private $wlfmc_automation_admin;

		/**
		 * Wishlist analytics
		 *
		 * @var WLFMC_Analytics
		 */
		private $wlfmc_analytics;

		/**
		 * Returns single instance of the class
		 *
		 * @return WLFMC
		 */
		public static function get_instance(): WLFMC {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @version 1.0.1
		 */
		public function __construct() {

			// register data stores.
			add_filter( 'woocommerce_data_stores', array( $this, 'register_data_stores' ) );

			define( 'WLFMC_VERSION', $this->version );
			define( 'WLFMC_DB_VERSION', $this->db_version );

			// init install class.
			$this->wlfmc_install = WLFMC_Install();

			// init  automation  emails.
			$this->wlfmc_automation_emails = WLFMC_Automation_Emails();

			// init frontend class.
			$this->wlfmc_frontend = WLFMC_Frontend();

			// init crons.
			$this->wlfmc_cron = WLFMC_Cron();

			// init session.
			$this->wlfmc_session = WLFMC_Session();

			if ( class_exists( '\Elementor\Plugin' ) ) {
				$this->wlfmc_elementor = WLFMC_Elementor();
			}

			$this->wlfmc_gutenberg = WLFMC_Gutenberg();

			$this->wlfmc_analytics = WLFMC_Analytics();

			// init admin handling.
			if ( is_admin() ) {
				$this->wlfmc_admin            = WLFMC_Admin();
				$this->wlfmc_admin_notice     = WLFMC_Admin_Notice();
				$this->wlfmc_automation_admin = WLFMC_Automation_Admin();
			}

			// add rewrite rule.
			add_action( 'init', array( $this, 'add_rewrite_rules' ), 0 );
			add_filter( 'query_vars', array( $this, 'add_public_query_var' ) );

			// Polylang integration.
			add_filter( 'pll_translation_url', array( $this, 'get_pll_wishlist_url' ), 10, 1 );

			// remove from wishlist on order status changed.
			add_action( 'woocommerce_order_status_processing', array( $this, 'action_remove_from_wishlist' ) );
			add_action( 'woocommerce_order_status_completed', array( $this, 'action_remove_from_wishlist' ) );

			// remove from lists after product removed.
			add_action( 'woocommerce_before_delete_product', array( $this, 'delete_products' ), 10, 1 );

			// update wishlist data.
			add_filter( 'wlfmc_update_wishlists_data', array( $this, 'update_wishlists_data' ) );
			add_action( 'wp_logout', array( $this, 'need_update_wishlist_data' ), 10 );
			add_action( 'wp_login', array( $this, 'need_update_wishlist_data' ), 10 );
			add_action( 'wlfmc_product_added_to_cart', array( $this, 'need_update_wishlist_data' ) );

			add_filter( 'wlfmc_wishlist_url', array( $this, 'wishlist_url' ), 10, 3 );

			add_action( 'delete_user', array( $this, 'after_delete_user' ), 10 );

			// save customer language.
			add_action( 'wlfmc_add_customer_lang', array( $this, 'add_language' ) );
			add_action( 'wpml_language_has_switched', array( $this, 'save_language' ) );
			// add args to url.
			add_filter('icl_lang_sel_copy_parameters', array( $this, 'preserve_query_args_in_translated_url' ));
			// disable change Settings adn automation and campaign on different current language with default language.
			add_filter( 'mct_options_can_update', array( $this, 'has_access' ), 0 );
			add_filter( 'mct_options_can_set_default_values', array( $this, 'has_access' ), 0 );
			add_filter( 'mct_options_can_reset', array( $this, 'has_access' ), 0 );
			add_filter( 'wlfmc_can_create_automation', array( $this, 'has_access' ), 0 );
			add_filter( 'wlfmc_can_update_automation', array( $this, 'has_access' ), 0 );
			add_filter( 'wlfmc_can_delete_automation', array( $this, 'has_access' ), 0 );
			add_filter( 'wlfmc_can_delete_recipients', array( $this, 'has_access' ), 0 );
			add_filter( 'wlfmc_can_duplicate_campaign', array( $this, 'has_access' ), 0 );
			add_filter( 'wlfmc_can_cancel_campaign', array( $this, 'has_access' ), 0 );
			add_filter( 'wlfmc_can_send_campaign', array( $this, 'has_access' ), 0 );
			add_filter( 'wlfmc_can_create_campaign', array( $this, 'has_access' ), 0 );
			add_filter( 'wlfmc_can_update_campaign', array( $this, 'has_access' ), 0 );
			add_filter( 'wlfmc_can_delete_campaign_item', array( $this, 'has_access' ), 0 );
			add_filter( 'wlfmc_can_delete_automation_item', array( $this, 'has_access' ), 0 );
			add_filter( 'wlfmc_can_duplicate_automation', array( $this, 'has_access' ), 0 );
		}

		/**
		 * Preserve query arguments in translated URL
		 * @param array $args copy parameters
		 * @return array
		 */
		public function preserve_query_args_in_translated_url($args) {
			return array_unique( array_merge( $args, array( $this->wishlist_param, 'wishlist_id', 'pagenum' ) ) );
		}

		/**
		 * Save customer language after change it.
		 *
		 * @return void
		 */
		public function save_language() {
			$customer = WLFMC_Wishlist_Factory::get_current_customer( false );
			if ( $customer ) {
				$current_lang = apply_filters( 'wpml_current_language', $customer->get_lang() );
				if ( $current_lang !== $customer->get_lang() ) {
					$customer->set_lang( apply_filters( 'wpml_current_language', $customer->get_lang() ) );
					$customer->save();
				}
			}
		}

		/**
		 * Add current language to customer.
		 *
		 * @param string|null $lang current language.
		 * @return mixed|null
		 */
		public function add_language( $lang ) {
			return apply_filters( 'wpml_current_language', $lang );
		}

		/**
		 * Disable change Settings adn automation and campaign on different current language with default language.
		 *
		 * @param bool $access access state.
		 *
		 * @return bool
		 */
		public function has_access( $access ) {
			if ( function_exists( 'wpml_object_id_filter' ) || function_exists( 'icl_get_languages' ) ) {
				return apply_filters( 'wpml_current_language', null ) === apply_filters( 'wpml_default_language', null );
			}
			return $access;
		}

		/**
		 * Delete Customer after delete user.
		 *
		 * @param int $user_id user id.
		 * @return void
		 */
		public function after_delete_user( $user_id ) {
			$customer_id = wlfmc_get_customer_id_by_user( (int) $user_id );

			if ( $customer_id > 0 ) {
				WLFMC_Wishlist_Factory::delete_customer( $customer_id );
			}
		}

		/* === ITEMS METHODS === */

		/**
		 * Add a product in the wishlist.
		 *
		 * @param array $atts Array of parameters; when not passed, params will be searched in $_REQUEST.
		 *
		 * @return array
		 * @throws Exception|WLFMC_Exception When an error occurs with Add to Wishlist operation.
		 * @version 1.3.3
		 */
		public function add( $atts = array() ) {
			$defaults = array(
				'add_to_wishlist'     => 0,
				'wishlist_id'         => 0,
				'quantity'            => 1,
				'user_id'             => false,
				'dateadded'           => '',
				'wishlist_name'       => '',
				'wishlist_visibility' => 0,
				'product_type'        => 'simple',
			);
			$atts     = empty( $atts ) && ! empty( $this->details ) ? $this->details : $atts;
			$atts     = ! empty( $atts ) ? $atts : wp_unslash( $_REQUEST );// phpcs:ignore WordPress.Security.NonceVerification
			$atts     = wp_parse_args( $atts, $defaults );
			// filtering params.
			$prod_id      = apply_filters( 'wlfmc_adding_to_wishlist_prod_id', intval( $atts['add_to_wishlist'] ) );
			$wishlist_id  = apply_filters( 'wlfmc_adding_to_wishlist_wishlist_id', $atts['wishlist_id'] );
			$quantity     = apply_filters( 'wlfmc_adding_to_wishlist_quantity', intval( $atts['quantity'] ) );
			$user_id      = apply_filters( 'wlfmc_adding_to_wishlist_user_id', intval( $atts['user_id'] ) );
			$dateadded    = apply_filters( 'wlfmc_adding_to_wishlist_dateadded', $atts['dateadded'] );
			$product_type = apply_filters( 'wlfmc_adding_to_wishlist_product_type', $atts['product_type'] );
			do_action( 'wlfmc_adding_to_wishlist', $prod_id, $wishlist_id, $user_id );

			if ( ! $this->can_user_add_to_wishlist() ) {
				throw new WLFMC_Exception( apply_filters( 'wlfmc_user_cannot_add_to_wishlist_message', esc_html__( 'The item cannot be added to this wishlist', 'wc-wlfmc-wishlist' ) ), 1 );
			}

			if ( ! $prod_id ) {
				throw new WLFMC_Exception( __( 'An error occurred while adding the products to the wishlist.', 'wc-wlfmc-wishlist' ), 0 );
			}

			$wishlist = WLFMC_Wishlist_Factory::get_wishlist( $wishlist_id, 'edit' );

			if ( ! $wishlist instanceof WLFMC_Wishlist || ! $wishlist->current_user_can( 'add_to_wishlist' ) ) {
				throw new WLFMC_Exception( __( 'An error occurred while adding the products to the wishlist.', 'wc-wlfmc-wishlist' ), 0 );
			}

			$this->last_operation_token = $wishlist->get_token();

			if ( $wishlist->has_product( $prod_id ) ) {
				throw new WLFMC_Exception( apply_filters( 'wlfmc_product_already_in_wishlist_message', wlfmc_get_option( 'already_in_wishlist_text' ) ), 1 );
			}
			$result       = wlfmc_process_product_data( $product_type, $atts, $prod_id, $quantity );
			$posted_data  = $result['posted_data'];
			$product_meta = $result['product_meta'];

			$item = new WLFMC_Wishlist_Item();

			$item->set_product_id( $prod_id );
			$item->set_quantity( $quantity );
			$item->set_wishlist_id( $wishlist->get_id() );
			$item->set_product_meta( $product_meta );
			if ( ! empty( $posted_data ) ) {
				$item->set_posted_data( $posted_data );
			}
			if ( $dateadded ) {
				$item->set_date_added( $dateadded );
			}
			$wishlist->add_item( $item );
			$wishlist->save();

			wp_cache_delete( 'wishlist-count-' . $wishlist->get_token(), 'wlfmc-wishlists' );

			$user_id = $wishlist->get_user_id();

			if ( $user_id ) {
				wp_cache_delete( 'wishlist-user-total-count-' . $user_id, 'wlfmc-wishlists' );
			}

			do_action( 'wlfmc_added_to_wishlist', $prod_id, $item->get_wishlist_id(), $item, 'wishlist' );

			return array(
				'wishlist_id' => $wishlist->get_id(),
				'customer_id' => $wishlist->get_customer_id(),
				'item_id'     => $item->get_id(),
				'user_id'     => $item->get_user_id(),
			);
		}

		/**
		 * Add a product in the multiple lists.
		 *
		 * @param array $atts Array of parameters; when not passed, params will be searched in $_REQUEST.
		 *
		 * @return array
		 * @throws Exception|WLFMC_Exception When an error occurs with Add to Wishlist operation.
		 * @version 1.5.3
		 */
		public function add_to_lists( $atts = array() ) {
			$defaults = array(
				'add_to_list'   => 0,
				'wishlist_ids'  => array(),
				'current_lists' => array(),
				'quantity'      => 1,
				'user_id'       => false,
				'dateadded'     => '',
				'product_type'  => 'simple',
			);
			$atts     = empty( $atts ) && ! empty( $this->details ) ? $this->details : $atts;
			$atts     = ! empty( $atts ) ? $atts : wp_unslash( $_REQUEST );// phpcs:ignore WordPress.Security.NonceVerification

			$atts = wp_parse_args( $atts, $defaults );

			$wishlist_ids  = isset( $atts['wishlist_ids'] ) ? json_decode( wp_unslash( $atts['wishlist_ids'] ), true ) : false;// phpcs:ignore WordPress.Security
			$current_lists = isset( $atts['current_lists'] ) ? json_decode( wp_unslash( $atts['current_lists'] ), true ) : false;// phpcs:ignore WordPress.Security

			// filtering params.
			$prod_id       = apply_filters( 'wlfmc_adding_to_wishlist_prod_id', intval( $atts['add_to_list'] ) );
			$wishlist_ids  = apply_filters( 'wlfmc_adding_to_wishlist_wishlist_ids', $wishlist_ids );
			$current_lists = apply_filters( 'wlfmc_adding_to_wishlist_current_lists', $current_lists );
			$quantity      = apply_filters( 'wlfmc_adding_to_wishlist_quantity', intval( $atts['quantity'] ) );
			$user_id       = apply_filters( 'wlfmc_adding_to_wishlist_user_id', intval( $atts['user_id'] ) );
			$dateadded     = apply_filters( 'wlfmc_adding_to_wishlist_dateadded', $atts['dateadded'] );
			$product_type  = apply_filters( 'wlfmc_adding_to_wishlist_product_type', $atts['product_type'] );
			do_action( 'wlfmc_adding_to_wishlist', $prod_id, $wishlist_ids, $user_id );

			if ( ! $this->can_user_add_to_list() ) {
				throw new WLFMC_Exception( apply_filters( 'wlfmc_user_cannot_add_to_list_message', esc_html__( 'The item cannot be added to this lists', 'wc-wlfmc-wishlist' ) ), 1 );
			}

			if ( ! $prod_id ) {
				throw new WLFMC_Exception( __( 'An error occurred while adding the products to the list.', 'wc-wlfmc-wishlist' ), 0 );
			}

			// Remove the product from its current wishlists.
			$errors       = array();
			$action_count = 0;
			$last_list_id = false;
			$customer_id  = false;
			if ( ! empty( $current_lists ) ) {
				foreach ( $current_lists as $wishlist_id ) {
					$wishlist = WLFMC_Wishlist_Factory::get_wishlist( $wishlist_id, 'edit' );
					if ( $wishlist instanceof WLFMC_Wishlist && $wishlist->has_product( $prod_id ) ) {
						if ( $wishlist->current_user_can( 'remove_from_list' ) ) {
							$wishlist->remove_product( $prod_id );
							$wishlist->save();
							$action_count++;
						} else {
							/* translators: %s wishlist name */
							$errors[] = sprintf( __( 'Error. Unable to remove the product from the %s list.', 'wc-wlfmc-wishlist' ), $wishlist->get_formatted_name() );
						}
					}
				}
			}

			if ( ! empty( $wishlist_ids ) ) {
				$result       = wlfmc_process_product_data( $product_type, $atts, $prod_id, $quantity );
				$posted_data  = $result['posted_data'];
				$product_meta = $result['product_meta'];

				foreach ( $wishlist_ids as $wishlist_id ) {
					$wishlist = WLFMC_Wishlist_Factory::get_wishlist( $wishlist_id, 'edit' );
					if ( $wishlist instanceof WLFMC_Wishlist ) {
						if ( $wishlist->current_user_can( 'add_to_list' ) ) {
							$item = new WLFMC_Wishlist_Item();

							$item->set_product_id( $prod_id );
							$item->set_quantity( $quantity );
							$item->set_wishlist_id( $wishlist->get_id() );
							$item->set_product_meta( $product_meta );
							if ( ! empty( $posted_data ) ) {
								$item->set_posted_data( $posted_data );
							}
							if ( $dateadded ) {
								$item->set_date_added( $dateadded );
							}

							$wishlist->add_item( $item );
							$wishlist->save();

							do_action( 'wlfmc_added_to_wishlist', $prod_id, $item->get_wishlist_id(), $item, 'lists' );

							$last_list_id = $wishlist->get_id();
							$customer_id  = $wishlist->get_customer_id();
							$action_count++;
						} else {
							/* translators: %s wishlist name */
							$errors[] = sprintf( __( 'An error occurred while adding the products to the %s list.', 'wc-wlfmc-wishlist' ), $wishlist->get_formatted_name() );
						}
					}
				}
			}

			return array(
				'result'       => $action_count > 0,
				'last_list_id' => $last_list_id,
				'customer_id'  => $customer_id,
				'errors'       => $errors,
			);

		}

		/**
		 * Add a cart item in the save for later.
		 *
		 * @param array $atts Array of parameters; when not passed, parameters will be retrieved from $_REQUEST.
		 *
		 * @return array
		 * @throws Exception|WLFMC_Exception When an error occurs with Add to Wishlist operation.
		 * @version 1.5.0
		 */
		public function add_to_save_for_later( $atts = array() ) {
			$defaults = array(
				'add_to_save_for_later' => 0,
			);

			$atts = ! empty( $atts ) ? $atts : wp_unslash( $_REQUEST );// phpcs:ignore WordPress.Security.NonceVerification
			$atts = wp_parse_args( $atts, $defaults );

			$cart_item_key = sanitize_text_field( $atts['add_to_save_for_later'] );

			$cart_item = WC()->cart->get_cart_item( $cart_item_key );

			if ( empty( $cart_item ) ) {
				throw new WLFMC_Exception( __( 'An error occurred while adding the products to the next purchase cart(code:1).', 'wc-wlfmc-wishlist' ), 0 );
			}

			$atts = array(
				'add_to_wishlist' => intval( $cart_item['variation_id'] ) > 0 ? $cart_item['variation_id'] : $cart_item['product_id'],
				'quantity'        => $cart_item['quantity'],
				'user_id'         => false,
				'dateadded'       => '',
			);

			// filtering params.
			$prod_id   = apply_filters( 'wlfmc_adding_to_wishlist_prod_id', intval( $atts['add_to_wishlist'] ) );
			$quantity  = apply_filters( 'wlfmc_adding_to_wishlist_quantity', intval( $atts['quantity'] ) );
			$user_id   = apply_filters( 'wlfmc_adding_to_wishlist_user_id', intval( $atts['user_id'] ) );
			$dateadded = apply_filters( 'wlfmc_adding_to_wishlist_dateadded', $atts['dateadded'] );
			do_action( 'wlfmc_adding_to_save_for_later', $prod_id, $user_id );

			if ( ! $this->can_user_add_to_save_for_later() ) {
				throw new WLFMC_Exception( apply_filters( 'wlfmc_user_cannot_add_to_save_for_later_message', esc_html__( 'The item cannot be added to next purchase cart', 'wc-wlfmc-wishlist' ) ), 1 );
			}

			if ( ! $prod_id ) {
				throw new WLFMC_Exception( __( 'An error occurred while adding the products to the next purchase cart.(code:2)', 'wc-wlfmc-wishlist' ), 0 );
			}

			$wishlist = WLFMC_Wishlist_Factory::get_wishlist_by_slug( 'save-for-later', 'edit' );

			if ( ! $wishlist instanceof WLFMC_Wishlist || ! $wishlist->current_user_can( 'add_to_save_for_later' ) ) {
				throw new WLFMC_Exception( __( 'An error occurred while adding the products to the next purchase cart.(code:3)', 'wc-wlfmc-wishlist' ), 0 );
			}

			$this->last_operation_token = $wishlist->get_token();

			if ( $wishlist->has_cart_item_key( $cart_item_key ) ) {
				throw new WLFMC_Exception( apply_filters( 'wlfmc_product_already_in_save_for_later_message', wlfmc_get_option( 'sfl_already_in_text' ) ), 1 );
			}

			$item = new WLFMC_Wishlist_Item();
			$item->set_cart_item_key( $cart_item_key );
			$item->set_product_id( $prod_id );
			$item->set_quantity( $quantity );
			$item->set_original_price( (float) $cart_item['line_subtotal'] / (float) $cart_item['quantity'] );
			$item->set_wishlist_id( $wishlist->get_id() );
			$item->set_product_meta( $cart_item );

			if ( $dateadded ) {
				$item->set_date_added( $dateadded );
			}

			$wishlist->add_item( $item );
			$wishlist->save();

			wp_cache_delete( 'wlfmc-save-for-later-count-' . $wishlist->get_token(), 'wlfmc-wishlists' );

			$user_id = $wishlist->get_user_id();

			if ( $user_id ) {
				wp_cache_delete( 'wlfmc-save-for-later-user-total-count-' . $user_id, 'wlfmc-wishlists' );
			}

			do_action( 'wlfmc_added_to_wishlist', $prod_id, $item->get_wishlist_id(), $item, 'save-for-later' );

			WC()->cart->remove_cart_item( $cart_item_key );

			return array(
				'wishlist_id' => $wishlist->get_id(),
				'customer_id' => $wishlist->get_customer_id(),
				'item_id'     => $item->get_id(),
				'product_id'  => $item->get_product_id(),
				'remove_url'  => esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
				'user_id'     => $item->get_user_id(),
				'count'       => $wishlist->count_items(),
			);
		}

		/**
		 * Add a product in the waitlist.
		 *
		 * @param array $atts Array of parameters; when not passed, params will be searched in $_REQUEST.
		 *
		 * @return array
		 * @throws Exception|WLFMC_Exception When an error occurs with Add to Wishlist operation.
		 * @version 1.5.9
		 */
		public function add_to_waitlist( $atts = array() ) {
			$defaults = array(
				'add_to_waitlist' => 0,
				'quantity'        => 1,
				'user_id'         => false,
				'dateadded'       => '',
				'product_type'    => 'simple',
				'on_sale'         => 0,
				'back_in_stock'   => 0,
				'price_change'    => 0,
				'low_stock'       => 0,
			);
			$atts     = ! empty( $atts ) ? $atts : wp_unslash( $_REQUEST );// phpcs:ignore WordPress.Security.NonceVerification
			$atts     = wp_parse_args( $atts, $defaults );

			// filtering params.
			$prod_id       = apply_filters( 'wlfmc_adding_to_waitlist_prod_id', intval( $atts['add_to_waitlist'] ) );
			$quantity      = apply_filters( 'wlfmc_adding_to_waitlist_quantity', intval( $atts['quantity'] ) );
			$user_id       = apply_filters( 'wlfmc_adding_to_waitlist_user_id', intval( $atts['user_id'] ) );
			$dateadded     = apply_filters( 'wlfmc_adding_to_waitlist_dateadded', $atts['dateadded'] );
			$on_sale       = apply_filters( 'wlfmc_adding_to_waitlist_on_sale', wlfmc_is_true( $atts['on_sale'] ) );
			$back_in_stock = apply_filters( 'wlfmc_adding_to_waitlist_back_in_stock', wlfmc_is_true( $atts['back_in_stock'] ) );
			$price_change  = apply_filters( 'wlfmc_adding_to_waitlist_price_change', wlfmc_is_true( $atts['price_change'] ) );
			$low_stock     = apply_filters( 'wlfmc_adding_to_waitlist_low_stock', wlfmc_is_true( $atts['low_stock'] ) );
			$product_type  = apply_filters( 'wlfmc_adding_to_waitlist_product_type', $atts['product_type'] );

			if ( in_array( $product_type, apply_filters( 'wlfmc_waitlist_not_supported_product_types', array( 'grouped' ) ), true ) ) {
				throw new WLFMC_Exception( apply_filters( 'wlfmc_waitlist_not_supported_product_types_message', esc_html__( 'This product type not supported', 'wc-wlfmc-wishlist' ) ), 1 );
			}

			do_action( 'wlfmc_adding_to_waitlist', $prod_id, $user_id );

			if ( ! $this->can_user_add_to_waitlist() ) {
				throw new WLFMC_Exception( apply_filters( 'wlfmc_user_cannot_add_to_waitlist_message', esc_html__( 'The item cannot be added to waitlist', 'wc-wlfmc-wishlist' ) ), 1 );
			}

			if ( ! $prod_id ) {
				throw new WLFMC_Exception( __( 'An error occurred while adding the products to the waitlist.(code:2)', 'wc-wlfmc-wishlist' ), 0 );
			}

			$wishlist = WLFMC_Wishlist_Factory::get_wishlist_by_slug( 'waitlist', 'edit' );

			if ( ! $wishlist instanceof WLFMC_Wishlist || ! $wishlist->current_user_can( 'add_to_waitlist' ) ) {
				throw new WLFMC_Exception( __( 'An error occurred while adding the products to the waitlist.(code:3)', 'wc-wlfmc-wishlist' ), 0 );
			}

			$this->last_operation_token = $wishlist->get_token();
			$result                     = wlfmc_process_product_data( $product_type, $atts, $prod_id, $quantity );
			$posted_data                = $result['posted_data'];
			$product_meta               = $result['product_meta'];
			$old_price_change_state     = false;
			$old_on_sale_state          = false;
			$old_low_stock_state        = false;
			$old_back_in_stock_state    = false;
			$has_product                = $wishlist->has_product( $prod_id );
			$result                     = array(
				'wishlist_id' => $wishlist->get_id(),
				'customer_id' => $wishlist->get_customer_id(),
				'state'       => '',
			);
			if ( $on_sale || $back_in_stock || $price_change || $low_stock ) {
				$item = new WLFMC_Wishlist_Item();
				$item->set_product_id( $prod_id );
				$item->set_quantity( $quantity );
				$item->set_wishlist_id( $wishlist->get_id() );
				$item->set_on_sale( $on_sale );
				$item->set_back_in_stock( $back_in_stock );
				$item->set_price_change( $price_change );
				$item->set_low_stock( $low_stock );
				$item->set_product_meta( $product_meta );
				if ( ! empty( $posted_data ) ) {
					$item->set_posted_data( $posted_data );
				}
				if ( $dateadded ) {
					$item->set_date_added( $dateadded );
				}

				$wishlist->add_item( $item );
				$wishlist->save();

				if ( $has_product ) {
					$product                 = $wishlist->get_product( $prod_id );
					$old_price_change_state  = $product->is_price_change();
					$old_on_sale_state       = $product->is_on_sale();
					$old_low_stock_state     = $product->is_low_stock();
					$old_back_in_stock_state = $product->is_back_in_stock();
				}
				$result['state'] = 'changed';

				// analytics.
				if ( ! $has_product ) {
					do_action( 'wlfmc_added_to_wishlist', $prod_id, $item->get_wishlist_id(), $item, 'waitlist' );
					$result['state'] = 'added';
				}

				if ( $on_sale && ! wlfmc_is_true( $old_on_sale_state ) ) {
					do_action( 'wlfmc_added_to_wishlist', $prod_id, $item->get_wishlist_id(), $item, 'on-sale' );
				}
				if ( $back_in_stock && ! wlfmc_is_true( $old_back_in_stock_state ) ) {
					do_action( 'wlfmc_added_to_wishlist', $prod_id, $item->get_wishlist_id(), $item, 'back-in-stock' );
				}
				if ( $price_change && ! wlfmc_is_true( $old_price_change_state ) ) {
					do_action( 'wlfmc_added_to_wishlist', $prod_id, $item->get_wishlist_id(), $item, 'price-change' );
				}
				if ( $low_stock && ! wlfmc_is_true( $old_low_stock_state ) ) {
					do_action( 'wlfmc_added_to_wishlist', $prod_id, $item->get_wishlist_id(), $item, 'low-stock' );
				}

				$result['user_id'] = $item->get_user_id();
				$result['item_id'] = $item->get_id();

			} elseif ( $has_product ) {
				if ( $wishlist->current_user_can( 'remove_from_waitlist' ) ) {
					$wishlist->remove_product( $prod_id );
					$wishlist->save();
					$result['state'] = 'removed';
				}
			}

			wp_cache_delete( 'wlfmc-waitlist-count-' . $wishlist->get_token(), 'wlfmc-wishlists' );

			$user_id = $wishlist->get_user_id();

			if ( $user_id ) {
				wp_cache_delete( 'wlfmc-waitlist-user-total-count-' . $user_id, 'wlfmc-wishlists' );
			}
			// Send verification email.
			return apply_filters( 'wlfmc_added_to_waitlist', $result, $wishlist, $has_product );
		}

		/**
		 * Remove an entry from the wishlist.
		 *
		 * @param array $atts Array of parameters; when not passed, parameters will be retrieved from $_REQUEST.
		 *
		 * @return array
		 * @throws Exception|WLFMC_Exception When something was wrong with removal.
		 * @version 1.3.3
		 */
		public function remove_from_save_for_later( $atts = array() ) {
			$defaults = array(
				'remove_from_save_for_later' => 0,
			);

			$atts = ! empty( $atts ) ? $atts : wp_unslash( $_REQUEST );// phpcs:ignore WordPress.Security.NonceVerification
			$atts = wp_parse_args( $atts, $defaults );

			$item_id = intval( $atts['remove_from_save_for_later'] );

			do_action( 'wlfmc_removing_from_save_for_later', $item_id );

			if ( ! $item_id ) {
				throw new WLFMC_Exception( apply_filters( 'wlfmc_unable_to_remove_product_from_save_for_later_message', esc_html__( 'Error. Unable to remove the product from the next purchase cart.', 'wc-wlfmc-wishlist' ) ), 0 );
			}

			$wishlist = apply_filters( 'wlfmc_get_save_for_later_on_remove', WLFMC_Wishlist_Factory::get_wishlist_by_slug( 'save-for-later' ), $atts );

			if ( apply_filters( 'wlfmc_allow_remove_after_add_to_cart', ! $wishlist instanceof WLFMC_Wishlist || ! $wishlist->current_user_can( 'remove_from_save_for_later' ), $wishlist ) ) {
				throw new WLFMC_Exception( apply_filters( 'wlfmc_unable_to_remove_product_from_save_for_later_message', esc_html__( 'Error. Unable to remove the product from the next purchase cart.', 'wc-wlfmc-wishlist' ) ), 0 );
			}

			$wishlist->remove_item( $item_id );
			$wishlist->save();

			wp_cache_delete( 'wlfmc-save-for-later-count-' . $wishlist->get_token(), 'wlfmc-wishlists' );

			$user_id = $wishlist->get_user_id();

			if ( $user_id ) {
				wp_cache_delete( 'wlfmc-save-for-later-user-total-count-' . $user_id );
			}

			do_action( 'wlfmc_removed_from_save_for_later', $item_id, $wishlist->get_id(), $wishlist->get_user_id() );

			return array(
				'wishlist_id' => $wishlist->get_id(),
				'customer_id' => $wishlist->get_customer_id(),
				'count'       => $wishlist->count_items(),
			);
		}

		/**
		 * Remove an entry from the wishlist.
		 *
		 * @param array $atts Array of parameters; when not passed, parameters will be retrieved from $_REQUEST.
		 *
		 * @return array
		 * @throws Exception|WLFMC_Exception When something was wrong with removal.
		 * @version 1.3.3
		 */
		public function remove( $atts = array() ) {
			$defaults = array(
				'remove_from_wishlist' => 0,
				'wishlist_id'          => 0,
				'user_id'              => false,
			);

			$atts = empty( $atts ) && ! empty( $this->details ) ? $this->details : $atts;
			$atts = ! empty( $atts ) ? $atts : wp_unslash( $_REQUEST );// phpcs:ignore WordPress.Security.NonceVerification
			$atts = wp_parse_args( $atts, $defaults );

			$prod_id     = intval( $atts['remove_from_wishlist'] );
			$wishlist_id = intval( $atts['wishlist_id'] );
			$user_id     = intval( $atts['user_id'] );

			do_action( 'wlfmc_removing_from_wishlist', $prod_id, $wishlist_id, $user_id );

			if ( ! $prod_id ) {
				throw new WLFMC_Exception( apply_filters( 'wlfmc_unable_to_remove_product_message', esc_html__( 'Error. Unable to remove the product from the wishlist.', 'wc-wlfmc-wishlist' ) ), 0 );
			}

			$wishlist = apply_filters( 'wlfmc_get_wishlist_on_remove', WLFMC_Wishlist_Factory::get_wishlist( $wishlist_id ), $atts );

			if ( apply_filters( 'wlfmc_allow_remove_after_add_to_cart', ! $wishlist instanceof WLFMC_Wishlist || ! $wishlist->current_user_can( 'remove_from_wishlist' ), $wishlist ) ) {
				throw new WLFMC_Exception( apply_filters( 'wlfmc_unable_to_remove_product_message', esc_html__( 'Error. Unable to remove the product from the wishlist.', 'wc-wlfmc-wishlist' ) ), 0 );
			}

			$wishlist->remove_product( $prod_id );
			$wishlist->save();

			wp_cache_delete( 'wishlist-count-' . $wishlist->get_token(), 'wlfmc-wishlists' );

			$user_id = $wishlist->get_user_id();

			if ( $user_id ) {
				wp_cache_delete( 'wishlist-user-total-count-' . $user_id );
			}

			do_action( 'wlfmc_removed_from_wishlist', $prod_id, $wishlist->get_id(), $wishlist->get_user_id() );

			return array(
				'wishlist_id' => $wishlist->get_id(),
				'customer_id' => $wishlist->get_customer_id(),
				'count'       => $wishlist->count_items(),
			);
		}

		/**
		 * Check if the product exists in the wishlist.
		 *
		 * @param int      $product_id Product id to check.
		 * @param int|bool $wishlist_id Wishlist where to search (use false to search in default wishlist).
		 *
		 * @return bool
		 */
		public function is_product_in_wishlist( int $product_id, $wishlist_id = false ): bool {
			$wishlist = WLFMC_Wishlist_Factory::get_wishlist( $wishlist_id );

			if ( ! $wishlist ) {
				return false;
			}

			return apply_filters( 'wlfmc_is_product_in_wishlist', $wishlist->has_product( $product_id ), $product_id, $wishlist_id );
		}

		/**
		 * Check if the product exists in the wishlist.
		 *
		 * @param int $product_id Product id to check.
		 *
		 * @return bool
		 */
		public function is_product_in_waitlist( int $product_id ): bool {
			$wishlist = WLFMC_Wishlist_Factory::get_wishlist_by_slug( 'waitlist', 'edit' );

			if ( ! $wishlist ) {
				return false;
			}

			return apply_filters( 'wlfmc_is_product_in_waitlist', $wishlist->has_product( $product_id ), $product_id );
		}

		/**
		 * Remove product from wishlist after order purchased or completed
		 *
		 * @param int $order_id  Order ID.
		 *
		 * @return void
		 * @since 1.3.3
		 */
		public function action_remove_from_wishlist( int $order_id ) {

			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				return;
			}

			$options              = new MCT_Options( 'wlfmc_options' );
			$remove_from_wishlist = $options->get_option( 'remove_from_wishlist', 'none' );
			if ( $order->has_status( 'processing' ) && 'processing' !== $remove_from_wishlist ) {
				return;
			}

			if ( $order->has_status( 'completed' ) && 'completed' !== $remove_from_wishlist ) {
				return;
			}

			$items       = $order->get_items();
			$user_id     = $order->get_user_id();
			$customer_id = $order->get_meta( 'wlfmc_customer_id', true );
			$wishlists   = WLFMC_Wishlist_Factory::get_wishlists(
				array(
					'user_id'     => $user_id,
					'customer_id' => $customer_id,
				)
			);

			foreach ( $wishlists as $wishlist ) {
				foreach ( $items as  $value ) {

					$product_id   = $value->get_product_id();
					$variation_id = $value->get_variation_id();

					if ( $variation_id && $wishlist->has_product( $variation_id ) ) {

						$wishlist->remove_product( $variation_id );
						set_transient( '_wlfmc_update_wishlists_data', '1' );

					} elseif ( $product_id && $wishlist->has_product( $product_id ) ) {

						$wishlist->remove_product( $product_id );
						set_transient( '_wlfmc_update_wishlists_data', '1' );
					}
				}
				$wishlist->save();

			}

		}

		/**
		 * Update Wishlist data if needed
		 *
		 * @param bool $state Current state.
		 *
		 * @return bool
		 */
		public function update_wishlists_data( bool $state ): bool {

			if ( get_transient( '_wlfmc_update_wishlists_data' ) ) {
				delete_transient( '_wlfmc_update_wishlists_data' );
				return true;
			}
			return $state;
		}

		/**
		 * Update Wishlist data if logout | added product to cart
		 *
		 * @return void
		 */
		public function need_update_wishlist_data() {
			set_transient( '_wlfmc_update_wishlists_data', '1' );
		}

		/**
		 * Retrieve elements of the wishlist for a specific user
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
		 * @return WLFMC_Wishlist_Item[]|bool
		 */
		public function get_products( $args = array() ) {
			return WLFMC_Wishlist_Factory::get_wishlist_items( $args );
		}

		/**
		 * Retrieve the number of products in the wishlist.
		 *
		 * @param string|bool $wishlist_token Wishlist token if any; false for default wishlist.
		 *
		 * @return int
		 * @since 1.5.2
		 */
		public function count_products( $wishlist_token = false ) {
			$wishlist = WLFMC_Wishlist_Factory::get_wishlist( $wishlist_token );

			if ( ! $wishlist ) {
				return 0;
			}

			$count = wp_cache_get( 'wishlist-count-' . $wishlist->get_token(), 'wlfmc-wishlists' );

			if ( false === $count ) {
				$count = $wishlist->count_items();
				wp_cache_set( 'wishlist-count-' . $wishlist->get_token(), $count, 'wlfmc-wishlists' );
			}

			return $count;
		}

		/**
		 * Count all user items in wishlists
		 *
		 * @param int $user_id user id.
		 *
		 * @return int Count of items added all over wishlist from current user
		 * @since 1.4.4
		 */
		public function count_products_by_user( $user_id = '' ) {
			$args = array(
				'wishlist_id' => 'all',
			);
			if ( '' !== $user_id ) {
				$id              = $user_id;
				$args['user_id'] = $user_id;
			} elseif ( is_user_logged_in() ) {
				$id              = get_current_user_id();
				$args['user_id'] = $id;
			} elseif ( WLFMC_Session()->has_session() ) {
				$id                 = WLFMC_Session()->get_session_id();
				$args['session_id'] = $id;
			}

			if ( ! isset( $id ) ) {
				return 0;
			}

			$count = wp_cache_get( 'wishlist-user-total-count-' . $id, 'wlfmc-wishlists' );

			if ( false === $count ) {
				$count = WLFMC_Wishlist_Factory::get_wishlist_items_count( $args );
				wp_cache_set( 'wishlist-user-total-count-' . $id, $count, 'wlfmc-wishlists' );
			}

			return $count;
		}

		/**
		 * Count number of times a product was added to users wishlists
		 *
		 * @param int|bool $product_id Product id; false will force method to use global product.
		 * @param array    $columns An array of custom columns with values. Column keys can be 'on_sale', 'back_in_stock', 'price_change' or 'low_stock'.
		 *
		 * @return int Number of times the product was added to wishlist
		 * @since 1.4.4
		 */
		public function count_add_to_lists( $product_id = false, $columns = array() ): int {
			global $product;

			$product_id = ! ( $product_id ) ? $product->get_id() : $product_id;

			if ( ! $product_id ) {
				return 0;
			}

			return WLFMC_Wishlist_Factory::get_times_added_count( $product_id, $columns );
		}

		/**
		 * Count number of times a product was added to users wishlists
		 *
		 * @param int|bool $parent_product_id Product id; false will force method to use global product.
		 * @param array    $columns An array of custom columns with values. Column keys can be 'on_sale', 'back_in_stock', 'price_change' or 'low_stock'.
		 * @param string   $list_type list type. List type can be 'all','lists', 'wishlist', 'waitlist', 'save-for-later', 'on-sale', 'back-in-stock', 'price-change' or 'low-stock'.
		 *
		 * @return int Number of times the product was added to wishlist
		 * @since 1.4.4
		 */
		public function parent_count_add_to_lists( $parent_product_id = false, $columns = array(), $list_type = 'all' ): int {

			if ( ! $parent_product_id ) {
				return 0;
			}
			return WLFMC_Wishlist_Factory::get_times_parent_added_count( $parent_product_id, $columns, $list_type );
		}

		/**
		 * Retrieve details of a product in the wishlist.
		 *
		 * @param int      $product_id Product ID.
		 * @param int|bool $wishlist_id Wishlist ID.
		 *
		 * @return WLFMC_Wishlist_Item|bool
		 */
		public function get_product_details( int $product_id, $wishlist_id = false ) {
			$product = $this->get_products(
				array(
					'prod_id'     => $product_id,
					'wishlist_id' => $wishlist_id,
				)
			);

			if ( empty( $product ) ) {
				return false;
			}

			return array_shift( $product );
		}

		/**
		 * Remove product from lists before completely product removed.
		 *
		 * @param int $product_id product id.
		 *
		 * @return void
		 */
		public function delete_products( $product_id ) {
			global $wpdb;
			// Get the children IDs for the product.
			$children_ids     = get_children(
				array(
					'post_parent' => $product_id,
					'post_type'   => 'product',
				)
			);
			$children_ids_sql = ! empty( $children_ids ) ? implode( ',', $children_ids ) : '';
			// Delete rows from wishlist items table with product ID or children IDs.
			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->wlfmc_wishlist_items WHERE prod_id = %d OR parent_id = %d", $product_id, $product_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			if ( ! empty( $children_ids_sql ) ) {
				$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->wlfmc_wishlist_analytics WHERE prod_id = %d OR prod_id IN ( $children_ids_sql )", $product_id ) );// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			}

		}

		/* === WISHLISTS METHODS === */

		/**
		 * Retrieve all the wishlist matching specified arguments
		 *
		 * @param mixed $args Array of valid arguments<br/>
		 * [<br/>
		 *     'id'                  // Wishlist id to search, if any<br/>
		 *     'user_id'             // User owner<br/>
		 *     'wishlist_slug'       // Slug of the wishlist to search<br/>
		 *     'wishlist_name'       // Name of the wishlist to search<br/>
		 *     'wishlist_token'      // Token of the wishlist to search<br/>
		 *     'wishlist_visibility' // Wishlist visibility: all, visible, public, private<br/>
		 *     'user_search'         // String to match against first name / last name or email of the wishlist owner<br/>
		 *     'is_default'          // Whether wishlist should be default or not<br/>
		 *     'orderby'             // Column used to sort final result (could be any wishlist lists column)<br/>
		 *     'order'               // Sorting order<br/>
		 *     'limit'               // Pagination param: maximum number of elements in the set. 0 to retrieve all elements<br/>
		 *     'offset'              // Pagination param: offset for the current set. 0 to start from the first item<br/>
		 *     'show_empty'          // Whether to show empty lists' os not<br/>
		 * ].
		 *
		 * @return WLFMC_Wishlist[]
		 */
		public function get_wishlists( $args = array() ) {
			return WLFMC_Wishlist_Factory::get_wishlists( $args );
		}

		/**
		 * Wrapper for \WLFMC::get_wishlists, will return wishlists for current user
		 *
		 * @param bool $return_wishlists return wishlists or ids.
		 * @param bool $exclude_default exclude default wishlist.
		 *
		 * @return WLFMC_Wishlist[]
		 * @version 1.6.8
		 */
		public function get_current_user_wishlists( $return_wishlists = false, $exclude_default = true ) {
			$customer = WLFMC_Wishlist_Factory::get_current_customer( false );
			if ( ! $customer ) {
				return array();
			}
			$list_type  = $return_wishlists ? 'object' : 'ids';
			$list_type .= $exclude_default ? '-exclude-default' : '-include-default';
			$lists      = wp_cache_get( 'user-wishlists-' . $customer->get_id() . '-' . $list_type, 'wlfmc-wishlists' );

			if ( ! $lists ) {
				$lists = WLFMC_Wishlist_Factory::get_wishlists(
					array(
						'customer_id'      => $customer->get_id(),
						'exclude_slugs'    => wlfmc_reserved_slugs(),
						'exclude_default'  => $exclude_default,
						'return_wishlists' => $return_wishlists,
						'orderby'          => 'dateadded'
					)
				);

				wp_cache_set( 'user-wishlists-' . $customer->get_id() . '-' . $list_type, $lists, 'wlfmc-wishlists' );
			}

			return $lists;
		}

		/**
		 * Returns details of a wishlist, searching it by wishlist id
		 *
		 * @param int $wishlist_id Wishlist ID.
		 *
		 * @return WLFMC_Wishlist
		 */
		public function get_wishlist_detail( $wishlist_id ) {
			return WLFMC_Wishlist_Factory::get_wishlist( $wishlist_id );
		}

		/**
		 * Returns details of a wishlist, searching it by wishlist token
		 *
		 * @param string $wishlist_token Wishlist token.
		 *
		 * @return WLFMC_Wishlist
		 */
		public function get_wishlist_detail_by_token( $wishlist_token ) {
			return WLFMC_Wishlist_Factory::get_wishlist( $wishlist_token );
		}

		/**
		 * Generate default wishlist for current user or session
		 *
		 * @param string|int|bool $id string|int|bool Customer or session id; false if you want to use current customer or session.
		 *
		 * @return int Default wishlist id
		 */
		public function generate_default_wishlist( $id = false ) {
			$wishlist = WLFMC_Wishlist_Factory::generate_default_wishlist( $id );

			if ( $wishlist ) {
				return $wishlist->get_id();
			}

			return false;
		}

		/**
		 * Generate a token to visit wishlist
		 *
		 * @return string token
		 */
		public function generate_wishlist_token() {
			return WLFMC_Wishlist_Factory::generate_wishlist_token();
		}

		/**
		 * Returns an array of users that created and populated a public wishlist
		 *
		 * @param mixed $args Array of valid arguments<br/>
		 * [<br/>
		 *     'search' // String to match against first name / last name / user login or user email of wishlist owner<br/>
		 *     'limit'  // Pagination param: number of items to show in one page. 0 to show all items<br/>
		 *     'offset' // Pagination param: offset for the current set. 0 to start from the first item<br/>
		 * ].
		 *
		 * @return array
		 */
		public function get_users_with_wishlist( $args = array() ) {
			return WLFMC_Wishlist_Factory::get_wishlist_users( $args );
		}

		/**
		 * Count users that have public wishlists
		 *
		 * @param string $search String to match against first name / last name / user login or user email of wishlist owner.
		 *
		 * @return int
		 */
		public function count_users_with_wishlists( $search ) {
			return count( $this->get_users_with_wishlist( array( 'search' => $search ) ) );
		}

		/* === GENERAL METHODS === */

		/**
		 * Checks whether current user can add to the wishlist
		 *
		 * @param int|bool $user_id User id to test; false to use current user id.
		 *
		 * @return bool Whether current user can add to wishlist
		 */
		public function can_user_add_to_wishlist( $user_id = false ) {
			$user_id = $user_id ? $user_id : get_current_user_id();
			$options = new MCT_Options( 'wlfmc_options' );
			$return  = true;

			$who_can_see_wishlist_options = $options->get_option( 'who_can_see_wishlist_options', 'all' );
			$force_user_to_login          = $options->get_option( 'force_user_to_login', false );

			if ( ( 'users' === $who_can_see_wishlist_options && ! $user_id ) || ( wlfmc_is_true( $force_user_to_login ) && 'all' === $who_can_see_wishlist_options && ! $user_id ) ) {
				$return = false;
			}

			return apply_filters( 'wlfmc_can_user_add_to_wishlist', $return, $user_id );
		}

		/**
		 * Checks whether current user can add to the list
		 *
		 * @param int|bool $user_id User id to test; false to use current user id.
		 *
		 * @return bool Whether current user can add to wishlist
		 */
		public function can_user_add_to_list( $user_id = false ) {
			$user_id = $user_id ? $user_id : get_current_user_id();
			$options = new MCT_Options( 'wlfmc_options' );
			$return  = true;

			$who_can_see_wishlist_options = $options->get_option( 'multi_list_who_can_see', 'all' );
			$force_user_to_login          = $options->get_option( 'multi_list_force_user_to_login', false );

			if ( ( 'users' === $who_can_see_wishlist_options && ! $user_id ) || ( wlfmc_is_true( $force_user_to_login ) && 'all' === $who_can_see_wishlist_options && ! $user_id ) ) {
				$return = false;
			}

			return apply_filters( 'wlfmc_can_user_add_to_list', $return, $user_id );
		}

		/**
		 * Checks whether current user can add to the save for later
		 *
		 * @param int|bool $user_id User id to test; false to use current user id.
		 *
		 * @return bool Whether current user can add to save for later
		 */
		public function can_user_add_to_save_for_later( $user_id = false ) {
			$user_id = $user_id ? $user_id : get_current_user_id();
			$options = new MCT_Options( 'wlfmc_options' );
			$return  = true;

			$who_can_see_options = $options->get_option( 'sfl_who_can_see', 'all' );
			$force_user_to_login = $options->get_option( 'sfl_force_user_to_login', false );

			if ( ( 'users' === $who_can_see_options && ! $user_id ) || ( wlfmc_is_true( $force_user_to_login ) && 'all' === $who_can_see_options && ! $user_id ) ) {
				$return = false;
			}

			return apply_filters( 'wlfmc_can_user_add_to_save_for_later', $return, $user_id );
		}

		/**
		 * Checks whether current user can add to the waitlist
		 *
		 * @param int|bool $user_id User id to test; false to use current user id.
		 *
		 * @return bool Whether current user can add to waitlist
		 */
		public function can_user_add_to_waitlist( $user_id = false ) {
			$user_id = $user_id ? $user_id : get_current_user_id();
			$options = new MCT_Options( 'wlfmc_options' );
			$return  = true;

			$who_can_see_options = $options->get_option( 'waitlist_who_can_see', 'all' );
			$force_user_to_login = $options->get_option( 'waitlist_force_user_to_login', false );

			if ( ( 'users' === $who_can_see_options && ! $user_id ) || ( wlfmc_is_true( $force_user_to_login ) && 'all' === $who_can_see_options && ! $user_id ) ) {
				$return = false;
			}

			return apply_filters( 'wlfmc_can_user_add_to_waitlist', $return, $user_id );
		}

		/**
		 * Register custom plugin Data Stores classes
		 *
		 * @param array $data_stores Array of registered data stores.
		 *
		 * @return array Array of filtered data store
		 */
		public function register_data_stores( $data_stores ) {
			$data_stores['wlfmc-customer']      = 'WLFMC_Customer_Data_Store';
			$data_stores['wlfmc-wishlist']      = 'WLFMC_Wishlist_Data_Store';
			$data_stores['wlfmc-wishlist-item'] = 'WLFMC_Wishlist_Item_Data_Store';

			return $data_stores;
		}

		/**
		 * Add rewrite rules for wishlist
		 *
		 * @return void
		 * @version 1.5.9
		 */
		public function add_rewrite_rules() {

			// filter wishlist param.
			$this->wishlist_param = apply_filters( 'wlfmc_wishlist_param', $this->wishlist_param );

			$rewrite_rules = get_option( 'rewrite_rules' );
			$flush_rewrite = false;

			$wishlist_page_id   = isset( $_POST['wlfmc_wishlist_page_id'] ) ? intval( $_POST['wlfmc_wishlist_page_id'] ) : get_option( 'wlfmc_wishlist_page_id' );// phpcs:ignore WordPress.Security.NonceVerification
			$wishlist_page_id   = wlfmc_object_id( $wishlist_page_id, 'page', true, 'default' );
			$waitlist_page_id   = isset( $_POST['wlfmc_waitlist_page_id'] ) ? intval( $_POST['wlfmc_waitlist_page_id'] ) : get_option( 'wlfmc_waitlist_page_id' );// phpcs:ignore WordPress.Security.NonceVerification
			$waitlist_page_id   = wlfmc_object_id( $waitlist_page_id, 'page', true, 'default' );
			$multi_list_page_id = isset( $_POST['wlfmc_multi_list_page_id'] ) ? intval( $_POST['wlfmc_multi_list_page_id'] ) : get_option( 'wlfmc_multi_list_page_id' );// phpcs:ignore WordPress.Security.NonceVerification
			$multi_list_page_id = wlfmc_object_id( $multi_list_page_id, 'page', true, 'default' );
			$tabbed_page_id     = isset( $_POST['wlfmc_tabbed_page_id'] ) ? intval( $_POST['wlfmc_tabbed_page_id'] ) : get_option( 'wlfmc_tabbed_page_id' );// phpcs:ignore WordPress.Security.NonceVerification
			$tabbed_page_id     = wlfmc_object_id( $tabbed_page_id, 'page', true, 'default' );

			$pages = apply_filters(
				'wlfmc_rewrite_rules_pages',
				array(
					'wishlist' => $wishlist_page_id,
					'lists'    => $multi_list_page_id,
					'waitlist' => $waitlist_page_id,
					'tabbed'   => $tabbed_page_id,
				)
			);

			if ( ! empty( $pages ) ) {
				foreach ( $pages as $page ) {
					if ( ! empty( $page ) ) {
						$wishlist_page      = get_post( $page );
						$wishlist_page_slug = $wishlist_page ? $wishlist_page->post_name : false;
						if ( ! empty( $wishlist_page_slug ) ) {
							if ( ! defined( 'POLYLANG_VERSION' ) && ! defined( 'ICL_PLUGIN_PATH' ) ) {
								$regex_paged  = '(([^/]+/)*' . urldecode( $wishlist_page_slug ) . ')(/(.*))?/page/([0-9]{1,})/?$';
								$regex_simple = '(([^/]+/)*' . urldecode( $wishlist_page_slug ) . ')(/(.*))?/?$';

								add_rewrite_rule( $regex_paged, 'index.php?pagename=$matches[1]&' . $this->wishlist_param . '=$matches[4]&paged=$matches[5]', 'top' );
								add_rewrite_rule( $regex_simple, 'index.php?pagename=$matches[1]&' . $this->wishlist_param . '=$matches[4]', 'top' );
								if ( ! is_array( $rewrite_rules ) || ! array_key_exists( $regex_paged, $rewrite_rules ) || ! array_key_exists( $regex_simple, $rewrite_rules ) ) {
									$flush_rewrite = true;
								}
							}
						}
					}
				}
			}

			if ( $flush_rewrite ) {
				flush_rewrite_rules();
			}

		}

		/**
		 * Adds public query var for wishlist
		 *
		 * @param array $public_var Variables.
		 *
		 * @return array
		 */
		public function add_public_query_var( array $public_var ): array {
			$public_var[] = $this->wishlist_param;
			$public_var[] = 'wishlist_id';

			return $public_var;
		}

		/**
		 * Return wishlist page id, if any
		 *
		 * @return int Wishlist page id.
		 */
		public function get_wishlist_page_id(): int {
			$wishlist_page_id = get_option( 'wlfmc_wishlist_page_id' );
			$wishlist_page_id = wlfmc_object_id( $wishlist_page_id );

			return (int) apply_filters( 'wlfmc_wishlist_page_id', $wishlist_page_id );
		}

		/**
		 * Return waitlist page id, if any
		 *
		 * @return int Wishlist page id.
		 */
		public function get_waitlist_page_id(): int {
			$waitlist_page_id = get_option( 'wlfmc_waitlist_page_id' );
			$waitlist_page_id = wlfmc_object_id( $waitlist_page_id );

			return (int) apply_filters( 'wlfmc_waitlist_page_id', $waitlist_page_id );
		}

		/**
		 * Return wishlist page id, if any
		 *
		 * @return int Wishlist page id.
		 */
		public function get_multi_list_page_id(): int {
			$multi_list_page_id = get_option( 'wlfmc_multi_list_page_id' );
			$multi_list_page_id = wlfmc_object_id( $multi_list_page_id );

			return (int) apply_filters( 'wlfmc_multi_list_page_id', $multi_list_page_id );
		}

		/**
		 * Return tabbed page id, if any
		 *
		 * @return int Wishlist page id.
		 */
		public function get_tabbed_page_id(): int {
			$tabbed_page_id = get_option( 'wlfmc_tabbed_page_id' );
			$tabbed_page_id = wlfmc_object_id( $tabbed_page_id );

			return (int) apply_filters( 'wlfmc_tabbed_page_id', $tabbed_page_id );
		}

		/**
		 * Build wishlist page URL.
		 *
		 * @param string $list_type List type.
		 * @param string $action Action params.
		 *
		 * @return string
		 */
		public function get_wishlist_url( $list_type = 'wishlist', $action = '' ): string {
			global $sitepress;
			switch ( $list_type ) {
				case 'lists':
					$wishlist_page_id = $this->get_multi_list_page_id();
					break;
				case 'waitlist':
					$wishlist_page_id = $this->get_waitlist_page_id();
					break;
				case 'wishlist':
				default:
					$wishlist_page_id = $this->get_wishlist_page_id();
			}
			$enable_tabbed    = apply_filters( 'wlfmc_enable_list_tabbed', false, $list_type );
			$wishlist_page_id = apply_filters( 'wlfmc_get_page_id_by_type', $wishlist_page_id, $list_type );
			if ( $enable_tabbed ) {
				$wishlist_page_id = $this->get_tabbed_page_id();
			}
			$wishlist_permalink = get_the_permalink( $wishlist_page_id );
			$action_params      = $action ? explode( '/', $action ) : array();
			$view               = $action_params[0] ?? false;
			$data               = $action_params[1] ?? '';

			if ( 'view' === $action && empty( $data ) ) {
				return $wishlist_permalink;
			}

			if ( get_option( 'permalink_structure' ) && ! defined( 'ICL_PLUGIN_PATH' ) && ! defined( 'POLYLANG_VERSION' ) ) {
				$wishlist_permalink = trailingslashit( $wishlist_permalink );
				$base_url           = trailingslashit( $wishlist_permalink . $action );
			} else {
				$base_url                        = $wishlist_permalink;
				$params                          = array();
				$params[ $this->wishlist_param ] = $view;
				if ( ! empty( $data ) ) {

					if ( 'view' === $view ) {
						$params['wishlist_id'] = $data;
					} elseif ( 'user' === $view ) {
						$params['user_id'] = $data;
					}
				}

				$base_url = add_query_arg( $params, $base_url );
			}

			if ( defined( 'ICL_PLUGIN_PATH' ) && $sitepress->get_current_language() !== $sitepress->get_default_language() ) {
				$base_url = add_query_arg( 'lang', $sitepress->get_current_language(), $base_url );
			}

			return apply_filters( 'wlfmc_' . $list_type . '_page_url', esc_url_raw( $base_url ), $action );
		}

		/**
		 * Retrieve url for the wishlist that was affected by last operation
		 *
		 * @param string $list_type List type.
		 * @return string Url to view last operation wishlist
		 */
		public function get_last_operation_url( $list_type = 'wishlist' ): string {
			$action = 'view';

			if ( ! empty( $this->last_operation_token ) ) {
				$action .= "/$this->last_operation_token";
			}

			return $this->get_wishlist_url( $list_type, $action );
		}

		/**
		 * Retrieve url for the wishlist that was affected by woocommerce endpoint
		 *
		 * @param string      $list_type List type.
		 * @param string|null $action wishlist url.
		 *
		 * @return mixed|void
		 */
		public function get_wc_wishlist_url( $list_type = 'wishlist', $action = null ) {
			$options = new MCT_Options( 'wlfmc_options' );

			switch ( $list_type ) {
				case 'lists':
					$wishlist_user_page = $options->get_option( 'multi_list_user_page', 'myaccount-page' );
					$wc_panel_endpoint  = $options->get_option( 'multi_list_endpoint', 'wlfmc-lists' );
					$custom_panel_url   = $options->get_option( 'multi_list_custom_url', '' );
					break;
				case 'waitlist':
					$wishlist_user_page = $options->get_option( 'waitlist_user_page', 'myaccount-page' );
					$wc_panel_endpoint  = $options->get_option( 'waitlist_endpoint', 'wlfmc-waitlist' );
					$custom_panel_url   = $options->get_option( 'waitlist_custom_url', '' );
					break;
				case 'tabbed':
					$wishlist_user_page = $options->get_option( 'global_user_page', 'myaccount-page' );
					$wc_panel_endpoint  = $options->get_option( 'global_endpoint', 'wlfmc-wishlist' );
					$custom_panel_url   = $options->get_option( 'global_custom_url', '' );
					break;
				case 'wishlist':
				default:
					$wishlist_user_page = $options->get_option( 'wishlist_user_page', 'myaccount-page' );
					$wc_panel_endpoint  = $options->get_option( 'wishlist_endpoint', 'wlfmc-wishlist' );
					$custom_panel_url   = $options->get_option( 'wishlist_custom_url', '' );
			}
			$request_list = $list_type;
			$args         = apply_filters(
				'wlfmc_user_list_page_args',
				array(
					'user_page'  => $wishlist_user_page,
					'endpoint'   => $wc_panel_endpoint,
					'custom_url' => $custom_panel_url,
					'page_id'    => 0,
					'page_title' => '',
					'list_type'  => $list_type,
				),
				$options
			);

			$action_params = $action ? explode( '/', $action ) : array();
			$view          = $action_params[0] ?? false;
			$data          = $action_params[1] ?? '';

			if ( 'quest-page' !== $args['user_page'] && is_user_logged_in() ) {
				$wishlist_url = apply_filters( 'wlfmc_user_logged_in_wishlist_url', 'custom-panel' === $args['user_page'] && '' !== trim( $args['custom_url'] ) ? esc_url( $args['custom_url'] ) : wc_get_account_endpoint_url( '' === $args['endpoint'] ? $list_type : $args['endpoint'] ) );

				if ( 'view' === $view && ! empty( $data ) ) {
					$wishlist_url = add_query_arg(
						array(
							$this->wishlist_param => 'view',
							'wishlist_id'         => $data,
						),
						$wishlist_url
					);
				} elseif ( $action ) {
					$wishlist_url = add_query_arg(
						array(
							$this->wishlist_param => $view,
						),
						$wishlist_url
					);
				}
			} elseif ( 'last_operation' === $action ) {
				$wishlist_url = $this->get_last_operation_url( $list_type );
			} else {
				if ( 'view' === $view && ! empty( $data ) ) {
					$wishlist_url = add_query_arg(
						array(
							$this->wishlist_param => 'view',
							'wishlist_id'         => $data,
						),
						$this->get_wishlist_url( $list_type )
					);
				} else {
					$wishlist_url = $this->get_wishlist_url( $list_type, $action );
				}
			}

			if ( $request_list !== $args['list_type'] && 'tabbed' === $args['list_type'] && 'view' !== $view ) {
				$wishlist_url = add_query_arg( $this->wishlist_param, $request_list, $wishlist_url );
			}
			return $wishlist_url;
		}

		/**
		 * Generates Add to Wishlist url, to use when customer do not have js enabled
		 *
		 * @param int   $product_id Product id to add to wishlist.
		 * @param array $args Any of the following parameters
		 *   [
		 *       'base_url' => ''
		 *       'wishlist_id' => 0,
		 *       'quantity' => 1,
		 *       'user_id' => false,
		 *       'dateadded' => '',
		 *       'wishlist_name' => '',
		 *       'wishlist_visibility' => 0
		 *   ].
		 *
		 * @return string Add to wishlist url
		 */
		public function get_add_to_wishlist_url( int $product_id, array $args = array() ): string {
			$args = array_merge(
				array(
					'add_to_wishlist' => $product_id,
				),
				$args
			);

			if ( isset( $args['base_url'] ) ) {
				$base_url = $args['base_url'];
				unset( $args['base_url'] );

				$url = add_query_arg( $args, $base_url );
			} else {
				$url = add_query_arg( $args );
			}

			return apply_filters( 'wlfmc_add_to_wishlist_url', esc_url_raw( $url ), $product_id, $args );
		}

		/**
		 * Build the URL used to remove an item from the wishlist.
		 *
		 * @param int $item_id Item ID.
		 *
		 * @return string
		 */
		public function get_remove_url( int $item_id ): string {
			return esc_url( add_query_arg( 'remove_from_wishlist', $item_id ) );
		}

		/**
		 * Returns available views for wishlist page
		 *
		 * @return string[]
		 */
		public function get_available_views(): array {
			return apply_filters( 'wlfmc_available_wishlist_views', array( 'view', 'user' ) );
		}

		/**
		 * Return url to unsubscribe from wishlist mailing lists
		 *
		 * @param int $customer_id  customer id.
		 *
		 * @return string Unsubscribe url
		 */
		public function get_unsubscribe_url( int $customer_id ): string {

			$customer = wlfmc_get_customer( $customer_id );
			if ( ! $customer ) {
				return '';
			}
			$unsubscribe_token            = $customer->get_unsubscribe_token();
			$unsubscribe_token_expiration = $customer->get_unsubscribe_expiration();

			// if user has no token, or previous token has expired, generate new unsubscribe token.
			if ( ! $unsubscribe_token || $unsubscribe_token_expiration < time() ) {
				$unsubscribe_token            = wp_generate_password( 24, false );
				$unsubscribe_token_expiration = apply_filters( 'wlfmc_unsubscribe_token_expiration', time() + 30 * DAY_IN_SECONDS, $unsubscribe_token );

				$customer->set_unsubscribe_token( $unsubscribe_token );
				$customer->set_unsubscribe_expiration( $unsubscribe_token_expiration );
			}

			return apply_filters(
				'wlfmc_unsubscribe_url',
				add_query_arg(
					array(
						'wlfmc_unsubscribe' => $unsubscribe_token,
						'cid'               => $customer_id,
					),
					get_permalink( wc_get_page_id( 'shop' ) )
				),
				$customer_id,
				$unsubscribe_token,
				$unsubscribe_token_expiration
			);
		}

		/* === POLYLANG INTEGRATION === */

		/**
		 * Filters translation url for the wishlist page, when PolyLang is enabled
		 *
		 * @param string $url Translation url.
		 *
		 * @return string Filtered translation url for current page/post.
		 */
		public function get_pll_wishlist_url( $url ) {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( ( apply_filters( 'wlfmc_is_pll_wishlist_url', false ) || wlfmc_is_wishlist_page() || wlfmc_is_multi_list_page() || wlfmc_is_waitlist_page() || wlfmc_is_tabbed_page() ) && isset( $_GET[ $this->wishlist_param ] ) ) {
				$wishlist_action = sanitize_text_field( wp_unslash( $_GET[ $this->wishlist_param ] ) );
				$user_id         = isset( $_GET['user_id'] ) ? sanitize_text_field( wp_unslash( $_GET['user_id'] ) ) : '';
				$wishlist_id     = isset( $_GET['wishlist_id'] ) ? sanitize_text_field( wp_unslash( $_GET['wishlist_id'] ) ) : '';

				$params = array_filter(
					array(
						$this->wishlist_param => $wishlist_action,
						'user_id'             => $user_id,
						'wishlist_id'         => $wishlist_id,
					)
				);

				$url = add_query_arg( $params, $url );
			}
			// phpcs:enable WordPress.Security.NonceVerification
			return $url;
		}
	}
}

/**
 * Unique access to instance of WLFMC class
 *
 * @return WLFMC
 */
function WLFMC(): WLFMC {  // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return WLFMC::get_instance();
}
