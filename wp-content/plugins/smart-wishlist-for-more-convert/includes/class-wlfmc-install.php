<?php
/**
 * Smart Wishlist Install
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.3.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'WLFMC_Install' ) ) {
	/**
	 * Install plugin table and create the wishlist page
	 */
	class WLFMC_Install {

		/**
		 * Single instance of the class
		 *
		 * @var WLFMC_Install
		 */
		protected static $instance;

		/**
		 * Customers table name
		 *
		 * @var string
		 * @accces private
		 */
		private $table_customers;

		/**
		 * Items table name
		 *
		 * @var string
		 * @access private
		 */
		private $table_items;

		/**
		 * Items table name
		 *
		 * @var string
		 * @access private
		 */
		private $table_wishlists;

		/**
		 * Email table name
		 *
		 * @var string
		 * @access private
		 */
		private $table_offers;

		/**
		 * Email automation table name
		 *
		 * @var string
		 */
		private $table_automations;

		/**
		 * Analytics table name
		 *
		 * @var string
		 */
		private $table_analytics;

		/**
		 * Returns single instance of the class
		 *
		 * @return WLFMC_Install
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @version 1.4.0
		 */
		public function __construct() {
			global $wpdb;

			// define local private attribute.
			$this->table_customers   = $wpdb->prefix . 'wlfmc_wishlist_customers';
			$this->table_items       = $wpdb->prefix . 'wlfmc_wishlist_items';
			$this->table_wishlists   = $wpdb->prefix . 'wlfmc_wishlists';
			$this->table_automations = $wpdb->prefix . 'wlfmc_wishlist_automations';
			$this->table_offers      = $wpdb->prefix . 'wlfmc_wishlist_offers';
			$this->table_analytics   = $wpdb->prefix . 'wlfmc_wishlist_analytics';

			// add custom field to global $wpdb.
			$wpdb->wlfmc_wishlist_customers   = $this->table_customers;
			$wpdb->wlfmc_wishlist_items       = $this->table_items;
			$wpdb->wlfmc_wishlists            = $this->table_wishlists;
			$wpdb->wlfmc_wishlist_automations = $this->table_automations;
			$wpdb->wlfmc_wishlist_offers      = $this->table_offers;
			$wpdb->wlfmc_wishlist_analytics   = $this->table_analytics;

		}

		/**
		 * Init db structure of the plugin
		 */
		public function init() {
			$this->add_tables();
			$this->add_pages();
			wlfmc_create_default_automations();

			$this->register_current_version();
		}


		/**
		 * Register current version of plugin and database structure
		 */
		public function register_current_version() {
			delete_option( 'wlfmc_version' );
			update_option( 'wlfmc_version', WLFMC_VERSION );

			delete_option( 'wlfmc_db_version' );
			update_option( 'wlfmc_db_version', WLFMC_DB_VERSION );
		}

		/**
		 * Check if the table of the plugin already exists.
		 *
		 * @return bool
		 * @veriaon 1.3.3
		 */
		public function is_installed() {
			global $wpdb;
			$number_of_tables = $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'wlfmc_wishlist%' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

			return 3 <= $number_of_tables;
		}

		/**
		 * Add tables for a fresh installation
		 *
		 * @return void
		 * @access private
		 *
		 * @version 1.0.1
		 */
		private function add_tables() {
			if ( ! $this->is_installed() ) {
				$this->add_customer_table();
				$this->add_wishlists_table();
				$this->add_items_table();
				$this->add_automations_table();
				$this->add_offer_table();
				$this->add_analytics_table();
			}
		}

		/**
		 * Add the customer table to the database.
		 *
		 * @return void
		 * @access private
		 */
		private function add_customer_table() {
			$sql = "CREATE TABLE IF NOT EXISTS $this->table_customers (
							customer_id BIGINT( 20 ) NOT NULL AUTO_INCREMENT,
    						order_customer_id TEXT DEFAULT NULL,
    						user_id	BIGINT( 20 ) unsigned DEFAULT NULL,
							session_id VARCHAR( 255 ) DEFAULT NULL,
    						first_name VARCHAR(250) DEFAULT '',
    						last_name VARCHAR(250) DEFAULT '',
    						email VARCHAR(100) DEFAULT '',
    						phone VARCHAR(20) DEFAULT '',
    						token VARCHAR( 64 ),
    						lang VARCHAR( 7 ) DEFAULT '',
    						notes LONGTEXT,
    						customer_meta LONGTEXT,
    						unsubscribed TINYINT( 1 ) NOT NULL DEFAULT 0,
							email_verified TINYINT( 1 ) NOT NULL DEFAULT 0,
    						phone_verified TINYINT( 1 ) NOT NULL DEFAULT 0,
    						dateadded timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							expiration timestamp NULL DEFAULT NULL,
    						unsubscribe_token VARCHAR( 64 ),
    						unsubscribe_expiration timestamp NULL DEFAULT NULL,
							PRIMARY KEY  ( customer_id ),
							UNIQUE KEY user_id (user_id),
    						UNIQUE KEY session_id (session_id),
							KEY token (token)
						) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		/**
		 * Add the wishlists table to the database.
		 *
		 * @return void
		 * @access private
		 */
		private function add_wishlists_table() {

			$sql = "CREATE TABLE $this->table_wishlists (
							ID BIGINT( 20 ) NOT NULL AUTO_INCREMENT,
							customer_id BIGINT( 20 ) NOT NULL DEFAULT 0,
							user_id BIGINT( 20 ) NULL DEFAULT NULL,
							session_id VARCHAR( 255 ) DEFAULT NULL,
							wishlist_slug VARCHAR( 200 ) NOT NULL,
							wishlist_name TEXT,
							wishlist_desc TEXT,
							wishlist_token VARCHAR( 64 ) NOT NULL UNIQUE,
							wishlist_privacy TINYINT( 1 ) NOT NULL DEFAULT 0,
							is_default TINYINT( 1 ) NOT NULL DEFAULT 0,
							dateadded timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							expiration timestamp NULL DEFAULT NULL,
							PRIMARY KEY  ( ID ),
							KEY wishlist_slug ( wishlist_slug )
						) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

		}

		/**
		 * Add the items' table to the database.
		 *
		 * @return void
		 * @access private
		 */
		private function add_items_table() {

			$sql = "CREATE TABLE $this->table_items (
							ID BIGINT( 20 ) NOT NULL AUTO_INCREMENT,
							customer_id BIGINT( 20 ) NOT NULL DEFAULT 0,
							parent_id BIGINT( 20 ) NOT NULL DEFAULT 0,
							prod_id BIGINT( 20 ) NOT NULL,
							quantity INT( 11 ) NOT NULL,
							user_id BIGINT( 20 ) NULL DEFAULT NULL,
							wishlist_id BIGINT( 20 ) NULL,
							position INT( 11 ) DEFAULT 0,
							note VARCHAR (250),
							product_meta TEXT NULL,
							posted_data TEXT NULL,
							cart_item_key TEXT NULL,
							importance TINYINT( 1 ) NOT NULL DEFAULT 0,
							original_price DECIMAL( 18,3 ) NULL DEFAULT NULL,
							original_currency CHAR( 3 ) NULL DEFAULT NULL,
							dateadded timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							on_sale tinyint NOT NULL DEFAULT 0,
							back_in_stock tinyint NOT NULL DEFAULT 0,
							price_change tinyint NOT NULL DEFAULT 0,
							low_stock tinyint NOT NULL DEFAULT 0,
							PRIMARY KEY  ( ID ),
							KEY prod_id ( prod_id )
						) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

		}


		/**
		 * Add the email automations table to the database.
		 *
		 * @return void
		 * @access private
		 *
		 * @version 1.3.3
		 */
		private function add_automations_table() {

			$sql = "CREATE TABLE $this->table_automations (
							ID BIGINT( 20 ) NOT NULL AUTO_INCREMENT,
							automation_name TEXT,
						    trigger_name TEXT,
							options LONGTEXT,
							created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							is_active TINYINT( 1 ) NOT NULL DEFAULT 0,
							is_special TINYINT( 1 ) NOT NULL DEFAULT 0,
							is_pro TINYINT( 1 ) NOT NULL DEFAULT 0,
							PRIMARY KEY  ( ID )
						) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

		}

		/**
		 * Add the email table to the database.
		 *
		 * @return void
		 * @access private
		 *
		 * @version 1.0.1
		 */
		private function add_offer_table() {

			$sql = "CREATE TABLE $this->table_offers (
							ID BIGINT( 20 ) NOT NULL AUTO_INCREMENT,
							automation_id BIGINT( 20 ) NULL,
							customer_id BIGINT( 20 ) NOT NULL DEFAULT 0,
							wishlist_id BIGINT( 20 ) NULL,
							list_type VARCHAR(20) NOT NULL DEFAULT 'wishlist',
							has_coupon  TINYINT( 1 ) NOT NULL DEFAULT 0,
							coupon_id BIGINT( 20 ) NULL,
							product_id BIGINT( 20 ) NULL,
							email_options LONGTEXT,
							cache TEXT NULL,
							email_key INT(1),
							track_code varchar(50) NULL,
							days SMALLINT( 3 ) NULL DEFAULT 0,
							net DOUBLE,
							dateadded timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							datesend timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							datesent timestamp,
							status varchar( 200 ) NOT NULL DEFAULT 'sending' comment 'sending ,not-send , sent ,  opened ,  clicked, coupon-used,unsubscribed',
							PRIMARY KEY  ( ID )
						) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

		}


		/**
		 * Add the analytics table to the database.
		 *
		 * @return void
		 * @access private
		 *
		 * @version 1.4.0
		 */
		private function add_analytics_table() {

			$sql = "CREATE TABLE $this->table_analytics (
							ID BIGINT( 20 ) NOT NULL AUTO_INCREMENT,
							customer_id BIGINT( 20 ) NOT NULL DEFAULT 0,
							prod_id BIGINT( 20 ) NOT NULL,
							quantity INT( 11 ) NOT NULL,
							wishlist_id BIGINT( 20 ) NULL,
							order_id BIGINT( 20 ) NULL,
							type VARCHAR(20) NOT NULL DEFAULT 'add-to-list' comment 'add-to-list ,buy-through-list , buy-through-coupon',
							list_type VARCHAR(20) NOT NULL DEFAULT 'wishlist' comment 'wishlist or all new list types in premium version',
							price DECIMAL( 18,3 ) NULL DEFAULT NULL,
							currency CHAR( 3 ) NULL DEFAULT NULL,
							dateadded timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							datepurchased timestamp NULL DEFAULT NULL,
							PRIMARY KEY  ( ID )
						) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

		}

		/**
		 * Update db structure and options of the plugin
		 *
		 * @param string $current_version Version from which we're updating.
		 * @param string $current_db_version Version from which we're updating.
		 *
		 * @since 1.0.1
		 * @version 1.2.0
		 */
		public function update( string $current_version, string $current_db_version ) {

			if ( version_compare( $current_db_version, '1.0.1', '<' ) ) {
				$this->update_1_0_1();
			}

			if ( version_compare( $current_version, '1.2.0', '<' ) ) {

				$this->update_1_2_0();
			}

			if ( version_compare( $current_version, '1.3.0', '<' ) ) {

				$this->update_1_3_0();
			}

			if ( version_compare( $current_version, '1.3.1', '<' ) ) {

				$this->update_1_3_1();
			}

			if ( version_compare( $current_version, '1.3.2', '<' ) ) {

				$this->update_1_3_2();
			}

			if ( version_compare( $current_db_version, '1.1.0', '<' ) ) {

				$this->add_automations_table();

			}

			if ( version_compare( $current_version, '1.3.3', '<' ) ) {

				$this->update_1_3_3();
			}

			if ( version_compare( $current_version, '1.4.0', '<' ) ) {

				$this->update_1_4_0();
			}

			if ( version_compare( $current_db_version, '1.2.0', '<' ) ) {

				$this->add_analytics_table();
			}

			if ( version_compare( $current_db_version, '1.2.1', '<' ) ) {

				$this->update_1_4_1();
			}

			if ( version_compare( $current_db_version, '1.2.2', '<' ) ) {

				$this->update_1_4_2();
			}

			if ( version_compare( $current_db_version, '1.2.3', '<' ) ) {

				$this->update_1_4_3();
			}

			if ( version_compare( $current_db_version, '1.2.4', '<' ) ) {

				$this->update_1_4_4();
			}

			if ( version_compare( $current_db_version, '1.2.5', '<' ) ) {

				$this->update_1_4_5();
			}

			if ( version_compare( $current_version, '1.5.0', '<' ) ) {

				$this->update_1_5_0();
			}

			if ( version_compare( $current_db_version, '1.2.6', '<' ) ) {

				$this->update_1_5_2();
			}

			if ( version_compare( $current_version, '1.5.2', '<' ) ) {

				$this->update_1_5_2_1();
			}

			if ( version_compare( $current_version, '1.5.4', '<' ) ) {

				$this->update_1_5_4();
			}

			if ( version_compare( $current_db_version, '1.2.7', '<' ) ) {

				$this->update_1_5_7();
			}

			if ( version_compare( $current_version, '1.5.9', '<' ) ) {

				$this->update_1_5_9();
			}

			if ( version_compare( $current_db_version, '1.2.8', '<' ) ) {

				$this->update_1_5_9_1();
				wlfmc_create_default_automations();
			}

			if ( version_compare( $current_db_version, '1.2.9', '<' ) ) {

				$this->update_1_6_0();
			}

			if ( version_compare( $current_db_version, '1.3.0', '<' ) ) {

				$this->update_1_6_3();
			}

			if ( version_compare( $current_db_version, '1.3.1', '<' ) ) {

				$this->update_1_6_9();
			}

			if ( version_compare( $current_version, '1.7.0', '<' ) ) {

				$this->update_1_7_0();
			}

			$this->register_current_version();
		}

		/**
		 * Update from 1.0.0 to 1.0.1
		 *
		 * @since 1.0.1
		 */
		private function update_1_0_1() {
			global $wpdb;
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->wlfmc_wishlist_offers';" ) ) {
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_offers` LIKE 'days';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_offers ADD `days` SMALLINT( 3 ) NULL DEFAULT 0;" );
				}
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_offers` LIKE 'product_id';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_offers ADD `product_id` BIGINT( 20 ) NULL;" );
				}
			}
			// phpcs:enable WordPress.DB.DirectDatabaseQuery
		}

		/**
		 * Update to 1.2.0
		 *
		 * @since 1.2.0
		 */
		private function update_1_2_0() {

			set_transient( '_wlfmc_wishlist_activation_redirect', true, 30 );

			update_option( 'smart-wishlist-for-more-convert_tracking_notice', 'hide' );

			update_option( 'wlfmc-skip-wizard-notice', true );

			update_option( 'wlfmc-finish-wizard-notice', true );

			// If our option doesn't exist already, we'll create it with today's timestamp.
			if ( ! get_option( 'wlfmc_wishlist_activation_date' ) ) {
				update_option( 'wlfmc_wishlist_activation_date', gmdate( 'Y-m-d' ) );
			}
		}

		/**
		 * Update to 1.3.0
		 *
		 * @since 1.3.0
		 */
		private function update_1_3_0() {

			$options = new MCT_Options( 'wlfmc_options' );
			if ( 'before_image' === $options->get_option( 'loop_position' ) ) {
				$options->update_option( 'loop_position', 'image_top_left' );
			}
			if ( 'thumbnails' === $options->get_option( 'wishlist_button_position' ) ) {
				$options->update_option( 'wishlist_button_position', 'image_top_left' );
			}
			if ( 'before_image' === $options->get_option( 'gutenberg_position' ) ) {
				$options->update_option( 'gutenberg_position', 'image_top_left' );
			}

			$options->update_option( 'enable_for_outofstock_product', '1' );
		}

		/**
		 * Update to 1.3.1
		 *
		 * @since 1.3.1
		 */
		private function update_1_3_1() {

			$options = new MCT_Options( 'wlfmc_options' );
			$options->update_option( 'show_login_notice_for_guests', $options->get_option( 'show_login_notice_for_quests' ) );
		}

		/**
		 * Update to 1.3.2
		 *
		 * @since 1.3.2
		 */
		private function update_1_3_2() {

			$options               = new MCT_Options( 'wlfmc_options' );
			$wishlist_button_style = $options->get_option( 'wishlist_button_style' );
			if ( is_array( $wishlist_button_style ) ) {
				if ( isset( $wishlist_button_style['color'] ) ) {
					$options->update_option( 'wishlist_button_color', $wishlist_button_style['color'] );
				}
				if ( isset( $wishlist_button_style['color-hover'] ) ) {
					$options->update_option( 'wishlist_button_hover_color', $wishlist_button_style['color-hover'] );
				}
				if ( isset( $wishlist_button_style['background'] ) ) {
					$options->update_option( 'wishlist_button_background_color', $wishlist_button_style['background'] );
				}
				if ( isset( $wishlist_button_style['background-hover'] ) ) {
					$options->update_option( 'wishlist_button_background_hover_color', $wishlist_button_style['background-hover'] );
				}
				if ( isset( $wishlist_button_style['border'] ) ) {
					$options->update_option( 'wishlist_button_border_color', $wishlist_button_style['border'] );
				}
				if ( isset( $wishlist_button_style['border-hover'] ) ) {
					$options->update_option( 'wishlist_button_border_hover_color', $wishlist_button_style['border-hover'] );
				}
			}

			$icon_style_single  = $options->get_option( 'icon_style_single' );
			$text_style_single  = $options->get_option( 'text_style_single' );
			$button_type_single = $options->get_option( 'button_type_single' );
			if ( is_array( $icon_style_single ) ) {
				if ( isset( $icon_style_single['color'] ) ) {
					$options->update_option( 'icon_color_single', $icon_style_single['color'] );
				}
				if ( isset( $icon_style_single['color-hover'] ) ) {
					$options->update_option( 'icon_hover_color_single', $icon_style_single['color-hover'] );
				}
			}
			if ( is_array( $text_style_single ) ) {
				if ( isset( $text_style_single['color'] ) ) {
					$options->update_option( 'text_color_single', $text_style_single['color'] );
				}
				if ( isset( $text_style_single['color-hover'] ) ) {
					$options->update_option( 'text_hover_color_single', $text_style_single['color-hover'] );
				}
			}

			if ( 'icon' === $button_type_single && is_array( $icon_style_single ) ) {
				if ( isset( $icon_style_single['background'] ) ) {
					$options->update_option( 'button_background_color_single', $icon_style_single['background'] );
				}
				if ( isset( $icon_style_single['background-hover'] ) ) {
					$options->update_option( 'button_background_hover_color_single', $icon_style_single['background-hover'] );
				}
				if ( isset( $icon_style_single['border'] ) ) {
					$options->update_option( 'button_border_color_single', $icon_style_single['border'] );
				}
				if ( isset( $icon_style_single['border-hover'] ) ) {
					$options->update_option( 'button_border_hover_color_single', $icon_style_single['border-hover'] );
				}
			} elseif ( is_array( $text_style_single ) ) {
				if ( isset( $text_style_single['background'] ) ) {
					$options->update_option( 'button_background_color_single', $text_style_single['background'] );
				}
				if ( isset( $text_style_single['background-hover'] ) ) {
					$options->update_option( 'button_background_hover_color_single', $text_style_single['background-hover'] );
				}
				if ( isset( $text_style_single['border'] ) ) {
					$options->update_option( 'button_border_color_single', $text_style_single['border'] );
				}
				if ( isset( $text_style_single['border-hover'] ) ) {
					$options->update_option( 'button_border_hover_color_single', $text_style_single['border-hover'] );
				}
			}

			$icon_style_loop  = $options->get_option( 'icon_style_loop' );
			$text_style_loop  = $options->get_option( 'text_style_loop' );
			$button_type_loop = $options->get_option( 'button_type_loop' );
			if ( is_array( $icon_style_loop ) ) {
				if ( isset( $icon_style_loop['color'] ) ) {
					$options->update_option( 'icon_color_loop', $icon_style_loop['color'] );
				}
				if ( isset( $icon_style_loop['color-hover'] ) ) {
					$options->update_option( 'icon_hover_color_loop', $icon_style_loop['color-hover'] );
				}
			}

			if ( is_array( $text_style_loop ) ) {
				if ( isset( $text_style_loop['color'] ) ) {
					$options->update_option( 'text_color_loop', $text_style_loop['color'] );
				}
				if ( isset( $text_style_loop['color-hover'] ) ) {
					$options->update_option( 'text_hover_color_loop', $text_style_loop['color-hover'] );
				}
			}

			if ( 'icon' === $button_type_loop && is_array( $icon_style_loop ) ) {
				if ( isset( $icon_style_loop['background'] ) ) {
					$options->update_option( 'button_background_color_loop', $icon_style_loop['background'] );
				}
				if ( isset( $icon_style_loop['background-hover'] ) ) {
					$options->update_option( 'button_background_hover_color_loop', $icon_style_loop['background-hover'] );
				}
				if ( isset( $icon_style_loop['border'] ) ) {
					$options->update_option( 'button_border_color_loop', $icon_style_loop['border'] );
				}
				if ( isset( $icon_style_loop['border-hover'] ) ) {
					$options->update_option( 'button_border_hover_color_loop', $icon_style_loop['border-hover'] );
				}
			} elseif ( is_array( $text_style_loop ) ) {
				if ( isset( $text_style_loop['background'] ) ) {
					$options->update_option( 'button_background_color_loop', $text_style_loop['background'] );
				}
				if ( isset( $text_style_loop['background-hover'] ) ) {
					$options->update_option( 'button_background_hover_color_loop', $text_style_loop['background-hover'] );
				}
				if ( isset( $text_style_loop['border'] ) ) {
					$options->update_option( 'button_border_color_loop', $text_style_loop['border'] );
				}
				if ( isset( $text_style_loop['border-hover'] ) ) {
					$options->update_option( 'button_border_hover_color_loop', $text_style_loop['border-hover'] );
				}
			}
		}

		/**
		 * Update to 1.3.3
		 *
		 * @since 1.3.3
		 */
		private function update_1_3_3() {
			global $wpdb;

			wp_clear_scheduled_hook( 'wlfmc_send_offer_emails' );

			$options                  = new MCT_Options( 'wlfmc_options' );
			$single_position          = $options->get_option( 'wishlist_button_position', 'after_add_to_cart' );
			$loop_position            = $options->get_option( 'loop_position', 'after_add_to_cart' );
			$remove_after_add_to_cart = wlfmc_is_true( $options->get_option( 'remove_after_add_to_cart', true ) );
			$button_label_add         = $options->get_option( 'button_label_add_single', esc_html__( 'Add To Wishlist', 'wc-wlfmc-wishlist' ) );
			$button_label_view        = $options->get_option( 'button_label_view_single', esc_html__( 'View My Wishlist', 'wc-wlfmc-wishlist' ) );
			$button_label_remove      = $options->get_option( 'button_label_remove_single', esc_html__( 'Remove From Wishlist', 'wc-wlfmc-wishlist' ) );

			$options->update_option( 'button_label_add', $button_label_add );
			$options->update_option( 'button_label_view', $button_label_view );
			$options->update_option( 'button_label_remove', $button_label_remove );

			if ( $remove_after_add_to_cart ) {
				$options->update_option( 'remove_from_wishlist', 'added-to-cart' );
			}
			if ( in_array(
				$single_position,
				array(
					'image_top_left',
					'image_top_right',
					'image_bottom_left',
					'image_bottom_right',
				),
				true
			) ) {
				$options->update_option( 'button_type_single', 'icon' );
			}
			if ( in_array(
				$loop_position,
				array(
					'image_top_left',
					'image_top_right',
				),
				true
			) ) {
				$options->update_option( 'button_type_loop', 'icon' );
			}
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->wlfmc_wishlist_offers';" ) ) {
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_offers` LIKE 'automation_id';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_offers ADD `automation_id` BIGINT( 20 ) NULL;" );
				}
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_offers` LIKE 'net';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_offers ADD `net` DOUBLE ;" );
				}
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_offers` LIKE 'track_code';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_offers ADD `track_code` VARCHAR(50) NULL;" );
				}
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_offers` LIKE 'email_key';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_offers ADD `email_key` INT(1) ;" );
				}

				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_offers` LIKE 'status';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_offers ADD `status`  varchar( 200 ) NOT NULL DEFAULT 'sending' comment 'sending ,not-send , sent ,  opened ,  clicked, coupon-used' ;" );
				}
				if ( $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_offers` LIKE 'datesent';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_offers MODIFY `datesent` timestamp ;" );
				}
			}
			// phpcs:enable WordPress.DB.DirectDatabaseQuery
			$min_total          = $options->get_option( 'minimum-wishlist-total', 1 );
			$min_count          = $options->get_option( 'minimum-wishlist-count', 1 );
			$include_products   = maybe_unserialize( $options->get_option( 'include-product' ) );
			$period_days        = (int) $options->get_option( 'period-days' );
			$automation_options = array(
				'minimum-wishlist-total'       => $min_total,
				'minimum-wishlist-count'       => $min_count,
				'include-product'              => $include_products,
				'period-days'                  => $period_days,
				'discount-type'                => $options->get_option( 'discount-type', 'fixed_cart' ),
				'coupon-amount'                => $options->get_option( 'coupon-amount' ),
				'individual-use'               => $options->get_option( 'individual-use', '0' ),
				'exclude-sale-items'           => $options->get_option( 'exclude-sale-items', '0' ),
				'free-shipping'                => $options->get_option( 'free-shipping', '0' ),
				'expiry-date'                  => $options->get_option( 'expiry-date' ),
				'user-restriction'             => $options->get_option( 'user-restriction', '0' ),
				'delete-after-expired'         => $options->get_option( 'delete-after-expired', '0' ),
				'email-from-name'              => $options->get_option( 'email-from-name' ),
				'email-from-address'           => $options->get_option( 'email-from-address' ),
				'mail-type'                    => $options->get_option( 'mail-type', 'html' ),
				'email-template-logo'          => $options->get_option( 'email-template-logo' ),
				'email-template-avatar'        => $options->get_option( 'email-template-avatar' ),
				'email-template-customer-name' => $options->get_option( 'email-template-customer-name' ),
				'email-template-customer-job'  => $options->get_option( 'email-template-customer-job' ),
				'email-template-facebook'      => $options->get_option( 'email-template-facebook' ),
				'email-template-linkedin'      => $options->get_option( 'email-template-linkedin' ),
				'email-template-instagram'     => $options->get_option( 'email-template-instagram' ),
				'offer_emails'                 => $options->get_option( 'offer_emails' ),
			);
			$insert             = $wpdb->insert(  // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$this->table_automations,
				array(
					'automation_name' => esc_html__( 'Default', 'wc-wlfmc-wishlist' ),
					'options'         => serialize( $automation_options ),// @codingStandardsIgnoreLine.
				),
				array(
					'%s',
					'%s',
				)
			);
			if ( is_wp_error( $insert ) ) {
				/**
				 * Variable
				 *
				 * @var WP_Error $insert WordPress error.
				 */
				new WP_Error( 'Create_Automation_table_error', $insert->get_error_message() );
			} else {
				$wpdb->query( "TRUNCATE TABLE $wpdb->wlfmc_wishlist_offers" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

			}

		}

		/**
		 * Update to 1.4.0
		 *
		 * @since 1.4.0
		 */
		private function update_1_4_0() {
			$options                   = new MCT_Options( 'wlfmc_options' );
			$remove_after_second_click = wlfmc_is_true( $options->get_option( 'remove_after_second_click', '0' ) );
			if ( $remove_after_second_click ) {
				$options->update_option( 'after_second_click', 'remove' );
			}
		}

		/**
		 * Update Db from 1.2.0 to 1.2.1
		 *
		 * @since 1.4.1
		 */
		private function update_1_4_1() {
			global $wpdb;
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->wlfmc_wishlist_items';" ) ) {
				if ( $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_items` LIKE 'original_price';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_items CHANGE `original_price` `original_price` DECIMAL( 18,3 ) NULL DEFAULT NULL;" );
				}
			}
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->wlfmc_wishlist_analytics';" ) ) {
				if ( $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_analytics` LIKE 'price';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_analytics CHANGE `price` `price` DECIMAL( 18,3 ) NULL DEFAULT NULL;" );
				}
			}
			// phpcs:enable WordPress.DB.DirectDatabaseQuery
		}

		/**
		 * Update Db from 1.2.1 to 1.2.2
		 *
		 * @since 1.4.2
		 */
		private function update_1_4_2() {
			global $wpdb;
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->wlfmc_wishlist_items';" ) ) {
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_items` LIKE 'product_meta';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_items ADD `product_meta` TEXT NULL;" );
				}
			}
			// phpcs:enable WordPress.DB.DirectDatabaseQuery
		}

		/**
		 * Update Db from 1.2.1 to 1.2.3
		 *
		 * @since 1.4.3
		 */
		private function update_1_4_3() {
			global $wpdb;
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->wlfmc_wishlist_items';" ) ) {
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_items` LIKE 'posted_data';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_items ADD `posted_data` TEXT NULL;" );
				}
			}
			// phpcs:enable WordPress.DB.DirectDatabaseQuery
		}

		/**
		 * Update Db from 1.2.13 to 1.2.4
		 *
		 * @since 1.4.4
		 */
		private function update_1_4_4() {
			global $wpdb;
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->wlfmc_wishlist_items';" ) ) {
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_items` LIKE 'parent_id';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_items ADD `parent_id` BIGINT( 20 ) NOT NULL DEFAULT 0;" );
				}
			}
			// phpcs:enable WordPress.DB.DirectDatabaseQuery
		}

		/**
		 * Update Db from 1.2.4 to 1.2.5
		 *
		 * @since 1.5.0
		 */
		private function update_1_4_5() {
			global $wpdb;
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->wlfmc_wishlist_items';" ) ) {
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_items` LIKE 'cart_item_key';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_items ADD `cart_item_key` TEXT NULL;" );
				}
			}
			// phpcs:enable WordPress.DB.DirectDatabaseQuery
		}

		/**
		 * Update to 1.5.0
		 *
		 * @since 1.5.0
		 */
		private function update_1_5_0() {
			$options         = new MCT_Options( 'wlfmc_options' );
			$enable_dropdown = wlfmc_is_true( $options->get_option( 'enable_counter_mini_wishlist_dropdown', '0' ) );
			if ( wlfmc_is_true( $enable_dropdown ) ) {
				$enable_on_hover = $options->get_option( 'show_counter_mini_wishlist_on_hover', '0' );
				$options->update_option( 'display_mini_wishlist_for_counter', wlfmc_is_true( $enable_on_hover ) ? 'on-hover' : 'on-click' );
			}
			if ( class_exists( 'Elementor\Plugin' ) ) {
				// Elementor is active.
				global $wpdb;

				$post_ids = $wpdb->get_col( //phpcs:ignore WordPress.DB.DirectDatabaseQuery
					'SELECT post_id FROM ' . $wpdb->postmeta . ' WHERE meta_key = "_elementor_data" AND `meta_value` LIKE \'%"widgetType":"wlfmc-wishlist-counter"%\';'
				);

				if ( empty( $post_ids ) ) {
					return;
				}

				foreach ( $post_ids as $post_id ) {
					$do_update = false;
					$document  = Elementor\Plugin::instance()->documents->get( $post_id );

					if ( $document ) {
						$data = $document->get_elements_data();
					}

					if ( empty( $data ) ) {
						continue;
					}

					$data = Elementor\Plugin::instance()->db->iterate_data(
						$data,
						function( $element ) use ( &$do_update ) {

							if ( empty( $element['widgetType'] ) || 'wlfmc-wishlist-counter' !== $element['widgetType'] ) {
								return $element;
							}
							$show_products     = isset( $element['settings']['show_products'] ) && wlfmc_is_true( $element['settings']['show_products'] );
							$show_on_hover     = isset( $element['settings']['show_list_on_hover'] ) && wlfmc_is_true( $element['settings']['show_list_on_hover'] );
							$dropdown_products = isset( $element['settings']['dropdown_products'] ) && wlfmc_is_true( $element['settings']['dropdown_products'] );
							if ( $show_products ) {
								$element['settings']['display_mode'] = $dropdown_products ? ( $show_on_hover ? 'on-hover' : 'on-click' ) : 'mini-wishlist';
							} else {
								$element['settings']['display_mode'] = 'counter-only';
							}
							$element['settings']['position_mode'] = 'absolute';
							$do_update                            = true;
							return $element;
						}
					);

					// Only update if widget has dropdown.
					if ( ! $do_update ) {
						continue;
					}
					// We need the `wp_slash` in order to avoid the unslashing during the `update_post_meta`.
					$json_value = wp_slash( wp_json_encode( $data ) );

					update_metadata( 'post', $post_id, '_elementor_data', $json_value );
				}
			}
		}

		/**
		 * Update Db from 1.2.5 to 1.2.6
		 *
		 * @since 1.5.2
		 */
		private function update_1_5_2() {
			global $wpdb;
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->wlfmc_wishlists';" ) ) {
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlists` LIKE 'wishlist_desc';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlists ADD `wishlist_desc` TEXT NULL;" );
				}
			}
			// phpcs:enable WordPress.DB.DirectDatabaseQuery
		}

		/**
		 * Update to 1.5.2
		 *
		 * @since 1.5.2
		 */
		private function update_1_5_2_1() {
			$options                  = new MCT_Options( 'wlfmc_options' );
			$social_color             = $options->get_option( 'social_color' );
			$view_mode                = $options->get_option( 'wishlist_view_mode', 'list' );
			$tooltip_color            = $options->get_option( 'tooltip_color_single', 'list' );
			$tooltip_background_color = $options->get_option( 'tooltip_background_color_single', 'list' );
			$tooltip_border_radius    = $options->get_option( 'tooltip_border_radius_single', 'list' );

			$options->move_options(
				'button-display',
				'global-settings',
				array(
					'popup_position',
					'popup_background_color',
					'popup_border_color',
					'popup_border_radius',
					'share_items',
					'socials_title',
					'action_label',
					'action_add_to_cart_label',
					'action_remove_label',
					'apply_label',
					'all_add_to_cart_label',
					'share_on_label',
					'share_on_download_pdf_tooltip_label',
					'share_on_copy_link_tooltip_label',
					'share_on_facebook_tooltip_label',
					'share_on_messenger_tooltip_label',
					'share_on_twitter_tooltip_label',
					'share_on_whatsapp_tooltip_label',
					'share_on_telegram_tooltip_label',
					'share_on_email_tooltip_label',
				)
			);
			$options->update_option( 'tooltip_custom_style', '1', 'global-settings' );
			$options->update_option( 'tooltip_color', $tooltip_color, 'global-settings' );
			$options->update_option( 'tooltip_background_color', $tooltip_background_color, 'global-settings' );
			$options->update_option( 'tooltip_border_radius', $tooltip_border_radius, 'global-settings' );

			$options->update_option( 'facebook_color', $social_color, 'global-settings' );
			$options->update_option( 'twitter_color', $social_color, 'global-settings' );
			$options->update_option( 'messenger_color', $social_color, 'global-settings' );
			$options->update_option( 'whatsapp_color', $social_color, 'global-settings' );
			$options->update_option( 'telegram_color', $social_color, 'global-settings' );
			$options->update_option( 'email_color', $social_color, 'global-settings' );
			$options->update_option( 'pdf_color', $social_color, 'global-settings' );
			$options->update_option( 'copy_color', $social_color, 'global-settings' );
			$options->update_option( 'popup_title_color', '#333', 'global-settings' );
			$options->update_option( 'popup_content_color', '#333', 'global-settings' );
			if ( 'list' === $view_mode ) {
				$options->update_option( 'wishlist_table_grid_border_color', 'transparent' );
			}
		}
		/**
		 * Update to 1.5.4
		 *
		 * @since 1.5.4
		 */
		private function update_1_5_4() {
			$options = new MCT_Options( 'wlfmc_options' );
			$options->move_options(
				'button-display',
				'global-settings',
				array(
					'remove_from_wishlist',
					'redirect_after_add_to_cart',
				)
			);
		}

		/**
		 * Update Db from 1.2.6 to 1.2.7
		 *
		 * @since 1.5.7
		 */
		private function update_1_5_7() {
			global $wpdb;
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->wlfmc_wishlist_items';" ) ) {
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_items` LIKE 'back_in_stock';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_items ADD `back_in_stock` tinyint NOT NULL DEFAULT 0;" );
				}
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_items` LIKE 'price_change';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_items ADD `price_change` tinyint NOT NULL DEFAULT 0;" );
				}
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_items` LIKE 'low_stock';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_items ADD `low_stock` tinyint NOT NULL DEFAULT 0;" );
				}
			}
			// phpcs:enable WordPress.DB.DirectDatabaseQuery
		}

		/**
		 * Update Db from 1.2.7 to 1.2.8
		 *
		 * @since 1.5.9
		 */
		private function update_1_5_9_1() {
			global $wpdb;
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->wlfmc_wishlist_automations';" ) ) {
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_automations` LIKE 'is_special';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_automations ADD `is_special` TINYINT( 1 ) NOT NULL DEFAULT 0;" );
				}
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_automations` LIKE 'is_pro';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_automations ADD `is_pro` TINYINT( 1 ) NOT NULL DEFAULT 0;" );
				}
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_automations` LIKE 'trigger_name';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_automations ADD `trigger_name` TEXT;" );
				}
				if ( $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_automations` LIKE 'list_type';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_automations DROP COLUMN `list_type`;" );
				}
			}
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->wlfmc_wishlist_offers';" ) ) {
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_offers` LIKE 'list_type';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_offers ADD `list_type` VARCHAR(20) NOT NULL DEFAULT 'wishlist';" );
				}
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_offers` LIKE 'cache';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_offers ADD `cache` TEXT NULL;" );
				}
			}
			// phpcs:enable WordPress.DB.DirectDatabaseQuery
		}

		/**
		 * Update Db from 1.2.8 to 1.2.9
		 *
		 * @since 1.6.0
		 */
		private function update_1_6_0() {
			global $wpdb;
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->wlfmc_wishlist_analytics';" ) ) {
				if ( $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_analytics` LIKE 'list_type';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_analytics MODIFY `list_type` VARCHAR(20) NOT NULL DEFAULT 'wishlist';" );
					$wpdb->query( "UPDATE $wpdb->wlfmc_wishlist_analytics SET list_type='wishlist' WHERE list_type='default'" );
				}
			}
			// phpcs:enable WordPress.DB.DirectDatabaseQuery
		}

		/**
		 * Update to 1.5.9
		 *
		 * @since 1.5.9
		 */
		private function update_1_5_9() {
			$options = new MCT_Options( 'wlfmc_options' );
			$options->update_option( 'wishlist_endpoint', get_option( 'woocommerce_myaccount_wlfmc_wishlist_endpoint', 'wlfmc-wishlist' ), 'button-display' );
			$options->update_option( 'wishlist_user_page', 'myaccount-page', 'button-display' );
			$options->update_option( 'tabbed_page', get_option( 'wlfmc_wishlist_page_id' ), 'global-settings' );
			$options->update_option( 'global_user_page', 'myaccount-page', 'global-settings' );
			$options->update_option( 'global_endpoint', get_option( 'woocommerce_myaccount_wlfmc_wishlist_endpoint', 'wlfmc-wishlist' ), 'global-settings' );
			$options->update_option( 'global_custom_url', '', 'global-settings' );
			update_option( 'wlfmc_tabbed_page_id', get_option( 'wlfmc_wishlist_page_id' ) );
		}

		/**
		 * Update Db from 1.2.9 to 1.3.0
		 *
		 * @since 1.6.3
		 */
		private function update_1_6_3() {
			global $wpdb;
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->wlfmc_wishlist_analytics';" ) ) {
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_analytics` LIKE 'customer_id';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_analytics ADD `customer_id` BIGINT( 20 ) NOT NULL DEFAULT 0;" );
				}
			}
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->wlfmc_wishlists';" ) ) {
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlists` LIKE 'customer_id';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlists ADD `customer_id` BIGINT( 20 ) NOT NULL DEFAULT 0;" );
				}
			}
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->wlfmc_wishlist_items';" ) ) {
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_items` LIKE 'customer_id';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_items ADD `customer_id` BIGINT( 20 ) NOT NULL DEFAULT 0;" );
				}
			}
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->wlfmc_wishlist_offers';" ) ) {
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_offers` LIKE 'customer_id';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_offers ADD `customer_id` BIGINT( 20 ) NOT NULL DEFAULT 0;" );
				}
			}

			$this->add_customer_table();

			try {
				$wpdb->query( 'START TRANSACTION;' );
				// create customers by wishlists.
				$wpdb->query(
					"INSERT INTO $wpdb->wlfmc_wishlist_customers (customer_id, session_id, user_id)
					SELECT DISTINCT 0 as customer_id, session_id, user_id
					FROM $wpdb->wlfmc_wishlists
					WHERE (session_id, user_id) NOT IN (SELECT session_id, user_id FROM $wpdb->wlfmc_wishlist_customers);"
				);
				$wpdb->query( 'COMMIT;' );
				if ( '' !== $wpdb->last_error ) {
					throw new Exception( $wpdb->last_error );
				}

				if ( $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_analytics` LIKE 'user_id';" ) ) {
					$wpdb->query( 'START TRANSACTION;' );
					// create customers by analytics data.
					$wpdb->query(
						"INSERT INTO $wpdb->wlfmc_wishlist_customers (customer_id, session_id, user_id)
					SELECT DISTINCT 0 as customer_id, NULL as session_id, user_id
					FROM $wpdb->wlfmc_wishlist_analytics
					WHERE user_id NOT IN (SELECT DISTINCT user_id FROM $wpdb->wlfmc_wishlist_customers);"
					);
					$wpdb->query( 'COMMIT;' );
					if ( '' !== $wpdb->last_error ) {
						throw new Exception( $wpdb->last_error );
					}
					$wpdb->query( 'START TRANSACTION;' );
					// update analytics and add customer id.
					$wpdb->query(
						"UPDATE $wpdb->wlfmc_wishlist_analytics as a
						JOIN $wpdb->wlfmc_wishlist_customers as c ON a.user_id = c.user_id
						SET a.customer_id = c.customer_id
						WHERE a.customer_id = 0;"
					);
					$wpdb->query( 'COMMIT;' );
					if ( '' !== $wpdb->last_error ) {
						throw new Exception( $wpdb->last_error );
					}
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_analytics DROP COLUMN `user_id`;" );
				}
				$wpdb->query( 'START TRANSACTION;' );
				// update wishlist and add customer id.
				$wpdb->query(
					"UPDATE $wpdb->wlfmc_wishlists as w
					JOIN $wpdb->wlfmc_wishlist_customers as c ON ( w.session_id = c.session_id AND w.user_id IS NULL) OR ( w.user_id = c.user_id AND w.session_id IS NULL )
					SET w.customer_id = c.customer_id
					WHERE w.customer_id = 0;"
				);
				$wpdb->query( 'COMMIT;' );
				if ( '' !== $wpdb->last_error ) {
					throw new Exception( $wpdb->last_error );
				}
				$wpdb->query( 'START TRANSACTION;' );
				// update wishlist items and add customer id base on user_id.
				$wpdb->query(
					"UPDATE $wpdb->wlfmc_wishlist_items as i
                    JOIN $wpdb->wlfmc_wishlists as w ON i.wishlist_id = w.ID
					SET i.customer_id = w.customer_id
					WHERE i.customer_id = 0;"
				);
				$wpdb->query( 'COMMIT;' );
				if ( '' !== $wpdb->last_error ) {
					throw new Exception( $wpdb->last_error );
				}

				if ( $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_offers` LIKE 'user_id';" ) ) {
					$wpdb->query( 'START TRANSACTION;' );
					// update offers and add customer id.
					$wpdb->query(
						"UPDATE $wpdb->wlfmc_wishlist_offers as o
						JOIN $wpdb->wlfmc_wishlist_customers as c ON o.user_id = c.user_id
						SET o.customer_id = c.customer_id
						WHERE o.customer_id = 0;"
					);
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_offers DROP COLUMN `user_id`;" );
					$wpdb->query( 'COMMIT;' );
					if ( '' !== $wpdb->last_error ) {
						throw new Exception( $wpdb->last_error );
					}
				}

				// phpcs:enable WordPress.DB.DirectDatabaseQuery
			} catch ( Exception $e ) {
				$wpdb->query( 'ROLLBACK;' ); // phpcs:ignore WordPress.DB
				$options = new MCT_Options( 'wlfmc_options' );
				update_option( 'wlfmc_need_update_tables', '1.6.3' );
				update_option( 'wlfmc_wishlist_old_status', $options->get_option( 'wishlist_enable', '1' ) );
				$options->update_option( 'wishlist_enable', '0' );
				/* translators: 1 error message */
				log_me( sprintf( __( 'An error occurred after update to the version 1.6.3: %s', 'wc-wlfmc-wishlist' ), $e->getMessage() ) );
			}
		}

		/**
		 * Update Db from 1.3.0 to 1.3.1
		 *
		 * @since 1.6.9
		 */
		private function update_1_6_9() {
			global $wpdb;
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->wlfmc_wishlist_customers';" ) ) {
				if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `$wpdb->wlfmc_wishlist_customers` LIKE 'lang';" ) ) {
					$wpdb->query( "ALTER TABLE $wpdb->wlfmc_wishlist_customers ADD `lang` VARCHAR(7) DEFAULT '';" );
				}
			}
			// phpcs:enable WordPress.DB.DirectDatabaseQuery
		}

		/**
		 * Update from 1.6.9 to 1.7.0
		 *
		 * @since 1.7.0
		 */
		public function update_1_7_0() {
			if ( function_exists( 'wpml_object_id_filter' ) || function_exists( 'icl_object_id' ) ) {
				update_option( 'wlfmc_need_update_tables', '1.7.0' );
			}

		}
		/**
		 * Add a page "Wishlist".
		 *
		 * @return void
		 */
		private function add_pages() {
			wc_create_page(
				sanitize_title_with_dashes( _x( 'wishlist', 'page_slug', 'wc-wlfmc-wishlist' ) ),
				'wlfmc_wishlist_page_id',
				__( 'Wishlist', 'wc-wlfmc-wishlist' ),
				'<!-- wp:shortcode -->[wlfmc_wishlist]<!-- /wp:shortcode -->'
			);
		}
	}
}

/**
 * Unique access to instance of WLFMC_Install class
 *
 * @return WLFMC_Install
 */
function WLFMC_Install(): WLFMC_Install { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return WLFMC_Install::get_instance();
}
