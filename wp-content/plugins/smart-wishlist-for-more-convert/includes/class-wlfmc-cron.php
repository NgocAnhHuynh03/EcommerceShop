<?php
/**
 * Smart Wishlist Cron
 *
 * @author MoreConvert
 * @package Smart Wishlist For More Convert
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WLFMC_Cron' ) ) {
	/**
	 * This class handles cron for wishlist plugin
	 */
	class WLFMC_Cron {
		/**
		 * Array of events to schedule
		 *
		 * @var array
		 */
		protected $crons = array();

		/**
		 * Single instance of the class
		 *
		 * @var WLFMC_Cron
		 */
		protected static $instance;

		/**
		 * Constructor
		 *
		 * @return void
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'schedule' ) );
		}

		/**
		 * Returns registered crons
		 *
		 * @return array Array of registered crons and callbacks
		 */
		public function get_crons() {
			if ( empty( $this->crons ) ) {
				$this->crons = array(
					'wlfmc_wishlist_delete_expired_wishlists' =>
						array(
							'schedule' => 'daily',
							'callback' => array( $this, 'delete_expired_wishlists' ),
						),
					'wlfmc_delete_expired_coupons' =>
						array(
							'schedule' => 'hourly',
							'callback' => array( $this, 'delete_expired_coupons' ),
						),
				);
			}

			return apply_filters( 'wlfmc_wishlist_crons', $this->crons );
		}

		/**
		 * Schedule events not scheduled yet; register callbacks for each event
		 *
		 * @return void
		 */
		public function schedule() {
			$crons = $this->get_crons();

			if ( ! empty( $crons ) ) {
				foreach ( $crons as $hook => $data ) {

					add_action( $hook, $data['callback'] );

					if ( ! wp_next_scheduled( $hook ) ) {
						wp_schedule_event( time() + MINUTE_IN_SECONDS, $data['schedule'], $hook );
					}
				}
			}
		}

		/**
		 * Delete expired session wishlist
		 *
		 * @return void
		 */
		public function delete_expired_wishlists() {
			try {
				WC_Data_Store::load( 'wlfmc-customer' )->delete_expired();
			} catch ( Exception $e ) {
				return;
			}
		}

		/**
		 * Delete expired coupons
		 *
		 * @return void
		 * @version 1.3.3
		 */
		public function delete_expired_coupons() {
			try {
				$args = array(
					'posts_per_page' => - 1,
					'post_type'      => 'shop_coupon',
					'post_status'    => 'publish',
					'meta_query'     => array(  // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						'relation' => 'AND',
						array(
							'key'     => 'date_expires',
							'value'   => current_time( 'timestamp' ), // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
							'compare' => '<=',
						),
						array(
							'key'     => 'date_expires',
							'value'   => '',
							'compare' => '!=',
						),
						array(
							'key'     => 'delete_after_expired',
							'value'   => 'yes',
							'compare' => '=',
						),
					),
				);

				$coupons = get_posts( $args );

				if ( ! empty( $coupons ) ) {
					foreach ( $coupons as $coupon ) {
						wp_delete_post( $coupon->ID, true );
					}
				}
			} catch ( Exception $e ) {
				return;
			}
		}


		/**
		 * Returns single instance of the class
		 *
		 * @return WLFMC_Cron
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
 * Unique access to instance of WLFMC_Cron class
 *
 * @return WLFMC_Cron
 */
function WLFMC_Cron(): WLFMC_Cron { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return WLFMC_Cron::get_instance();
}
